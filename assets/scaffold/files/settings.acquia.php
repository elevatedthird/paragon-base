<?php

$acquia_site_name = 'paragondev';
if (file_exists('/var/www/site-php')) {
  require("/var/www/site-php/{$acquia_site_name}/{$acquia_site_name}-settings.inc");
}

$settings['file_private_path'] = "/mnt/files/{$acquia_site_name}.{$_ENV['AH_SITE_ENVIRONMENT']}/files-private";
$settings['file_temp_path'] = "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/tmp";

/**
 * Include Acquia Cloud Memcache configuration when available.
 * Note that this is NOT needed once a server is upgraded to Acquia Cloud Next.
 * @see https://docs.acquia.com/cloud-platform/performance/memcached/enable/
 */
if (file_exists(DRUPAL_ROOT . '/sites/default/cloud-memcache-d8+.php')) {
  require(DRUPAL_ROOT . '/sites/default/cloud-memcache-d8+.php');
}

// Enable CSS and JS preprocessing
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;

// Set GTM Code default
$config['google_tag.settings']['container_id'] = '';

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
$config['environment_indicator.indicator']['name'] = $_ENV['AH_SITE_ENVIRONMENT'] . ' environment';

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
    $config['environment_indicator.indicator']['name'] = $_ENV['AH_SITE_ENVIRONMENT'] . ' environment'  . ' [Master DB]';
    $config['environment_indicator.indicator']['bg_color'] = '#006002';

    /**
     * Override robots.txt so development sites are not crawled.
     */
    $config['robotstxt.settings']['content'] = "#\r\n# robots.txt\r\n#\r\n# This file is to prevent the crawling and indexing of certain parts\r\n# of your site by web crawlers and spiders run by sites like Yahoo!\r\n# and Google. By telling these \"robots\" where not to go on your site,\r\n# you save bandwidth and server resources.\r\n#\r\n# This file will be ignored unless it is at the root of your host:\r\n# Used:    http://example.com/robots.txt\r\n# Ignored: http://example.com/site/robots.txt\r\n#\r\n# For more information about the robots.txt standard, see:\r\n# http://www.robotstxt.org/robotstxt.html\r\n\r\nUser-agent: *\r\nDisallow: /\r\n";

    break;
  case 'test':
    $config['environment_indicator.indicator']['bg_color'] = '#E7C600';
    $config['environment_indicator.indicator']['fg_color'] = '#000000';

    /**
     * Override robots.txt so development sites are not crawled.
     */
    $config['robotstxt.settings']['content'] = "#\r\n# robots.txt\r\n#\r\n# This file is to prevent the crawling and indexing of certain parts\r\n# of your site by web crawlers and spiders run by sites like Yahoo!\r\n# and Google. By telling these \"robots\" where not to go on your site,\r\n# you save bandwidth and server resources.\r\n#\r\n# This file will be ignored unless it is at the root of your host:\r\n# Used:    http://example.com/robots.txt\r\n# Ignored: http://example.com/site/robots.txt\r\n#\r\n# For more information about the robots.txt standard, see:\r\n# http://www.robotstxt.org/robotstxt.html\r\n\r\nUser-agent: *\r\nDisallow: /\r\n";
    break;
  case 'prod':
    // Turn off shield on prod
    // TODO: Disable before site launch
    $config['shield.settings']['shield_enable'] = true;

    $config['environment_indicator.indicator']['bg_color'] = '#930007';

    // Set logging level on production.
    $config['system.logging']['error_level'] = 'hide';

    // Set GTM Code
    // TODO: Enable gtm before site launch
    $config['gtm.settings']['enable'] = 0;
    break;
}
