<?php

namespace Drupal\layout_builder_limit;

class PluginFilterAlter {

  /**
   * Filters block definitions if maximum number are reached in any scope.
   */
  public function blockLayoutBuilderPluginFilterAlter(array &$definitions, array $extra) {
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $extra['section_storage'];
    $delta = $extra['delta'] ?? FALSE;
    if ($delta !== FALSE) {
      $section = $section_storage->getSection($delta);
      $configuration = $section->getThirdPartySetting('layout_builder_limit', 'limit', LayoutBuilderLimit::DEFAULT_CONFIGURATION);
      $configuration = LayoutBuilderLimit::getDefaultConfiguration($section, $configuration);

      if ($configuration['scope'] == LayoutBuilderLimit::LIMIT_REGION) {
        if (isset($configuration['settings']['region'][$extra['region']])) {
          $region_settings = $configuration['settings']['region'][$extra['region']];
          $maximum_enabled = (bool) $region_settings['maximum_enabled'];
          if ($maximum_enabled) {
            $maximum = (int) $region_settings['maximum'];
            if(count($section->getComponentsByRegion($extra['region'])) >= $maximum) {
              $definitions = [];
            }
          }
        }
      }
      elseif ($configuration['scope'] == LayoutBuilderLimit::LIMIT_SECTION) {
        if (isset($configuration['settings']['section'])) {
          $section_settings = $configuration['settings']['section'];
          $maximum_enabled = (bool) $section_settings['maximum_enabled'];
          if ($maximum_enabled) {
            $maximum = (int) $section_settings['maximum'];
            if(count($section->getComponents()) >= $maximum) {
              $definitions = [];
            }
          }
        }
      }
    }
  }

}
