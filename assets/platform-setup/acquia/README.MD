#Instructions
1. Move the "hooks" directory to the root of your repository.
2. Move the acquia.yml.disable file to /github/workflows/acquia.yml
3. Move the settings.acquia.php file to /sites/default/settings.acquia.php
4. Ensure you properly include the settings.acquia.php file in your settings.php file.

```php
if(isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.acquia.php')) {
    include $app_root . '/' . $site_path . '/settings.acquia.php';
  }
}
```
