<?php

$dev_debug = TRUE;

ini_set('zend.assertions', 1);

if ($dev_debug) {

  /**
   * Enable local development services.
   */
  $settings['container_yamls'][] =  $app_root . '/' . $site_path . '/local.services.yml';

  /**
   * Show all error messages, with backtrace information.
   *
   * In case the error level could not be fetched from the database, as for
   * example the database connection failed, we rely only on this value.
   */
  $config['system.logging']['error_level'] = 'verbose';

  /**
   * Disable CSS and JS aggregation.
   */
  $config['system.performance']['css']['preprocess'] = FALSE;
  $config['system.performance']['js']['preprocess'] = FALSE;
  $config['advagg.settings']['enabled'] = FALSE;

  /**
   * Disable Page and Render Cache.
   */
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
  $settings['cache']['bins']['render'] = 'cache.backend.null';
  $settings['cache']['bins']['page'] = 'cache.backend.null';
  $settings['cache']['bins']['discovery'] = 'cache.backend.null';
}

/**
 * Allow test modules and themes to be installed.
 *
 * Drupal ignores test modules and themes by default for performance reasons.
 * During development it can be useful to install test extensions for debugging
 * purposes.
 */
$settings['extension_discovery_scan_tests'] = FALSE;
