<?php

$acquia_site_name = 'paragondev';
if (file_exists('/var/www/site-php')) {
  require("/var/www/site-php/{$acquia_site_name}/{$acquia_site_name}-settings.inc");
}

$settings['file_private_path'] = "/mnt/files/{$acquia_site_name}.{$_ENV['AH_SITE_ENVIRONMENT']}/files-private";
$settings['file_temp_path'] = "/mnt/tmp/{$acquia_site_name}.{$_ENV['AH_SITE_ENVIRONMENT']}";

// https://docs.acquia.com/article/drupal-8-cache-backend
$settings['cache']['default'] = 'cache.backend.database';

// Force common chainedfast bins to use Memcache.
$settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
$settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
$settings['cache']['bins']['render'] = 'cache.backend.memcache';
$settings['cache']['bins']['data'] = 'cache.backend.memcache';
$settings['cache']['bins']['config'] = 'cache.backend.memcache';
$settings['cache']['bins']['menu'] = 'cache.backend.memcache';
$settings['cache']['bins']['entity'] = 'cache.backend.memcache';

// Enable CSS and JS preprocessing
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;

// Set GTM Code default
$config['google_tag.settings']['container_id'] = '';

/**
 * Environment Indicator Settings
 *
 * This should be configured per environment.
 *
 * Drupal VM | #005093
 *
 * For environment with canon DB:
 * <Environment> [Master DB] | #000000
 *
 * For environment with dispensable DB:
 * <Environment> | #930007
 *
 * Available environments include:
 *
 * Acquia dev
 * Acquia test
 * Acquia prod
 */
$config['environment_indicator.indicator']['bg_color'] = '#930007';
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Acquia ' . $_ENV['AH_SITE_ENVIRONMENT'];

// Set trusted host pattern for the acquia paragon site. We need to set this because we cannot add additional
// aliases to a free acquia account. This can be deleted for any new project created from paragon.
$settings['trusted_host_patterns'][] = 'paragondevansnwocpp3.devcloud.acquia-sites.com';
$settings['trusted_host_patterns'][] = 'paragondevmsusi7dabk.devcloud.acquia-sites.com';
$settings['trusted_host_patterns'][] = 'pargon.ac.e3develop.com';

/**
 * Set default config_readonly status to TRUE on all Acquia environments.
 * This allows all changes via the command line and enable readonly mode for the UI only.
 */
if (PHP_SAPI !== 'cli') {
  $settings['config_readonly'] = TRUE;

  // @TODO: Whitelist other conifg files on as needed basis.
  $settings['config_readonly_whitelist_patterns'] = [
    'system.menu.*',
    'core.menu.static_menu_link_overrides',
    'simple_sitemap.*',
    'embed.button.*',
  ];
}

switch ($_ENV['AH_SITE_ENVIRONMENT']) {
  case 'dev':
    // Configure shield for dev environment.
    $config['shield.settings']['credentials']['shield']['user'] = 'paragon';
    $config['shield.settings']['credentials']['shield']['pass'] = '3ditParagon';

    /**
     * Load the development services definition file.
     */
    $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

    /**
     * Master DB and Config Read-Only settings
     *
     * Set the environment indicator for the environment with the Master DB.  This should never be on more than one DB.
     * If non-developers are allowed to modify configuration on the master environment, add the following line:
     *
     * $settings['config_readonly'] = FALSE;
     *
     * NOTE: If set to FALSE, caution should be used when merging in config changes.
     * All Master DB config must be merged into the master branch before merging new config from VCS.
     *
     */
    $config['environment_indicator.indicator']['name'] = 'Acquia ' . $_ENV['AH_SITE_ENVIRONMENT'] . ' [Master DB]';
    $config['environment_indicator.indicator']['bg_color'] = '#000000';

    break;
  case 'test':
    // Configure shield for test environment.
    $config['shield.settings']['credentials']['shield']['user'] = 'paragon';
    $config['shield.settings']['credentials']['shield']['pass'] = '3ditParagon';
    break;
  case 'prod':
    // Configure shield for prod environment.
    // TODO: Disable before site launch
    $config['shield.settings']['credentials']['shield']['user'] = 'paragon';
    $config['shield.settings']['credentials']['shield']['pass'] = '3ditParagon';

    // Set logging level on production.
    $config['system.logging']['error_level'] = 'hide';

    // Set GTM Code
    // TODO: Set this to the live tag manager container ID.
    $config['google_tag.settings']['container_id'] = '';
    break;
}
