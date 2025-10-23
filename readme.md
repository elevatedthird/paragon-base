# Paragon installation template using Composer

Paragon is a Drupal distribution focused on providing a clean starting point for site builds with just the right amount of baked in configuration.This allows developers to focus on building custom functionality, rather than recreating the same standard set of baseline features they’ve built 100 times before.

The intent of this distribution is to create a Drupal install that will be treated as an artifact and maintained independently of this project after the initial installation. As such, rather than making this an official install profile, Paragon is instead managed as a Composer template that heavily leverages [drupal/core-composer-scaffold](https://github.com/drupal/core-composer-scaffold) and also includes exported configuration that can be used to install the site.

## Prerequisites
- [Download DDEV](https://ddev.com/get-started/) if it is not already installed.
- Access to Elevated Third Github organization, Paragon-base repository and SSH key setup.
- You must be using Composer 2.

## Setup instructions
### Step #1: Clone repository
1. Run the following command:  `composer create-project elevatedthird/paragon-base [install_directory_name]` which will clone down the composer template and create all necessary files.
2. You will prompted to select a hosting environment for your project. Select 'custom' if you don't want platform specific files. You can set up hosting requirements later by running `composer setup-platform`

### Step #2: Project setup
1. Rename the the app in the `.ddev/config.yml` file
2. `ddev start`
3. `ddev composer install`
4. `ddev composer npm-install`

### Step #3: Install Drupal
1. Install the site: `ddev drush si --existing-config --site-name=[SITE_NAME] --account-name=root --account-pass=[PASSWORD] -vv -y`
2. Once install completes, remove the automatically generated database connection details that have most likely been appended to the bottom of `settings.php`.
3. Build the theme: `ddev vite:build`

### Step #4: Set up Solr Search
1. `ddev drush pm:enable search_api_solr_admin`
  - This module is ignored in settings.php
3. Create a new Solr server connection in the UI
2. [Follow the ddev-solr steps](https://github.com/ddev/ddev-solr?tab=readme-ov-file#installation-steps) to connect to Solr
2. Upload the config set to Solr and create the collection. `ddev drush --numShards=1 search-api-solr:upload-configset [YOUR_SERVER_NAME]`


## Essential DDEV commands
  - `ddev xdebug on`
  - `ddev xdebug off`
  - `ddev vite`
  - `ddev vite:build`

## E3 Github Workflows

By default, all Paragon projects have Github Actions enabled. To disable, rename the `.github/workflows/main.yml` to `main.disable`

1. Ensure you have invited `hosting@elevatedthird.com` to your project.
2. Set up the `.env` file located in the project root. These variables will be used as settings for the Github Workflows.
3. If your site is on Pantheon, you will need to uncomment and fill out the `PANTHEON_SITE` var.
4. If you are NOT using kinetic, change the `THEME_NAME` variable to the name of the active theme's folder. Also, change the paths to the theme in the `build-theme` and `npm-install` scripts in composer.json

## Specific Platform Instructions

### Acquia
1. Ensure you have a hooks/dev/post-code-update/drush-deploy.sh
2. Ensure that the code below is present in the settings.php.
```php
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.acquia.php')) {
    include $app_root . '/' . $site_path . '/settings.acquia.php';
  }
}
```

### Pantheon
1. Ensure you change the `docroot` folder name to `web`
2. Change `docroot` to `web` in your `composer.json` file
3. Ensure that the code below is present in the settings.php.
```php
if(isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.pantheon.php')) {
    include $app_root . '/' . $site_path . '/settings.pantheon.php';
  }
}
```

### Platform
1. Ensure your `.platform.app.yaml` deploy hook looks like this
```yaml
  deploy: |
    set -e
    php ./drush/platformsh_generate_drush_yml.php
    cd docroot
    drush deploy
```
2. Ensure that the code below is present in the settings.php.
```php
if (isset($_ENV['PLATFORM_PROJECT'])) {
  if (file_exists($app_root . '/' . $site_path . '/settings.platformsh.php')) {
    include $app_root . '/' . $site_path . '/settings.platformsh.php';
  }
}
```

## Tugboat Integration
By default, Paragon creates a `.tugboat` folder containing configuration related to [Tugboat QA](https://www.tugboatqa.com). You must set up a tugboat project and connect the Github repo to it.

## Related Projects
- [E3 Actions](https://github.com/elevatedthird/actions)
- [Paragon Core](https://www.drupal.org/project/paragon_core)
- [Paragon Gin](https://www.drupal.org/project/paragon_gin)
- [Paragon Mega Menus](https://github.com/elevatedthird/paragon_mega_menus)
- [Kinetic](https://www.drupal.org/project/kinetic)