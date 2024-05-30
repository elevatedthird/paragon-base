<?php

namespace Drupal\field_tools;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides form options arrays for selecting fields and other things.
 */
class FieldOptions {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Creates a FieldOptions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    EntityDisplayRepositoryInterface $entity_display_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  public function getAllFieldOptions(string $entity_type_id, string $bundle, $machine_names = TRUE, $field_types = TRUE): array {
    // TODO!
  }

  /**
   * Gets form options for a bundle's config fields.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle name.
   *
   * @return array
   *   An array of form options suitable for checkboxes, radios, or select
   *   elements, keyed by the field machine name.
   */
  public function getConfigFieldOptions(string $entity_type_id, string $bundle): array {
    $field_ids = $this->entityTypeManager->getStorage('field_config')->getQuery()
      ->accessCheck(FALSE)
      ->condition('entity_type', $entity_type_id)
      ->condition('bundle', $bundle)
      ->execute();
    $bundle_config_fields = $this->entityTypeManager->getStorage('field_config')->loadMultiple($field_ids);

    $field_options = [];
    foreach ($bundle_config_fields as $field_id => $field) {
      $field_options[$field->getName()] = $this->t("@field-label (machine name: @field-name, type: @field-type)", array(
        '@field-label' => $field->getLabel(),
        '@field-name' => $field->getName(),
        '@field-type' => $field->getType(),
      ));
    }
    asort($field_options);

    return $field_options;
  }

  /**
   * Get the form options for a display type.
   *
   * @param $type
   *  The entity type ID of the display type.
   * @param $entity_type_id
   *  The target entity type ID of the displays.
   * @param $bundle
   *  The target bundle.
   *
   * @return
   *  An array of form options.
   */
  public function getDisplayOptions($type, $entity_type_id, $bundle) {
    $display_ids = $this->entityTypeManager->getStorage($type)->getQuery()
      ->condition('targetEntityType', $entity_type_id)
      ->condition('bundle', $bundle)
      ->execute();
    $form_displays = $this->entityTypeManager->getStorage($type)->loadMultiple($display_ids);

    // Unfortunately, getDisplayModesByEntityType() is protected :(
    if ($type == 'entity_form_display') {
      $mode_options = $this->entityDisplayRepository->getFormModeOptions($entity_type_id);
    }
    else {
      $mode_options = $this->entityDisplayRepository->getViewModeOptions($entity_type_id);
    }

    $form_display_options = [];
    foreach ($form_displays as $id => $form_display) {
      // The label() method of displays returns NULL always, so we get the label
      // from the related mode.
      $form_display_options[$id] = $mode_options[$form_display->getMode()];
    }
    asort($form_display_options);

    return $form_display_options;
  }

}
