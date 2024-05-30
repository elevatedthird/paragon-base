<?php

namespace Drupal\list_predefined_options;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the List options plugin manager.
 */
class ListOptionsManager extends DefaultPluginManager {

  /**
   * Constructor for ListOptionsManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ListOptions', $namespaces, $module_handler, 'Drupal\list_predefined_options\Plugin\ListOptionsInterface', 'Drupal\list_predefined_options\Annotation\ListOptions');

    $this->alterInfo('list_predefined_options_list_options_info');
    $this->setCacheBackend($cache_backend, 'list_predefined_options_list_options_plugins');
  }

  /**
   * Returns a list of names available predefined list options.
   *
   * @param string $field_type
   *   The field type to get lists for.
   *
   * @return array
   *   An array keyed by plugin ID whose values are the plugin labels.
   */
  public function listOptions(string $field_type) {
    $options = [];
    foreach ($this->getDefinitions() as $key => $definition) {
      if (in_array($field_type, $definition['field_types'])) {
        $options[$key] = $definition['label'];
      }
    }
    natcasesort($options);
    return $options;
  }

}
