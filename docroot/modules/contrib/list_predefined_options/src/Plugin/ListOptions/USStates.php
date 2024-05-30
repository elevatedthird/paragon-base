<?php

namespace Drupal\list_predefined_options\Plugin\ListOptions;

use Drupal\list_predefined_options\Plugin\ListOptionsBase;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a list of US states.
 *
 * @ListOptions(
 *   id = "us_states",
 *   label = @Translation("US States"),
 *   field_types = {
 *     "list_string",
 *   },
 * )
 */
class USStates extends ListOptionsBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getListOptions(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, &$cacheable = TRUE) {
    $options['AL'] = $this->t('Alabama');
    $options['AK'] = $this->t('Alaska');
    $options['AZ'] = $this->t('Arizona');
    $options['AR'] = $this->t('Arkansas');
    $options['CA'] = $this->t('California');
    $options['CO'] = $this->t('Colorado');
    $options['CT'] = $this->t('Connecticut');
    $options['DE'] = $this->t('Delaware');
    $options['DC'] = $this->t('District Of Columbia');
    $options['FL'] = $this->t('Florida');
    $options['GA'] = $this->t('Georgia');
    $options['HI'] = $this->t('Hawaii');
    $options['ID'] = $this->t('Idaho');
    $options['IL'] = $this->t('Illinois');
    $options['IN'] = $this->t('Indiana');
    $options['IA'] = $this->t('Iowa');
    $options['KS'] = $this->t('Kansas');
    $options['KY'] = $this->t('Kentucky');
    $options['LA'] = $this->t('Louisiana');
    $options['ME'] = $this->t('Maine');
    $options['MD'] = $this->t('Maryland');
    $options['MA'] = $this->t('Massachusetts');
    $options['MI'] = $this->t('Michigan');
    $options['MN'] = $this->t('Minnesota');
    $options['MS'] = $this->t('Mississippi');
    $options['MO'] = $this->t('Missouri');
    $options['MT'] = $this->t('Montana');
    $options['NE'] = $this->t('Nebraska');
    $options['NV'] = $this->t('Nevada');
    $options['NH'] = $this->t('New Hampshire');
    $options['NJ'] = $this->t('New Jersey');
    $options['NM'] = $this->t('New Mexico');
    $options['NY'] = $this->t('New York');
    $options['NC'] = $this->t('North Carolina');
    $options['ND'] = $this->t('North Dakota');
    $options['OH'] = $this->t('Ohio');
    $options['OK'] = $this->t('Oklahoma');
    $options['OR'] = $this->t('Oregon');
    $options['PA'] = $this->t('Pennsylvania');
    $options['RI'] = $this->t('Rhode Island');
    $options['SC'] = $this->t('South Carolina');
    $options['SD'] = $this->t('South Dakota');
    $options['TN'] = $this->t('Tennessee');
    $options['TX'] = $this->t('Texas');
    $options['UT'] = $this->t('Utah');
    $options['VT'] = $this->t('Vermont');
    $options['VA'] = $this->t('Virginia');
    $options['WA'] = $this->t('Washington');
    $options['WV'] = $this->t('West Virginia');
    $options['WI'] = $this->t('Wisconsin');
    $options['WY'] = $this->t('Wyoming');
    return $options;
  }

}
