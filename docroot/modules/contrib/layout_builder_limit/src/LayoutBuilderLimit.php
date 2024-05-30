<?php

namespace Drupal\layout_builder_limit;

use Drupal\layout_builder\Section;

class LayoutBuilderLimit {

  const LIMIT_DISABLED = 'disabled';

  const LIMIT_REGION = 'region';

  const LIMIT_SECTION = 'section';

  const DEFAULT_CONFIGURATION = [
    'scope' => self::LIMIT_DISABLED,
  ];

  const DEFAULT_SCOPE_CONFIGURATION = [
    'minimum_enabled' => FALSE,
    'minimum' => 1,
    'maximum_enabled' => FALSE,
    'maximum' => 1,
  ];

  public static function getDefaultConfiguration(Section $section, $configuration = []) {
    $configuration['scope'] = $configuration['scope'] ?? self::LIMIT_DISABLED;
    $scope = $configuration['scope'];
    $settings = $configuration['settings'] ?? [];
    switch ($scope) {
      case self::LIMIT_SECTION:
        $settings[$scope] = self::getScopeSettings($settings[$scope]);
        $configuration['settings'] = $settings;
        break;
      case self::LIMIT_REGION:
        foreach ($section->getLayout()->getPluginDefinition()->getRegions() as $region_name => $region) {
          $settings[$scope][$region_name] = self::getScopeSettings($settings[$scope][$region_name]);
        }
        $configuration['settings'] = $settings;
        break;
    }
    return $configuration;
  }

  public static function getScopeSettings($settings) {
    return [
      'minimum_enabled' => $settings['minimum_enabled'] ?? FALSE,
      'minimum' => is_numeric($settings['minimum']) ? $settings['minimum'] : 1,
      'maximum_enabled' => $settings['maximum_enabled'] ?? FALSE,
      'maximum' =>  is_numeric($settings['maximum']) ? $settings['maximum'] : 1,
    ];
  }

}
