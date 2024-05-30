<?php

namespace Drupal\list_predefined_options;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Prevents uninstallation of modules providing an active list options plugin.
 */
class FieldListOptionsProviderUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * The list options manager.
   *
   * @var \Drupal\list_predefined_options\ListOptionsManager
   */
  protected $listOptionsManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a FieldListOptionsProviderUninstallValidator instance.
   *
   * @param \Drupal\list_predefined_options\ListOptionsManager $list_options_manager
   *   The list options manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    ListOptionsManager $list_options_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->listOptionsManager = $list_options_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];

    // Find any list options plugin which the module provides.
    $uninstalling_module_plugins = [];
    foreach ($this->listOptionsManager->getDefinitions() as $plugin_id => $plugin_definition) {
      if ($plugin_definition['provider'] == $module) {
        $uninstalling_module_plugins[$plugin_id] = $plugin_definition;
      }
    }

    // If the module provides no plugins, we don't care about it.
    if (empty($uninstalling_module_plugins)) {
      return $reasons;
    }

    // Get the field storages which use the module's list options plugin.
    $field_storages_using_list_options_plugin = $this->getFieldStoragesUsingListOptionsPlugin(array_keys($uninstalling_module_plugins));

    // Group the field storages by plugin ID.
    $fields_in_use_by_plugin = [];
    foreach ($field_storages_using_list_options_plugin as $field_storage) {
      $plugin_id = $field_storage->getThirdPartySetting('list_predefined_options', 'plugin_id');
      $fields_in_use_by_plugin[$plugin_id][] = $field_storage->getLabel();
    }

    // Output a validation reason for each plugin that is in use.
    foreach ($fields_in_use_by_plugin as $plugin_id => $field_storage_labels) {
      $reasons[] = $this->formatPlural(
        count($field_storage_labels),
        'The %plugin-label list options plugin is used in the following field: @fields',
        'The %plugin-label list options plugin is used in the following fields: @fields',
        [
          '%plugin-label' => $this->listOptionsManager->getDefinition($plugin_id)['label'],
          '@fields' => implode(', ', $field_storage_labels),
        ]
      );
    }

    return $reasons;
  }

  /**
   * Gets field storages which use one of the given list options plugins.
   *
   * @param array $list_option_plugin_ids
   *   An array of list option plugin IDs.
   *
   * @return array
   *   An array of field storage entities, keyed by ID.
   */
  protected function getFieldStoragesUsingListOptionsPlugin(array $list_option_plugin_ids): array {
    $field_storages_using_list_options_plugin = [];

    $field_storages = $this->entityTypeManager->getStorage('field_storage_config')->loadMultiple();
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    foreach ($field_storages as $field_storage_id => $field_storage) {
      if ($field_storage->getSetting('allowed_values_function') != 'list_predefined_options_allowed_values') {
        continue;
      }

      if (in_array($field_storage->getThirdPartySetting('list_predefined_options', 'plugin_id'), $list_option_plugin_ids)) {
        $field_storages_using_list_options_plugin[$field_storage_id] = $field_storage;
      }
    }

    return $field_storages_using_list_options_plugin;
  }

}
