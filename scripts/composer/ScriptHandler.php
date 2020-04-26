<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use Composer\Semver\Comparator;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler {

  protected static function getDrupalRoot($project_root) {
    return $project_root . '/docroot';
  }

  public static function createRequiredFiles(Event $event) {
    $fs = new Filesystem();
    $project_root = getcwd();
    $drupal_root = static::getDrupalRoot($project_root);

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupal_root . '/'. $dir)) {
        $fs->mkdir($drupal_root . '/'. $dir);
        $fs->touch($drupal_root . '/'. $dir . '/.gitkeep');
      }
    }

    // Copy over custom .htaccess file.
    if (!$fs->exists($drupal_root . '/.htaccess') and $fs->exists($project_root . '/scaffold/.htaccess')) {
      $fs->copy($project_root . '/scaffold/.htaccess', $drupal_root . '/.htaccess');
      $event->getIO()->write("Copied custom .htaccess from scaffold into docroot.");
    }

    // Copy over robots_hidden.txt
    if (!$fs->exists($drupal_root . '/robots_hidden.txt') and $fs->exists($project_root . '/scaffold/robots_hidden.txt')) {
      $fs->copy($project_root . '/scaffold/robots_hidden.txt', $drupal_root . '/robots_hidden.txt');
      $event->getIO()->write("Copied robots_hidden.txt from scaffold into docroot.");
    }

    // Copy over custom settings.php and services.yml files
    if (!$fs->exists($drupal_root . '/sites/default/example.settings.php') and $fs->exists($project_root . '/scaffold/example.settings.php')) {
      $fs->copy($project_root . '/scaffold/example.settings.php', $drupal_root . '/sites/default/example.settings.php');
      $event->getIO()->write("Copied example.settings.php from scaffold into docroot.");
    }
    if (!$fs->exists($drupal_root . '/sites/default/local.services.yml') and $fs->exists($project_root . '/scaffold/local.services.yml')) {
      $fs->copy($project_root . '/scaffold/local.services.yml', $drupal_root . '/sites/default/local.services.yml');
      $event->getIO()->write("Copied local.services.yml from scaffold into docroot.");
    }
    if (!$fs->exists($drupal_root . '/sites/default/settings.acquia.php') and $fs->exists($project_root . '/scaffold/settings.acquia.php')) {
      $fs->copy($project_root . '/scaffold/settings.acquia.php', $drupal_root . '/sites/default/settings.acquia.php');
      $event->getIO()->write("Copied settings.acquia.php from scaffold into docroot.");
    }
    if (!$fs->exists($drupal_root . '/sites/default/settings.drupalvm.php') and $fs->exists($project_root . '/scaffold/settings.drupalvm.php')) {
      $fs->copy($project_root . '/scaffold/settings.drupalvm.php', $drupal_root . '/sites/default/settings.drupalvm.php');
      $event->getIO()->write("Copied settings.drupalvm.php from scaffold into docroot.");
    }
    if (!$fs->exists($drupal_root . '/sites/default/settings.fast404.php') and $fs->exists($project_root . '/scaffold/settings.fast404.php')) {
      $fs->copy($project_root . '/scaffold/settings.fast404.php', $drupal_root . '/sites/default/settings.fast404.php');
      $event->getIO()->write("Copied settings.fast404.php from scaffold into docroot.");
    }

    // Prepare the settings file for installation
    if (!$fs->exists($drupal_root . '/sites/default/settings.php') and $fs->exists($drupal_root . '/sites/default/default.settings.php')) {
      $fs->copy($drupal_root . '/sites/default/default.settings.php', $drupal_root . '/sites/default/settings.php');
      require_once $drupal_root . '/core/includes/bootstrap.inc';
      require_once $drupal_root . '/core/includes/install.inc';
      $fs->chmod($drupal_root . '/sites/default/settings.php', 0666);
      $event->getIO()->write("Create a sites/default/settings.php file with chmod 0666");
    }

    // Prepare the services file for installation
    if (!$fs->exists($drupal_root . '/sites/default/services.yml') and $fs->exists($drupal_root . '/sites/default/default.services.yml')) {
      $fs->copy($drupal_root . '/sites/default/default.services.yml', $drupal_root . '/sites/default/services.yml');
      $fs->chmod($drupal_root . '/sites/default/services.yml', 0666);
      $event->getIO()->write("Create a sites/default/services.yml file with chmod 0666");
    }

    // Create the files directory with chmod 0777
    if (!$fs->exists($drupal_root . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($drupal_root . '/sites/default/files', 0777);
      umask($oldmask);
      $event->getIO()->write("Create a sites/default/files directory with chmod 0777");
    }
  }

  public static function removeGitSubmodules (Event $event) {
    exec("find " . getcwd() . "'/vendor' -not -path \"*geerlingguy/drupal-vm*\" | grep '.git$' | xargs rm -rf");
    exec("find " . getcwd() . "'/docroot/modules/contrib' | grep '.git$' | xargs rm -rf");
    exec("find " . getcwd() . "'/docroot/profiles/contrib' | grep '.git$' | xargs rm -rf");
    exec("find " . getcwd() . "'/docroot/themes/contrib' | grep '.git$' | xargs rm -rf");
    $event->getIO()->write("Removed all .git files from vendor and contrib.");
  }

  public static function createPrivateTempDirectories (Event $event) {
    $fs = new Filesystem();
    $drupal_root = '.';

    $dirs = array(
      'private',
      'private/tmp',
    );

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupal_root . '/'. $dir)) {
        $fs->mkdir($drupal_root . '/'. $dir);
        $event->getIO()->write("Created directory \"$dir\".");
      }
    }
  }

  /**
   * Checks if the installed version of Composer is compatible.
   *
   * Composer 1.0.0 and higher consider a `composer install` without having a
   * lock file present as equal to `composer update`. We do not ship with a lock
   * file to avoid merge conflicts downstream, meaning that if a project is
   * installed with an older version of Composer the scaffolding of Drupal will
   * not be triggered. We check this here instead of in drupal-scaffold to be
   * able to give immediate feedback to the end user, rather than failing the
   * installation after going through the lengthy process of compiling and
   * downloading the Composer dependencies.
   *
   * @see https://github.com/composer/composer/pull/5035
   */
  public static function checkComposerVersion(Event $event) {
    $composer = $event->getComposer();
    $io = $event->getIO();

    $version = $composer::VERSION;

    // If Composer is installed through git we have no easy way to determine if
    // it is new enough, just display a warning.
    if ($version === '@package_version@') {
      $io->writeError('<warning>You are running a development version of Composer. If you experience problems, please update Composer to the latest stable version.</warning>');
    }
    elseif (Comparator::lessThan($version, '1.0.0')) {
      $io->writeError('<error>Drupal-project requires Composer version 1.0.0 or higher. Please update your Composer before continuing</error>.');
      exit(1);
    }
  }

}
