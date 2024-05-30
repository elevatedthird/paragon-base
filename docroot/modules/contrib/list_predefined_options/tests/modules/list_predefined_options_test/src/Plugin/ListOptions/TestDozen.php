<?php

namespace Drupal\list_predefined_options_test\Plugin\ListOptions;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\list_predefined_options\Plugin\ListOptionsBase;

/**
 * Provides integers 1-12.
 *
 * @ListOptions(
 *   id = "test_dozen",
 *   label = @Translation("Test Dozen"),
 *   field_types = {
 *     "list_integer",
 *   },
 * )
 */
class TestDozen extends ListOptionsBase {

  /**
   * {@inheritdoc}
   */
  public function getListOptions(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, &$cacheable = TRUE) {
    return array_combine(
      range(1, 12),
      range(1, 12)
    );
  }

}
