<?php

namespace Drupal\e3_list_options\Plugin\ListOptions;

use Drupal\list_predefined_options\Plugin\ListOptionsBase;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a list of Colors.
 *
 * @ListOptions(
 *   id = "colors",
 *   label = @Translation("Colors"),
 *   field_types = {
 *     "list_string",
 *   },
 * )
 */
class Colors extends ListOptionsBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getListOptions(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, &$cacheable = TRUE) {
    $options['primary'] = $this->t('Primary');
    $options['secondary'] = $this->t('Secondary');
    $options['tertiary'] = $this->t('Tertiary');
    return $options;
  }

}
