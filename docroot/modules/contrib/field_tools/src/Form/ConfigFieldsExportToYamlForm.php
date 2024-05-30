<?php

namespace Drupal\field_tools\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\field_tools\FieldCloner;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to export multiple fields to config YAML.
 */
class ConfigFieldsExportToYamlForm extends FormBase {

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
   * The field cloner.
   *
   * @var \Drupal\field_tools\FieldCloner
   */
  protected $fieldCloner;

  /**
   * Creates a Clone instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\field_tools\FieldCloner $field_cloner
   *   The field cloner.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, FieldCloner $field_cloner) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->fieldCloner = $field_cloner;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('field_tools.field_cloner')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_tools_field_export_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL, $bundle = NULL) {
    $this->configStorage = \Drupal::service('config.storage');

    $this->entityTypeId = $entity_type_id;
    $this->bundle = $bundle;
    $entity_type = $this->entityTypeManager->getdefinition($entity_type_id);

    $code = [];
    if (!empty($form_state->getValues())) {
      $field_manager = \Drupal::service('entity_field.manager');
      $field_definitions = $field_manager->getFieldDefinitions($entity_type_id, $bundle);

      $code = [];

      if ($form_state->getValue('bundle')) {
        $bundle_entity_type_id = $entity_type->getBundleEntityType();
        $bundle_entity = $this->entityTypeManager->getStorage($bundle_entity_type_id)->load($bundle);

        $this->addConfigEntityToCode($code, $bundle_entity);
      }

      $form_display_ids = array_filter($form_state->getValue('entity_form_displays'));
      $form_displays = $this->entityTypeManager->getStorage('entity_form_display')->loadMultiple($form_display_ids);
      foreach ($form_displays as $form_display) {
        $data = $this->getConfigEntityDataArray($form_display);
        $data = $this->removeUnexportedFieldsFromDisplayData($data, $form_state->getValue('fields'));
        $this->addDataArrayToCode($code, $data);
      }

      $view_display_ids = array_filter($form_state->getValue('entity_view_displays'));
      $view_displays = $this->entityTypeManager->getStorage('entity_view_display')->loadMultiple($view_display_ids);
      foreach ($view_displays as $view_display) {
        $data = $this->getConfigEntityDataArray($view_display);
        $data = $this->removeUnexportedFieldsFromDisplayData($data, $form_state->getValue('fields'));
        $this->addDataArrayToCode($code, $data);
      }

      foreach (array_filter($form_state->getValues()['fields']) as $field_name) {
        $this->getConfigYaml($code, $field_definitions[$field_name]);

        $code[] = '';
      }

      $form['code'] = [
        '#type' => 'textarea',
        '#title' => $this->t("Field config YAML. This can be imported to another site at Administration > Configuration > Development > Import > Multiple."),
        '#value' => implode("\n", $code),
        '#rows' => count($code) + 1,
      ];
    }

    // Only show if bundles are config entities.
    if ($entity_type->getBundleEntityType()) {
      $form['bundle'] = [
        '#title' => $this->t('Export bundle'),
        '#type' => 'checkbox',
        '#description' => $this->t("Select to export the bundle config entity."),
      ];
    }

    $form['entity_form_displays'] = [
      '#title' => $this->t('Form displays to clone'),
      '#type' => 'checkboxes',
      '#options' => \Drupal::service('field_tools.field_options')->getDisplayOptions('entity_form_display', $entity_type_id, $bundle),
      '#description' => $this->t("Select form displays to export."),
    ];

    $form['entity_view_displays'] = [
      '#title' => $this->t('View displays to clone'),
      '#type' => 'checkboxes',
      '#options' => \Drupal::service('field_tools.field_options')->getDisplayOptions('entity_view_display', $entity_type_id, $bundle),
      '#description' => $this->t("Select view displays to export."),
    ];

    $form['fields'] = array(
      '#title' => $this->t('Fields to export to YAML'),
      '#type' => 'checkboxes',
      '#options' => \Drupal::service('field_tools.field_options')->getConfigFieldOptions($entity_type_id, $bundle),
      '#description' => $this->t("Select fields to export as config YAML."),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Export field definitions to YAML'),
    );

    return $form;
  }

  // TODO: Clean up all these helpers: convert everything to use
  // getConfigEntityDataArray() and then build an array of arrays to finally
  // conver to YAML.
  protected function getConfigYaml(&$code, FieldDefinitionInterface $field) {
    $field_storage = $field->getFieldStorageDefinition();

    $code[] = '_config_id: ' . $field_storage->getConfigDependencyName();
    $code[] = Yaml::encode($this->configStorage->read($field_storage->getConfigDependencyName()));

    $code[] = '_config_id: ' . $field->getConfigDependencyName();
    $code[] = Yaml::encode($this->configStorage->read($field->getConfigDependencyName()));
  }

  protected function getConfigEntityDataArray(ConfigEntityInterface $config_entity) {
    $data['_config_id'] = $config_entity->getConfigDependencyName();
    $data += $this->configStorage->read($config_entity->getConfigDependencyName());
    return $data;
  }

  protected function addConfigEntityToCode(&$code, ConfigEntityInterface $config_entity) {
    $code[] = '_config_id: ' . $config_entity->getConfigDependencyName();
    $code[] = Yaml::encode($this->configStorage->read($config_entity->getConfigDependencyName()));
  }

  /**
   * Remove any fields not selected for export from display mode data.
   *
   * @param array $data
   *   The config data for the display mode entity.
   * @param array $form_state_field_values
   *   The complete, unfiltered array of form values for the fields to export.
   *   It is important that this contains the empty checkbox values!
   *
   * @return array
   *   The config data for the display mode entity with the unselected fields
   *   removed from config dependencies, content, and hidden properties.
   */
  protected function removeUnexportedFieldsFromDisplayData($data, $form_state_field_values): array {
    foreach ($form_state_field_values as $field_name => $form_value) {
      // The field is selected for export: keep it in the display.
      if (!empty($form_value)) {
        continue;
      }

      // Remove from dependencies.
      foreach ($data['dependencies']['config'] as $index => $dependency) {
        // Quick hack.
        if (str_ends_with($dependency, '.' . $field_name)) {
          unset($data['dependencies']['config'][$index]);
        }

        unset($data['content'][$field_name]);
        unset($data['hidden'][$field_name]);
      }
    }

    // Reindex the dependencies array.
    $data['dependencies']['config'] = array_values($data['dependencies']['config']);

    return $data;
  }

  protected function addDataArrayToCode(&$code, $data) {
    $code[] = Yaml::encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

}
