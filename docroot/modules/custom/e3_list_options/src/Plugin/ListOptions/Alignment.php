<?php

namespace Drupal\e3_list_options\Plugin\ListOptions;

use Drupal\list_predefined_options\Plugin\ListOptionsBase;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a list of Alignment options.
 *
 * @ListOptions(
 *   id = "alignment",
 *   label = @Translation("Alignment"),
 *   field_types = {
 *     "list_string",
 *   },
 * )
 */
class Alignment extends ListOptionsBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getListOptions(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, &$cacheable = TRUE) {
    $options['left'] = $this->t('Left');
    $options['right'] = $this->t('Right');
    $options['center'] = $this->t('Center');
    return $options;
  }

}
