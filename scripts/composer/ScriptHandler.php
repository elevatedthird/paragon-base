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
    exec("find " . getcwd() . "'/docroot/libraries' | grep '.git$' | xargs rm -rf");
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

  /**
   * Replaces a desired string with a new one in specified file
   *
   * @param $pathToFile
   * @param $newNew
   * @param $oldOld
   */
  public static function fileStringReplace($pathToFile, $newNew, $oldOld)
  {
    $content = file_get_contents($pathToFile);
    $content = str_replace($oldOld, $newNew, $content);
    file_put_contents($pathToFile, $content);
  }

  /**
   * Helper function to recursively copy files from one directory to another
   *
   * @param $src
   * @param $dst
   * @param $theme
   * @return bool
   * @see https://www.php.net/manual/en/function.copy.php#91010
   */
  public static function recursiveCopy($src, $dst, $theme): bool
  {
    // Check to make sure that source directory actually exists
    if(is_dir($src))
    {
      $dir = opendir($src);
      @mkdir($dst);

      while(false !== ( $file = readdir($dir)) )
      {
        if(( $file != '.' ) && ( $file != '..' ))
        {
          // recursively calls this function on directories
          if ( is_dir($src . '/' . $file) )
          {
            self::recursiveCopy($src . '/' . $file, $dst . '/' . $file, $theme);
          }
          else
          {
            $fileCopy = str_replace('starterkit', $theme, $file);
            copy($src . '/' . $file,$dst . '/' . $fileCopy);
            static::fileStringReplace($dst . '/' . $fileCopy, $theme, 'starterkit');
            static::fileStringReplace($dst . '/' . $fileCopy, ucfirst($theme), 'Starter Kit');
          }
        }
      }
      closedir($dir);
      return true;
    }
    else
    {
      echo "\033[31m$src is not a valid directory\n";
      return false;
    }


  }

  /**
   * @param Event $event
   * Copy themekit's and adminkit's starterkit from contrib folders to custom folders
   */
  public static function themeStartkitCopy(Event $event)
  {
    $themes = ['themekit', 'adminkit'];
    $project_root = getcwd();
    $drupal_root = static::getDrupalRoot($project_root);

    foreach($themes as $theme) {
      if (!is_dir($drupal_root . "/themes/custom/$theme")) {
        if (!is_dir($drupal_root . 'themes/custom')) {
          @mkdir($drupal_root . '/themes/custom');
        }

        $srcFolder = $drupal_root . "/themes/contrib/paragon_$theme/starterkit";
        $dstFolder = $drupal_root . "/themes/custom/$theme";

        echo "  - Copy \033[32m$srcFolder \033[97mto \033[32m$dstFolder\033[97m\n";

        if (static::recursiveCopy($srcFolder, $dstFolder, $theme)) {
          echo "Successfully copied $srcFolder -> $dstFolder\n";
        } else {
          echo "Failed to copy $srcFolder -> $dstFolder\n";
        }
      }
    }
  }
}
