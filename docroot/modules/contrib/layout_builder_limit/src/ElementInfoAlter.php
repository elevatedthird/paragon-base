<?php

namespace Drupal\layout_builder_limit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;

class ElementInfoAlter implements TrustedCallbackInterface {

  /**
   * Alters and validates the Layout Builder element display based on limits.
   *
   * @param $info
   *   The layout builder element info.
   */
  public function elementInfoAlter(&$info) {
    if (!empty($info['layout_builder'])) {
      $info['layout_builder']['#pre_render'][] = [$this, 'preRender'];
      $info['layout_builder']['#element_validate'][] = [$this, 'validateLayoutBuilderElement'];
    }
  }

  /**
   * Applies limit settings to the Layout Builder element.
   *
   * @param array $element
   *   The Layout Builder render element.
   *
   * @return array
   *   The modified Layout Builder render element.
   */
  public function preRender(array $element) {
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $element['#section_storage'];

    // We do some additional logic to track the section_number.
    // I took this from layout_builder_limit and we should definitely revise
    // if a better solution is found.
    $section_number = 0;
    for ($i = 0; $i < $section_storage->count(); $i++) {
      // Get settings, continue if empty.
      $section = $section_storage->getSection($i);
      $configuration = $section->getThirdPartySetting('layout_builder_limit', 'limit', LayoutBuilderLimit::DEFAULT_CONFIGURATION);
      $configuration = LayoutBuilderLimit::getDefaultConfiguration($section, $configuration);
      if ($configuration['scope'] == LayoutBuilderLimit::LIMIT_DISABLED) {
        continue;
      }
      $components = $section->getComponents();

      // The element key in the build starts at $i + 1, after that it's 2 on
      // because of the add section links.
      if ($i == 0) {
        $section_number = $i + 1;
      }
      elseif ($i == 1) {
        $section_number = $i + 2;
      }
      else {
        $section_number += 2;
      }
      // Ignore non existing section numbers.
      if (!isset($element['layout_builder'][$section_number])) {
        continue;
      }

      if ($configuration['scope'] == LayoutBuilderLimit::LIMIT_REGION) {
        foreach ($element['layout_builder'][$section_number] as $name => $item) {
          if (isset($item['#layout'])) {
            foreach ($item['#layout']->getRegions() as $region_name => $region) {
              if (isset($configuration['settings']['region'][$region_name])) {
                $region_settings = $configuration['settings']['region'][$region_name];
                $minimum_enabled = (bool) $region_settings['minimum_enabled'];
                $maximum_enabled = (bool) $region_settings['maximum_enabled'];
                if ($minimum_enabled || $maximum_enabled) {
                  $component_count = count($section->getComponentsByRegion($region_name));
                  if ($minimum_enabled) {
                    $minimum = (int) $region_settings['minimum'];
                    if ($component_count < $minimum) {
                      $element['layout_builder'][$section_number][$name][$region_name]['limit_message'] = [
                        '#theme' => 'status_messages',
                        '#message_list' => [
                          'warning' => [
                            \Drupal::translation()
                              ->formatPlural($minimum, 'A minimum of @minimum block is required in this @region region.', 'A minimum of @minimum blocks are required in this @region region.', [
                                '@minimum' => $minimum,
                                '@region' => $region['label']
                              ]),
                          ],
                        ],
                        '#status_headings' => [
                          'status' => t('Status message'),
                          'error' => t('Error message'),
                          'warning' => t('Warning message'),
                        ],
                        '#weight' => -10,
                      ];
                    }
                  }
                  if ($maximum_enabled) {
                    $maximum = (int) $region_settings['maximum'];
                    if ($component_count > $maximum) {
                      $element['layout_builder'][$section_number][$name][$region_name]['limit_message'] = [
                        '#theme' => 'status_messages',
                        '#message_list' => [
                          'error' => [
                            \Drupal::translation()->formatPlural($maximum, 'This @region region is exceeding the maximum limit of @maximum block.', 'This @region region is exceeding the maximum limit of @maximum blocks.', ['@maximum' => $maximum, '@region' => $region['label']]),
                          ],
                        ],
                        '#status_headings' => [
                          'status' => t('Status message'),
                          'error' => t('Error message'),
                          'warning' => t('Warning message'),
                        ],
                        '#weight' => -10,
                      ];
                    }
                    if ($component_count >= $maximum) {
                      unset($element['layout_builder'][$section_number][$name][$region_name]['layout_builder_add_block']);
                    }
                  }
                }
              }
            }
          }
        }
      }
      elseif ($configuration['scope'] == LayoutBuilderLimit::LIMIT_SECTION) {
        if (isset($configuration['settings']['section'])) {
          $section_settings = $configuration['settings']['section'];
          $minimum_enabled = (bool) $section_settings['minimum_enabled'];
          $maximum_enabled = (bool) $section_settings['maximum_enabled'];
          if ($minimum_enabled) {
            $minimum = (int) $section_settings['minimum'];
            if (count($components) < $minimum) {
              $element['layout_builder'][$section_number]['limit_message'] = [
                '#theme' => 'status_messages',
                '#message_list' => [
                  'warning' => [
                    \Drupal::translation()->formatPlural($minimum, 'A minimum of @minimum block is required in this section.', 'A minimum of @minimum blocks are required in this section.', ['@minimum' => $minimum]),
                  ],
                ],
                '#status_headings' => [
                  'status' => t('Status message'),
                  'error' => t('Error message'),
                  'warning' => t('Warning message'),
                ],
                '#weight' => -10,
              ];
            }
          }
          if ($maximum_enabled) {
            $maximum = (int) $section_settings['maximum'];
            if (count($components) > $maximum) {
              $element['layout_builder'][$section_number]['limit_message'] = [
                '#theme' => 'status_messages',
                '#message_list' => [
                  'error' => [
                    \Drupal::translation()->formatPlural($maximum, 'This section is exceeding the maximum limit of @maximum block.', 'This section is exceeding the maximum limit of @maximum blocks.', ['@maximum' => $maximum]),
                  ],
                ],
                '#status_headings' => [
                  'status' => t('Status message'),
                  'error' => t('Error message'),
                  'warning' => t('Warning message'),
                ],
                '#weight' => -10,
              ];
            }
            if (count($components) >= $maximum) {
              foreach ($element['layout_builder'][$section_number] as $name => $item) {
                if (isset($item['#layout'])) {
                  foreach (Element::children($item) as $region_key) {
                    unset($element['layout_builder'][$section_number][$name][$region_key]['layout_builder_add_block']);
                    // todo: Restrict move into, without changing move out.
                  }
                }
              }
            }
          }
        }
      }
    }
    return $element;
  }

  /**
   * Provides a '#validate' callback for the layout builder element.
   */
  public function validateLayoutBuilderElement(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $form['#section_storage'];
    for ($i = 0; $i < $section_storage->count(); $i++) {
      // Get settings, continue if empty.
      $section = $section_storage->getSection($i);
      $configuration = $section->getThirdPartySetting('layout_builder_limit', 'limit', LayoutBuilderLimit::DEFAULT_CONFIGURATION);
      $configuration = LayoutBuilderLimit::getDefaultConfiguration($section, $configuration);
      if ($configuration['scope'] == LayoutBuilderLimit::LIMIT_DISABLED) {
        continue;
      }
      $components = $section->getComponents();
      if ($configuration['scope'] == LayoutBuilderLimit::LIMIT_REGION) {
        $regions = $section->getLayout()->getPluginDefinition()->getRegionNames();
        foreach ($regions as $region_name) {
          if (isset($configuration['settings']['region'][$region_name])) {
            $region_settings = $configuration['settings']['region'][$region_name];
            $minimum_enabled = (bool) $region_settings['minimum_enabled'];
            $maximum_enabled = (bool) $region_settings['maximum_enabled'];
            if ($minimum_enabled || $maximum_enabled) {
              $component_count = count($section->getComponentsByRegion($region_name));
              if ($minimum_enabled) {
                $minimum = (int) $region_settings['minimum'];
                if ($component_count < $minimum) {
                  $form_state->setError($form, 'One or more regions are missing the minimum required blocks.');;
                }
              }
              if ($maximum_enabled) {
                $maximum = (int) $region_settings['maximum'];
                if ($component_count > $maximum) {
                  $form_state->setError($form, 'One or more regions are exceeding the maximum allowed blocks.');
                }
              }
            }

          }
        }
      }
      elseif ($configuration['scope'] == LayoutBuilderLimit::LIMIT_SECTION) {
        if (isset($configuration['settings']['section'])) {
          $section_settings = $configuration['settings']['section'];
          $minimum_enabled = (bool) $section_settings['minimum_enabled'];
          $maximum_enabled = (bool) $section_settings['maximum_enabled'];
          if ($minimum_enabled) {
            $minimum = (int) $section_settings['minimum'];
            if (count($components) < $minimum) {
              $form_state->setError($form, 'One or more sections are missing the minimum required blocks.');;
            }
          }
          if ($maximum_enabled) {
            $maximum = (int) $section_settings['maximum'];
            if (count($components) > $maximum) {
              $form_state->setError($form, 'One or more sections are exceeding the maximum allowed blocks.');
            }
          }
        }
      }
    }
  }

  /**
   * @inheritDoc
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

}
