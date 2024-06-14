<?php
$databases['default']['default'] = array (
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
// Use the TUGBOAT_REPO_ID to generate a hash salt for Tugboat sites.
// Adding a test comment to test creating a pull request. Modifying to test more.
$settings['hash_salt'] = hash('sha256', getenv('TUGBOAT_REPO_ID'));

$settings['file_temp_path'] = '/tmp';

// A comment to trigger a pull request
$settings['trusted_host_patterns'] = array(
  '^.+\.tugboatqa\.com$',
);
// Adds dev GTM container
$config['google_tag.container.primary']['container_id'] = '';
