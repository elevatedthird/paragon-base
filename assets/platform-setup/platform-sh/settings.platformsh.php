<?php
/**
 * @file
 * Platform.sh settings.
 */

use Drupal\Core\Installer\InstallerKernel;

$platformsh = new \Platformsh\ConfigReader\Config();

// Configure the database.
if ($platformsh->hasRelationship('database')) {
  $creds = $platformsh->credentials('database');
  $databases['default']['default'] = [
    'driver' => $creds['scheme'],
    'database' => $creds['path'],
    'username' => $creds['username'],
    'password' => $creds['password'],
    'prefix' => '',
    'host' => $creds['host'],
    'port' => $creds['port'],
    'pdo' => [PDO::MYSQL_ATTR_COMPRESS => !empty($creds['query']['compression'])]
  ];
}

// Enable verbose error messages on development branches, but not on the production branch.
// You may add more debug-centric settings here if desired to have them automatically enable
// on development but not production.
if (isset($platformsh->branch)) {
  // Enable shield for all environments.
  $config['shield.settings']['shield_enable'] = true;
  $config['gtm.settings']['enable'] = 0;
  // Production type environment.
  if ($platformsh->branch == 'master' || $platformsh->onDedicated()) {
    $config['shield.settings']['shield_enable'] = false;
    $config['system.logging']['error_level'] = 'hide';
  } // Development type environment.
  else {
    //    $config['robotstxt.settings']['content'] = "#\r\n# robots.txt\r\n#\r\n# This file is to prevent the crawling and indexing of certain parts\r\n# of your site by web crawlers and spiders run by sites like Yahoo!\r\n# and Google. By telling these \"robots\" where not to go on your site,\r\n# you save bandwidth and server resources.\r\n#\r\n# This file will be ignored unless it is at the root of your host:\r\n# Used:    http://example.com/robots.txt\r\n# Ignored: http://example.com/site/robots.txt\r\n#\r\n# For more information about the robots.txt standard, see:\r\n# http://www.robotstxt.org/robotstxt.html\r\n\r\nUser-agent: *\r\nDisallow: /\r\n";
    $config['system.logging']['error_level'] = 'verbose';
  }
}

// Enable Redis caching.
if ($platformsh->hasRelationship('redis') && !InstallerKernel::installationAttempted() && extension_loaded('redis')) {
  $redis = $platformsh->credentials('redis');

  // Set Redis as the default backend for any cache bin not otherwise specified.
  $settings['cache']['default'] = 'cache.backend.redis';
  $settings['redis.connection']['host'] = $redis['host'];
  $settings['redis.connection']['port'] = $redis['port'];

  // Apply changes to the container configuration to better leverage Redis.
  // This includes using Redis for the lock and flood control systems, as well
  // as the cache tag checksum. Alternatively, copy the contents of that file
  // to your project-specific services.yml file, modify as appropriate, and
  // remove this line.
  $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

  // Allow the services to work before the Redis module itself is enabled.
  $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

  // Manually add the classloader path, this is required for the container cache bin definition below
  // and allows to use it without the redis module being enabled.
  $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');

  // Use redis for container cache.
  // The container cache is used to load the container definition itself, and
  // thus any configuration stored in the container itself is not available
  // yet. These lines force the container cache to use Redis rather than the
  // default SQL cache.
  $settings['bootstrap_container_definition'] = [
    'parameters' => [],
    'services' => [
      'redis.factory' => [
        'class' => 'Drupal\redis\ClientFactory',
      ],
      'cache.backend.redis' => [
        'class' => 'Drupal\redis\Cache\CacheBackendFactory',
        'arguments' => ['@redis.factory', '@cache_tags_provider.container', '@serialization.phpserialize'],
      ],
      'cache.container' => [
        'class' => '\Drupal\redis\Cache\PhpRedis',
        'factory' => ['@cache.backend.redis', 'get'],
        'arguments' => ['container'],
      ],
      'cache_tags_provider.container' => [
        'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
        'arguments' => ['@redis.factory'],
      ],
      'serialization.phpserialize' => [
        'class' => 'Drupal\Component\Serialization\PhpSerialize',
      ],
    ],
  ];
}

if ($platformsh->inRuntime()) {
  // Configure private and temporary file paths.
  $settings['file_private_path'] = $platformsh->appDir . '/private';
  $settings['file_temp_path'] = $platformsh->appDir . '/tmp';

  // Configure the default PhpStorage and Twig template cache directories.
  if (!isset($settings['php_storage']['default'])) {
    $settings['php_storage']['default']['directory'] = $settings['file_private_path'];
  }
  if (!isset($settings['php_storage']['twig'])) {
    $settings['php_storage']['twig']['directory'] = $settings['file_private_path'];
  }

  // Set the project-specific entropy value, used for generating one-time
  // keys and such.
  $settings['hash_salt'] = $settings['hash_salt'] ?? $platformsh->projectEntropy;

  // Set the deployment identifier, which is used by some Drupal cache systems.
  $settings['deployment_identifier'] = $settings['deployment_identifier'] ?? $platformsh->treeId;
}

// The 'trusted_hosts_pattern' setting allows an admin to restrict the Host header values
// that are considered trusted.  If an attacker sends a request with a custom-crafted Host
// header then it can be an injection vector, depending on how the Host header is used.
// However, Platform.sh already replaces the Host header with the route that was used to reach
// Platform.sh, so it is guaranteed to be safe.  The following line explicitly allows all
// Host headers, as the only possible Host header is already guaranteed safe.
$settings['trusted_host_patterns'] = ['.*'];

// Import variables prefixed with 'd8settings:' into $settings
// and 'd8config:' into $config.
foreach ($platformsh->variables() as $name => $value) {
  $parts = explode(':', $name);
  list($prefix, $key) = array_pad($parts, 3, null);
  switch ($prefix) {
    // Variables that begin with `d8settings` or `drupal` get mapped
    // to the $settings array verbatim, even if the value is an array.
    // For example, a variable named d8settings:example-setting' with
    // value 'foo' becomes $settings['example-setting'] = 'foo';
    case 'd8settings':
    case 'drupal':
      $settings[$key] = $value;
      break;
    // Variables that begin with `d8config` get mapped to the $config
    // array.  Deeply nested variable names, with colon delimiters,
    // get mapped to deeply nested array elements. Array values
    // get added to the end just like a scalar. Variables without
    // both a config object name and property are skipped.
    // Example: Variable `d8config:conf_file:prop` with value `foo` becomes
    // $config['conf_file']['prop'] = 'foo';
    // Example: Variable `d8config:conf_file:prop:subprop` with value `foo` becomes
    // $config['conf_file']['prop']['subprop'] = 'foo';
    // Example: Variable `d8config:conf_file:prop:subprop` with value ['foo' => 'bar'] becomes
    // $config['conf_file']['prop']['subprop']['foo'] = 'bar';
    // Example: Variable `d8config:prop` is ignored.
    case 'd8config':
      if (count($parts) > 2) {
        $temp = &$config[$key];
        foreach (array_slice($parts, 2) as $n) {
          $prev = &$temp;
          $temp = &$temp[$n];
        }
        $prev[$n] = $value;
      }
      break;
  }
}

// Send platform mail through platform's SMTP server
// This appears to only be set on master
if(isset($_ENV['PLATFORM_SMTP_HOST'])) {
  $config['swiftmailer.transport']['transport'] = 'smtp';
  $config['swiftmailer.transport']['smtp_port'] = '25';
  $config['swiftmailer.transport']['smtp_host'] = $_ENV['PLATFORM_SMTP_HOST'];
}