# Paragon installation template using Composer

Paragon is a Drupal distribution focused on providing a clean starting point for site builds with just the right amount of baked in configuration.This allows developers to focus on building custom functionality, rather than recreating the same standard set of baseline features they’ve built 100 times before.

The intent of this distribution is to create a Drupal install that will be treated as an artifact and maintained independently of this project after the initial installation. As such, rather than making this an official install profile, Paragon is instead managed as a Composer template that heavily leverages [drupal/core-composer-scaffold](https://github.com/drupal/core-composer-scaffold) and also includes exported configuration that can be used to install the site.

To create a new Paragon installation follow the steps below:

1. Using Composer 2, run the following command:  `composer create-project elevatedthird/paragon-base [install_directory_name]`, which will clone down the composer template and create all necessary files.
2. Set up a local site using this newly created site directory. Paragon comes with DrupalVM out of the box, which can be spun up by running `vagrant up` in the site root. DrupalVM configuration is found in `settings/config.yml` , and database settings in `settings.drupalvm.php` are automatically included in `settings.php`.
3. With a local site running, navigate to http://paragon.dvm (or whichever URL your local site is running on) and proceed with the Drupal installation. When prompted to select an installation profile be sure to select “Use existing configuration”, which will install from the existing configuration in the  `/config/default` directory.
4. If using DrupalVM, the when prompted to add Drupal database connection details, use `drupal` for the database name, user, and password.
5. Once install completes, be sure to remove the automatically generated database connection details that have most likely been appended to the bottom of `settings.php`, then you should be all set!
