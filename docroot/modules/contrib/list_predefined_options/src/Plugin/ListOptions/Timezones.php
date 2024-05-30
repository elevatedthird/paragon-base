<?php

namespace Drupal\list_predefined_options\Plugin\ListOptions;

use Drupal\list_predefined_options\Plugin\ListOptionsBase;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Provides a list of timezones.
 *
 * @ListOptions(
 *   id = "timezones",
 *   label = @Translation("Timezones"),
 *   field_types = {
 *     "list_string",
 *   },
 * )
 */
class Timezones extends ListOptionsBase {

  /**
   * {@inheritdoc}
   */
  public function getListOptions(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, &$cacheable = TRUE) {
    return system_time_zones();
  }

}
