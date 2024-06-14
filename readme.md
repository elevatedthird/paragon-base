# Paragon installation template using Composer

Paragon is a Drupal distribution focused on providing a clean starting point for site builds with just the right amount of baked in configuration.This allows developers to focus on building custom functionality, rather than recreating the same standard set of baseline features they’ve built 100 times before.

The intent of this distribution is to create a Drupal install that will be treated as an artifact and maintained independently of this project after the initial installation. As such, rather than making this an official install profile, Paragon is instead managed as a Composer template that heavily leverages [drupal/core-composer-scaffold](https://github.com/drupal/core-composer-scaffold) and also includes exported configuration that can be used to install the site.

### Prerequisites
- Lando: See [Lando requirements](https://docs.lando.dev/basics/installation.html).
- Access to Elevated Third Github organization, Paragon-base repository and SSH key setup.
- You must be using Composer 2.
  - To install a specific version use this command and pass in the version constraing: `composer self-update 2.0.7` 
  - If you have already installed and need to switch to compsoer 2 run `composer self-update --2`.

## Setup instructions

To create a new Paragon installation follow the steps below:

### Step #1: Clone repository
1. Run the following command:  `composer create-project elevatedthird/paragon-base [install_directory_name]` which will clone down the composer template and create all necessary files.
2. You will prompted to select a hosting environment for your project. Select 'custom' if you don't want platform specific files. You can set up hosting requirements later by running `composer setup-platform`

### Step #2: Project setup
1. Set up a local site using this newly created site directory. Paragon comes with Lando out of the box, which can be spun up by running `lando start` in the site root. Lando configuration is found in `.lando.yml` , and database settings in `settings.lando.php` are automatically included in `settings.php`.

2. Be sure to rename the the app in the .lando.yml file

### Step #3: Site setup
1. With a local site running, navigate to http://paragon.lndo.site/ (or whichever URL your local site is running on) and proceed with the Drupal installation. When prompted to select an installation profile be sure to select “Use existing configuration”, which will install from the existing configuration in the  `/config/default` directory.

### Step #4: Database setup
1. If using Lando, when prompted to add Drupal database connection details. You can find the connection info by running `lando info`

### Step #5: Database setup
1. Once install completes, be sure to remove the automatically generated database connection details that have most likely been appended to the bottom of `settings.php`, then you should be all set!

### Step #6: Setting up Drush
1. Run `mkdir -p drush/sites` from the project root


## Common commands
### Some common commands that may be helpful
  - `lando start/stop`
  - `lando poweroff`
  - `lando rebuild` - completely rebuilds the site and project containers. This is most useful if you keep running into lando issues
  - `composer install`
  - `composer depends [vendor/package]`
  - `composer show [vendor/package]`

### Xdebug Commands:
  - `lando xdebug debug`: Enables Step Debugging. This can be used to step through your code while it is running, and analyse values of variables.
  - `lando xdebug`: Turns off xdebug.
  - `lando xdebug develop`: Enables Development Helpers including the overloaded var_dump().
  - `lando xdebug coverage`: Enables Code Coverage Analysis to generate code coverage reports, mainly in combination with PHPUnit.
  - `lando xdebug gcstats`: Enables Garbage Collection Statistics to collect statistics about PHP's Garbage Collection Mechanism.
  - `lando xdebug profile`: EnableEnables Profiling, with which you can analyse performance bottlenecks with tools like KCacheGrind.
  - `lando xdebug trace`: Enables the Function Trace feature, which allows you record every function call, including arguments, variable assignment, and return value that is made during a request to a file.
The most common xdebug commands are debug and off but these other modes are available as well.

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
- [Kinetic](https://www.drupal.org/project/kinetic)