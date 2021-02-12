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
  }

  public static function removeGitSubmodules (Event $event) {
    exec("find " . getcwd() . "'/vendor' -not -path \"*geerlingguy/drupal-vm*\" | grep '.git$' | xargs rm -rf");
    exec("find " . getcwd() . "'/docroot/modules/contrib' | grep '.git$' | xargs rm -rf");
    exec("find " . getcwd() . "'/docroot/profiles/contrib' | grep '.git$' | xargs rm -rf");
    exec("find " . getcwd() . "'/docroot/themes/contrib' | grep '.git$' | xargs rm -rf");
    $event->getIO()->write("Removed all .git files from vendor and contrib.");
  }

  public static function createRequiredDirectories (Event $event) {
    $fs = new Filesystem();
    $project_root = getcwd();
    $drupal_root = static::getDrupalRoot($project_root);

    $dirs = array(
      'private',
      'private/tmp',
      'media-icons',
      'media-icons/generic'
    );

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupal_root . '/sites/default/files/'. $dir)) {
        $fs->mkdir($drupal_root . '/sites/default/files/'. $dir);
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
    elseif (Comparator::lessThan($version, '2.0.0')) {
      $io->writeError('<error>Paragon requires Composer version 2.0.0 or higher. Please update your Composer before continuing.</error>');
      exit(1);
    }
  }
}
