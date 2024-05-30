<?php

//@condingStandardsIgnoreFile

/**
 * Config settings
 */
$settings['config_sync_directory'] = '../config/default';

/**
 * Hash salt used for one-time login links, etc.
 */
$settings['hash_salt'] = '';

/**
 * Access control for update.php script.
 */
$settings['update_free_access'] = FALSE;

/**
 * Authorized file system operations.
 */
$settings['allow_authorize_operations'] = FALSE;

/**
 * Default mode for directories and files written by Drupal.
 */
$settings['file_chmod_directory'] = 0775;
$settings['file_chmod_file'] = 0664;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

/**
 * Trusted host patterns for e3develop and e3stanging. Make sure to add appropriate variations for production domain
 * and any additional version thereof.
 * Additional env specific patterns can be added in the following files (lando, local)
 */
$settings['trusted_host_patterns'] = [];

/**
 * Set default paths to public, private and temp directories.
 */
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = '../private';
$settings['file_temp_path'] = '../private/tmp';

/**
 * Remove shield print message by default.
 */
$config['shield.settings']['print'] = '';

/**
 * Allow cli to bypass shield.
 */
$config['shield.settings']['allow_cli'] = TRUE;

/**
 * Set logging level default.
 */
$config['system.logging']['error_level'] = 'all';

/**
 * The default list of directories that will be ignored by Drupal's file API.
 */
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

/**
 * The default number of entities to update in a batch process.
 */
$settings['entity_update_batch_size'] = 50;

/**
 * Exclude modules from configuration synchronization.
 */
$settings['config_exclude_modules'] = ['devel', 'stage_file_proxy'];

/**
 * Add fast404 settings
 */
if (file_exists($app_root . '/' . $site_path . '/settings.fast404.php')) {
  include $app_root . '/' . $site_path . '/settings.fast404.php';
}

/**
 * If $_ENV['AH_SITE_ENVIRONMENT'], load Acquia settings.
 */
if(isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.acquia.php')) {
    include $app_root . '/' . $site_path . '/settings.acquia.php';
  }
}

/**
 * If $_ENV['LANDO_APP_NAME'], load docker settings.
 */
elseif(isset($_ENV['LANDO_APP_NAME'])) {
    if (file_exists($app_root . '/' . $site_path . '/settings.lando.php')) {
        include $app_root . '/' . $site_path . '/settings.lando.php';
    }
}

/**
 * If local settings file exists, load it.
 */
if(file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
