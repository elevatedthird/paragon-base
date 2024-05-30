#Instructions
1. Move the pantheon.upstream.yml, pantheon.yml to the root of your repository.
2. Move the pantheon.yml.disable file to /github/workflows/pantheon.yml
3. Move the settings.pantheon.php file to /sites/default/settings.pantheon.php
4. Ensure you properly include the settings.pantheon.php file in your settings.php file.
5. Ensure you change docroot to web
6. Change docroot to web in your composer.json file

```php
if(isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.pantheon.php')) {
    include $app_root . '/' . $site_path . '/settings.pantheon.php';
  }
}
```
