1. Move the "platformsh_generate_drush_yml.php" into the drush directory in the root of your repository.
2. Move the platformsh.app.yaml, .environment, and .platform files/directory to the root of your repository.
3. Move the platform.yml.disable file to /github/workflows/platform.yml
4. run `composer require platformsh/config-reader`
5. Ensure you properly include the settings.platformsh.php file in your settings.php file.

```php
/**
 * If $_ENV['PLATFORM_ENVIRONMENT'], load Platform.sh settings.
 */
if (isset($_ENV['PLATFORM_PROJECT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.platformsh.php')) {
    include $app_root . '/' . $site_path . '/settings.platformsh.php';
  }
}

```

