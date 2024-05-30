<?php

/**
 * @file
 * Contains \Drupal\paragraphs_browser\Form\ParagraphsTypeGroupForm.
 */

namespace Drupal\paragraphs_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\ParagraphsTypeInterface;
use Drupal\paragraphs_browser\Entity\BrowserType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;


/**
 * Class CleanupUrlAliases.
 *
 * @package Drupal\paragraphs_browser\Form
 */
class ParagraphTypeGroupsForm extends FormBase {

  /**
   * The index for which the fields are configured.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $entity;

  /**
   * Drupal entityTypeManager
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Provides messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a new ParagraphsTypeDeleteConfirm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraphs_browser_group_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ParagraphsTypeInterface $paragraphs_type = null) {
    $paragraph_browser_type_ids = $this->entityTypeManager->getStorage('paragraphs_browser_type')->getQuery()->execute();
    //@todo: on unsaved change, hide links to configuration forms.
    if(!$paragraph_browser_type_ids) {
      // @todo set message and link to page
      return;
    }

    $paragraph_browser_types = array();
    $form['paragraph_browser_type'] = array(
      '#type' => 'container',
      '#tree' => TRUE
    );
    foreach($paragraph_browser_type_ids as $machine_name) {
      $paragraph_browser_type = BrowserType::load($machine_name);
      $paragraph_browser_types[$machine_name] = $paragraph_browser_type;
      $form['paragraph_browser_type'][$machine_name] = array(
        '#type' => 'fieldset',
        '#title' => $paragraph_browser_type->label()
      );
      if($groups = $paragraph_browser_type->groupManager()->getGroups()) {
        $options = array('_na' => '-- Not Defined --');
        foreach($groups as $group_machine_name => $group) {
          $options[$group_machine_name] = $group->getLabel();
        }

        $form['paragraph_browser_type'][$machine_name]['group'] = array(
          '#type' => 'select',
          '#title' => $paragraph_browser_type->label(),
          '#title_display' => 'hidden',
          '#options' => $options,
          '#default_value' => $paragraph_browser_type->getGroupMap($paragraphs_type->id()),
        );
      } else {
        $form['paragraph_browser_type'][$machine_name]['group'] = array(
          '#type' => 'markup',
          '#markup' => 'No groups defined.',
        );
      }

    }

    $form_state->addBuildInfo('paragraph_browser_types', $paragraph_browser_types);
    $form_state->addBuildInfo('paragraph_type', $paragraphs_type);

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#submit' => array('::submitForm', '::save'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $paragraph_browser_types = $build_info['paragraph_browser_types'];
    $paragraph_type = $build_info['paragraph_type'];

    $mapping = $form_state->getValue('paragraph_browser_type');
    $mapping = array_combine(array_keys($mapping), array_column($mapping, 'group'));
    $mapping = array_filter($mapping, function($v, $k) {
      return $v != '_na';
    }, ARRAY_FILTER_USE_BOTH);


    foreach($paragraph_browser_types as $paragraph_browser_type_id => $paragraph_browser_type) {
      $paragraph_browser_type->removeGroupMap($paragraph_type->id());
      if(isset($mapping[$paragraph_browser_type_id])) {
        $paragraph_browser_type->setGroupMap($paragraph_type->id(), $mapping[$paragraph_browser_type_id]);
      }
    }

    $this->messenger->addMessage($this->t('The selected groups have been added to the %label Paragraph type.', array(
      '%label' => $paragraph_type->label()
    )));
  }

  /**
   * Secondary submit handler
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function save(array $form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $paragraph_browser_types = $build_info['paragraph_browser_types'];

    foreach($paragraph_browser_types as $type) {
      $type->save();
    }
  }
}
