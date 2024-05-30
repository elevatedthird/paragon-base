<?php

namespace Drupal\field_tools;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Provides information about reference fields.
 */
class FieldToolsReferencesInfo {

  /**
   * Field types which are references.
   *
   * TODO: core should allow a way for field types to declare themselves as
   * such! See https://www.drupal.org/project/drupal/issues/3057545.
   */
  const REFERENCE_TYPES = [
    'file',
    'image',
    'entity_reference',
    'entity_reference_revisions',
    'dynamic_entity_reference',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Creates a FieldToolsReferencesInfo instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The Entity field manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Gets a list of all reference field definitions.
   *
   * @param bool $include_files
   *   (optional) Whether to include references to files (and images).
   * @param bool $include_owner
   *   (optional) Whether to include owner fields.
   * @param bool $include_config_targets
   *   (optional) Whether to include references to config entity types.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of field definitions. This can be a mix of base fields and
   *   config fields. The keys are of the form 'ENTITY_ID:BUNDLE:FIELD_NAME'.
   */
  public function getReferenceFields(
    $include_files = FALSE,
    $include_owner = FALSE,
    $include_config_targets = FALSE
  ): array {
    $reference_field_definitions = [];

    $bundle_info = \Drupal::service('entity_type.bundle.info')->getAllBundleInfo();
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Only look at content entities.
      if ($entity_type->getGroup() != 'content') {
        continue;
      }

      foreach ($bundle_info[$entity_type_id] as $bundle_name => $entity_bundle_info) {
        $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle_name);

        foreach ($fields as $field_name => $field_definition) {
          $storage_definition = $field_definition->getFieldStorageDefinition();

          // Skip non-reference fields.
          if (!in_array($storage_definition->getType(), self::REFERENCE_TYPES)) {
            continue;
          }

          // Skip file and image reference fields.
          if (!$include_files) {
            if (in_array($storage_definition->getType(), ['image', 'file'])) {
              continue;
            }
          }

          // Exclude references to the bundle entity.
          // TODO: consider adding a parameter for this?
          if ($bundle_key = $entity_type->getKey('bundle')) {
            if ($field_name == $bundle_key) {
              continue;
            }
          }

          // Exclude references to config entity types.
          if (!$include_config_targets) {
            // Only check the first referenced type; we assume that config and
            // content references aren't mixed.
            $referenced_entity_type_ids = $this->getReferencedTypes($storage_definition);
            $referenced_entity_type_id = reset($referenced_entity_type_ids);
            if ($this->entityTypeManager->getDefinition($referenced_entity_type_id)->getGroup() == 'configuration') {
              continue;
            }
          }

          // Exclude owner field.
          if (!$include_owner) {
            if ($bundle_key = $entity_type->getKey('owner')) {
              if ($field_name == $bundle_key) {
                continue;
              }
            }
          }

          if ($bundle_key = $entity_type->getRevisionMetadataKey('revision_user')) {
            if ($field_name == $bundle_key) {
              continue;
            }
          }

          $reference_field_definitions[implode(':', [$entity_type_id, $bundle_name, $field_name])] = $field_definition;
        }
      }
    }

    return $reference_field_definitions;
  }

  public function getReferenceFieldStorages(): array {
    $reference_storage_definitions = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->getGroup() != 'content') {
        continue;
      }

      $storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type_id);

      // Filter to reference fields.
      $entity_reference_storage_definitions = array_filter($storage_definitions, function($storage_definition) {
        return in_array($storage_definition->getType(), self::REFERENCE_TYPES);
      });

      // Exclude references to the bundle entity.
      // TODO: consider adding a parameter for this?
      if ($bundle_key = $entity_type->getKey('bundle')) {
        unset($entity_reference_storage_definitions[$bundle_key]);
      }

      // Prefix the field name with the entity type ID in the array of all
      // storage definitions, as field names are not unique across entity types.
      foreach ($entity_reference_storage_definitions as $field_name => $storage_definition) {
        $reference_storage_definitions[$entity_type_id . ':' . $field_name] = $storage_definition;
      }
    }

    return $reference_storage_definitions;
  }

  /**
   * Gets the referenced entity type for the field.
   *
   * TODO: improve this when
   * https://www.drupal.org/project/drupal/issues/3057545 is fixed.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage.
   *
   * @return string[]
   *   An array of referenced entity type IDs.
   */
  public function getReferencedTypes(FieldStorageDefinitionInterface $storage_definition): array {
    switch ($storage_definition->getType()) {
      case 'entity_reference':
      case 'entity_reference_revisions':
        return [$storage_definition->getSettings()['target_type']];

      case 'image':
      case 'file':
        return ['file'];

    // case 'dynamic_entity_reference':
    // TODO

      default:
        return [];
    }
  }

  /**
   * Gets the bundles that a field references.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *
   * @return string[]
   *   Array of whose values are of the form TARGET_ENTITY_TYPE:TARGET_BUNDLE.
   */
  public function getReferencedBundles(FieldDefinitionInterface $field_definition): array {
    $field_settings = $field_definition->getSettings();
    $storage_definition = $field_definition->getFieldStorageDefinition();

    switch ($storage_definition->getType()) {
      case 'file':
      case 'image':
        return ['file:file'];

      case 'entity_reference':
        $target_bundles = array_values($field_settings['handler_settings']['target_bundles'] ?? []);
        $target_entity_type_id = $storage_definition->getSettings()['target_type'];

        // Base fields don't set target bundles to mean all bundles.
        // Config fields cant do this because the option is required in the UI.
        if ($storage_definition->isBaseField() && empty($target_bundles)) {
          $target_bundles = array_keys(\Drupal::service('entity_type.bundle.info')->getBundleInfo($target_entity_type_id));
        }

        array_walk($target_bundles, function (&$bundle_name) use ($target_entity_type_id) {
          $bundle_name = $target_entity_type_id . ':' . $bundle_name;
        });

        return $target_bundles;

      case 'entity_reference_revisions':
        // AAAAAARGH TODO
        return [];

        $target_bundles = $field_settings['handler_settings']['target_bundles'];

        // 'negate' = 0 means 'include'; 1 means 'exclude'.
        if ($field_settings['handler_settings']['negate']) {
          // The target bundles in the setting are excluded.
          if (empty($target_bundles)) {
            // TODO!
            $target_bundles = array_keys($bundle_info[$field_settings['target_type']]);
          }
          else {
            // TODO!
            $target_bundles = array_diff_key($bundle_info[$field_settings['target_type']], $target_bundles);
            $target_bundles = array_keys($target_bundles);
          }
        }
        else {
          // The target bundles in the setting are included.
          if (empty($target_bundles)) {
            // No target bundles are included!
            return [];
          }
          else {
            $target_bundles = array_values($target_bundles);
          }
        }

        $target_entity_type_id = $storage_definition->getSettings()['target_type'];

        array_walk($target_bundles, function (&$bundle_name) {
          $bundle_name = $target_entity_type_id . ':' . $bundle_name;
        });

        return $target_bundles;

      default:
        return [];
    }

  }


}
