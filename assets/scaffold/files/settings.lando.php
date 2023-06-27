<?php

// @codingStandardsIgnoreFile
$lando_info = json_decode(getenv('LANDO_INFO'), TRUE);

/**
 * Configure Lando DB.
 */
$databases['default']['default'] = [
    'database' => $lando_info['database']['creds']['database'],
    'username' => $lando_info['database']['creds']['user'],
    'password' => $lando_info['database']['creds']['password'],
    'prefix' => '',
    'host' => $lando_info['database']['internal_connection']['host'],
    'port' => $lando_info['database']['internal_connection']['port'],
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
];

/**
 * Local file directory configuration
 */
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = 'sites/default/files/private';
$settings['file_temp_path'] = 'sites/default/files/private/tmp';

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
$config['environment_indicator.indicator']['name'] = 'Lando';

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
 * Add lando to trusted host patterns.
 */
$settings['trusted_host_patterns'] = [
    '^.+\.lndo\.site',
];

/**
 * Disable shield on local.
 */
$config['shield.settings']['shield_enable'] = false;

/**
 * Override robots.txt so development sites are not crawled.
 */
$config['robotstxt.settings']['content'] = "#\r\n# robots.txt\r\n#\r\n# This file is to prevent the crawling and indexing of certain parts\r\n# of your site by web crawlers and spiders run by sites like Yahoo!\r\n# and Google. By telling these \"robots\" where not to go on your site,\r\n# you save bandwidth and server resources.\r\n#\r\n# This file will be ignored unless it is at the root of your host:\r\n# Used:    http://example.com/robots.txt\r\n# Ignored: http://example.com/site/robots.txt\r\n#\r\n# For more information about the robots.txt standard, see:\r\n# http://www.robotstxt.org/robotstxt.html\r\n\r\nUser-agent: *\r\nDisallow: /\r\n";
