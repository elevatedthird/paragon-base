<?php

/**
 * Config settings
 * @TODO: Move to the repo root
 */

$config_directories = array(
  CONFIG_SYNC_DIRECTORY => '../config/default',
);

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
$settings['container_yamls'][] = __DIR__ . '/services.yml';


/**
 * Trusted host configuration.
 *
 * Drupal core can use the Symfony trusted host mechanism to prevent HTTP Host
 * header spoofing.
 *
 * To enable the trusted host mechanism, you enable your allowable hosts
 * in $settings['trusted_host_patterns']. This should be an array of regular
 * expression patterns, without delimiters, representing the hosts you would
 * like to allow.
 *
 * For example:
 * @code
 * $settings['trusted_host_patterns'] = array(
 *   '^www\.example\.com$',
 * );
 * @endcode
 * will allow the site to only run from www.example.com.
 *
 * If you are running multisite, or if you are running your site from
 * different domain names (eg, you don't redirect http://www.example.com to
 * http://example.com), you should specify all of the host patterns that are
 * allowed by your site.
 *
 * For example:
 * @code
 * $settings['trusted_host_patterns'] = array(
 *   '^example\.com$',
 *   '^.+\.example\.com$',
 *   '^example\.org$',
 *   '^.+\.example\.org$',
 * );
 * @endcode
 * will allow the site to run off of all variants of example.com and
 * example.org, with all subdomains included.
 */

// Trusted host patterns for e3develop and e3stanging. Make sure to add appropriate variations for production domain
// and any additional version thereof.
// Additional env specific patterns can be added in the following files (drupalvm, local)
$settings['trusted_host_patterns'] = array(
  '^e3develop\.com$',
  '^.+\.e3develop\.com$',
  '^e3staging\.com$',
  '^.+\.e3staging\.com$',
);

// Set default paths to public, private and temp directories.
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = '../private';
$settings['file_temp_path'] = '../private/tmp';

// Remove shield print message by default.
$config['shield.settings']['print'] = '';

// Allow cli to bypass shield.
$config['shield.settings']['allow_cli'] = TRUE;

// Set logging level default.
$config['system.logging']['error_level'] = 'all';

// Set Google Analytics to NULL, override this for production environment.
$config['']['account'] = '';

// Disable dev modules on all environments by default.
$config['config_split.config_split.config_dev']['status'] = FALSE;

// Add fast404 settings
if (file_exists($app_root . '/' . $site_path . '/settings.fast404.php')) {
  include $app_root . '/' . $site_path . '/settings.fast404.php';
}

// If $_ENV['AH_SITE_ENVIRONMENT'], load Acquia settings.
if(isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  if (file_exists(__DIR__ . '/settings.acquia.php')) {
    include __DIR__ . '/settings.acquia.php';

  }
}

// If drupal-vm settings exist, load them.
elseif (file_exists(__DIR__ . '/settings.drupalvm.php')) {
  include __DIR__ . '/settings.drupalvm.php';
}

// If local settings file exists, load it.
if(file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
