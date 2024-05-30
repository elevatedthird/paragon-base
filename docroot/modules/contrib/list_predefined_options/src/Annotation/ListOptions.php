<?php

namespace Drupal\list_predefined_options\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a List options item annotation object.
 *
 * Plugin namespace: ListOptions.
 *
 * @see \Drupal\list_predefined_options\ListOptionsManager
 * @see plugin_api
 *
 * @Annotation
 */
class ListOptions extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The field types that this plugin can be used with.
   *
   * An array of field type plugin IDs. Only the following are supported:
   *   - list_float
   *   - list_integer
   *   - list_string.
   *
   * @var string[]
   */
  public $field_types = [];

}
