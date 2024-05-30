<?php

namespace Drupal\field_tools\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_tools\DisplayCloner;
use Drupal\field_tools\FieldOptions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to clone displays from an entity bundle.
 */
class EntityDisplayBulkCloneForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The field options service.
   *
   * @var \Drupal\field_tools\FieldOptions
   */
  protected $fieldOptions;

  /**
   * The display cloner.
   *
   * @var \Drupal\field_tools\DisplayCloner
   */
  protected $displayCloner;

  /**
   * Creates a EntityDisplayBulkCloneForm instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository service.
   * @param \Drupal\field_tools\FieldOptions $field_options
   *   The field options service.
   * @param \Drupal\field_tools\DisplayCloner $display_cloner
   *   The display cloner service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityDisplayRepositoryInterface $entity_display_repository,
    FieldOptions $field_options,
    DisplayCloner $display_cloner
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->fieldOptions = $field_options;
    $this->displayCloner = $display_cloner;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_display.repository'),
      $container->get('field_tools.field_options'),
      $container->get('field_tools.display_cloner'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_tools_displays_clone_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL, $bundle = NULL) {
    $form['displays']['entity_form_display'] = array(
      '#title' => $this->t('Form displays to clone'),
      '#type' => 'checkboxes',
      '#options' => $this->fieldOptions->getDisplayOptions('entity_form_display', $entity_type_id, $bundle),
      '#description' => $this->t("Select form displays to clone onto one or more bundles."),
    );

    $form['displays']['entity_view_display'] = array(
      '#title' => $this->t('View displays to clone'),
      '#type' => 'checkboxes',
      '#options' => $this->fieldOptions->getDisplayOptions('entity_view_display', $entity_type_id, $bundle),
      '#description' => $this->t("Select view displays to clone onto one or more bundles."),
    );

    $entity_type_bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    $destination_bundle_options = [];
    foreach ($entity_type_bundles as $bundle_id => $bundle_info) {
      if ($bundle_id == $bundle) {
        continue;
      }

      $destination_bundle_options[$bundle_id] = $bundle_info['label'];
    }
    natcasesort($destination_bundle_options);

    $form['destination_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t("Bundle to clone displays to"),
      '#options' => $destination_bundle_options,
    ];

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Clone displays'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $destination_bundles = array_filter($form_state->getValue('destination_bundles'));

    $form_display_ids = array_filter($form_state->getValue('entity_form_display'));
    $form_displays_to_clone = $this->entityTypeManager->getStorage('entity_form_display')->loadMultiple($form_display_ids);
    foreach ($form_displays_to_clone as $form_display) {
      foreach ($destination_bundles as $destination_bundle) {
        $this->displayCloner->cloneDisplay($form_display, $destination_bundle);
      }
    }

    $view_display_ids = array_filter($form_state->getValue('entity_view_display'));
    $view_displays_to_clone = $this->entityTypeManager->getStorage('entity_view_display')->loadMultiple($view_display_ids);
    foreach ($view_displays_to_clone as $view_display) {
      foreach ($destination_bundles as $destination_bundle) {
        $this->displayCloner->cloneDisplay($view_display, $destination_bundle);
      }
    }

    $this->messenger()->addMessage(t("The displays have been cloned."));
  }

}
