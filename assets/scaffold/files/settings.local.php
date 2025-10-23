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

$settings['vite'] = [
  'devServerUrl' => 'https://' . $_ENV['DDEV_HOSTNAME'] . ':5173',
  'baseUrl' => '/themes/custom/kinetic/dist/',
  'devDependencies' => [
    'kinetic/vite-client'
  ],
];

/**
 * Local file directory configuration
 */
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = 'sites/default/files/private';
$settings['file_temp_path'] = 'sites/default/files/private/tmp';
/**
 * Allow test modules and themes to be installed.
 *
 * Drupal ignores test modules and themes by default for performance reasons.
 * During development it can be useful to install test extensions for debugging
 * purposes.
 */
$settings['extension_discovery_scan_tests'] = FALSE;
/**
 * Enable access to rebuild.php.
 *
 * This setting can be enabled to allow Drupal's php and database cached
 * storage to be cleared via the rebuild.php page. Access to this page can also
 * be gained by generating a query string from rebuild_token_calculator.sh and
 * using these parameters in a request to rebuild.php.
 */
$settings['rebuild_access'] = TRUE;

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;

/**
 * Environment Indicator Settings.
 *
 * This should be configured per environment.
 *
 * Local
 * Background: #005093
 * Foreground: #FFFFFF
 *
 * Dev
 * Background: #006002
 * Foreground: #FFFFFF
 *
 * Stage
 * Background: #E7C600
 * Foreground: #000000
 *
 * Prod
 * Background: #930007
 * Foreground: #FFFFFF
 */
$config['environment_indicator.indicator']['bg_color'] = '#005093';
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'DDEV';

/**
 * Acquia Connector Settings
 *
 * Hide signup messages by default.
 */
$config['acquia_connector.settings']['hide_signup_messages'] = TRUE;

/**
 * Disable Google Tag Manager on local.
 */
$config['gtm.settings']['enable'] = 0;

/**
 * Set logging level on lando.
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Disable shield on local.
 */
$config['shield.settings']['shield_enable'] = false;

/**
 * Override robots.txt so development sites are not crawled.
 */
$config['robotstxt.settings']['content'] = "#\r\n# robots.txt\r\n#\r\n# This file is to prevent the crawling and indexing of certain parts\r\n# of your site by web crawlers and spiders run by sites like Yahoo!\r\n# and Google. By telling these \"robots\" where not to go on your site,\r\n# you save bandwidth and server resources.\r\n#\r\n# This file will be ignored unless it is at the root of your host:\r\n# Used:    http://example.com/robots.txt\r\n# Ignored: http://example.com/site/robots.txt\r\n#\r\n# For more information about the robots.txt standard, see:\r\n# http://www.robotstxt.org/robotstxt.html\r\n\r\nUser-agent: *\r\nDisallow: /\r\n";