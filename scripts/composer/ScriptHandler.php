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
    // Get webroot from composer.json.
    $composer_json = json_decode(file_get_contents($project_root . '/composer.json'), TRUE);
    if (isset($composer_json['extra']['drupal-scaffold']['locations']['web-root'])) {
      return $project_root . '/' . $composer_json['extra']['drupal-scaffold']['locations']['web-root'];
    }
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
    exec("find " . getcwd() . "'/vendor' | grep '.git$' | xargs rm -rf");
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

  protected static function copyPlatformFiles($source, $destination) {
    $fs = new Filesystem();
    $relative_source = "assets/platform-setup{$source}";
    $is_file = !is_dir($relative_source);
    $exists = $fs->exists($destination);
    if ($is_file && !$exists) {
      $fs->copy($relative_source, $destination);
    }
    elseif (!$is_file && !$exists) {
      $fs->mirror($relative_source, $destination);
    }
    else {
      echo "{$destination} already exists... skipping\n";
    }
  }


  protected static function copyFromE3Repo(Event $event, $source_url, $destination, $detach = TRUE) {
    $io = $event->getIO();
    if (strpos($source_url, 'git@github.com:elevatedthird') === 0) {
      $fs = new Filesystem();
      $destination = '/modules/custom';
      // This always runs from same level as composer.json.
      $project_root = getcwd();
      $drupal_root = static::getDrupalRoot($project_root);
      $destination_root = $drupal_root . $destination;

      if (!is_dir($destination_root)) {
        if (!is_dir($destination_root)) {
          $fs->mkdir($destination_root);
          $io->write('Created ' . $destination_root);
        }
      }
      $first_dir =  getcwd();
      // change dir to the new path
      $new_dir = chdir($destination_root);
      // Git clone
      $git_clone = shell_exec('git clone '. $source_url);
      $parts = explode('/', $source_url);
      $end_path = end($parts);
      $repo_directory_name = pathinfo($end_path, PATHINFO_FILENAME);
      chdir($destination_root . '/' . $repo_directory_name);
      $io->write('Cloned ' . $source_url . ' to ' . $destination_root . '/' . $repo_directory_name);
      if ($detach) {
        $io->write('Removing .git directory');
        $fs->remove($destination_root . '/' . $repo_directory_name . '/.git');
      }
      // change dir back
      $change_dir_back = chdir($first_dir);
      return true;
    }
    else {
      return false;
    }
  }

  public static function createGHActions(Event $event) {
    $io = $event->getIO();
    // Copy over main.yaml for GH Actions.
    $io->write('Creating GitHub Actions workflow file.');
    self::copyPlatformFiles('/main.yml', './.github/workflows/main.yml');
  }

  public static function setupPlatformRequirements(Event $event) {
    $io = $event->getIO();
    $question = 'Select the hosting platform for this project';
    $choices = ['Acquia', 'Pantheon', 'Platform.sh', 'custom'];
    $default = $choices[0];
    $answer = $io->select($question, $choices, $default);
    $platform = $choices[$answer];
    $project_root = getcwd();
    $drupal_root = static::getDrupalRoot($project_root);
    switch ($platform) {
      case 'Acquia':
        $io->write('Setting up Acquia specific requirements');
        self::copyPlatformFiles('/acquia/hooks', './hooks');
        self::copyPlatformFiles('/acquia/settings.acquia.php', $drupal_root . '/sites/default/settings.acquia.php');
        break;
      case 'Pantheon':
        $io->write('Setting up Pantheon specific requirements');
        self::copyPlatformFiles('/pantheon/pantheon.upstream.yml', './pantheon.upstream.yml');
        self::copyPlatformFiles('/pantheon/pantheon.yml', './pantheon.yml');
        self::copyPlatformFiles('/pantheon/settings.pantheon.php', $drupal_root . '/sites/default/settings.pantheon.php');
        break;
      case 'Platform.sh':
        $io->write('Setting up Platform.sh specific requirements');
        self::copyPlatformFiles('/platform-sh/.platform', './.platform');
        self::copyPlatformFiles('/platform-sh/.environment', './.environment');
        self::copyPlatformFiles('/platform-sh/.platform.app.yaml', './.platform.app.yaml');
        self::copyPlatformFiles('/platform-sh/platformsh_generate_drush_yml.php', './drush/platformsh_generate_drush_yml.php');
        self::copyPlatformFiles('/platform-sh/settings.platformsh.php', $drupal_root . '/sites/default/settings.platformsh.php');
        break;
      case 'custom':
        $io->write('Paragon will not create any platform specific files. Run composer setup-platform to see these options again.');
        break;
    }
  }

  public static function setupListOptionsModule(Event $event) {
    $io = $event->getIO();
    $question = 'Do you want to copy the E3 - List Options module(requires git repo access)([y]/n)? ';
    // Ask a yes/no question:
    $answer = $io->ask($question, 'y');
    if ($answer === 'y') {
      self::copyFromE3Repo($event, 'git@github.com:elevatedthird/e3_list_options.git', '/modules/custom');
    }
  }
  /**
   * @param Event $event
   * Download and extract theme to the custom themes folder.
   */
  public static function downloadAndExtractTheme(Event $event)
  {
    $fs = new Filesystem();
    $theme = 'kinetic';
    // This always runs from same level as composer.json.
    $project_root = getcwd();
    $drupal_root = static::getDrupalRoot($project_root);
    $theme_root = $drupal_root . '/themes/custom/' . $theme;

    if (!is_dir($drupal_root . "/themes/custom/$theme")) {
      if (!is_dir($drupal_root . 'themes/custom')) {
        @mkdir($drupal_root . '/themes/custom');
      }
      try {
        $download_link = NULL;
        $file_name = NULL;
        // Get a list of releases from drupal.org.
        $project_status = file_get_contents("https://updates.drupal.org/release-history/{$theme}/current");
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $project_status, $vals, $index);
        xml_parser_free($parser);
        // Get the latest version of the theme.
        if (isset($index['VERSION'])) {
          $version = $vals[$index['VERSION'][0]]['value'];
          $file_name = $theme . '-' . $version . '.zip';
          $download_link = "https://ftp.drupal.org/files/projects/{$file_name}";
        }
        if (!$download_link || !$file_name) {
          echo "Could not find the latest version for $theme\n";
          return;
        }
        // Download the release.
        file_put_contents($file_name, fopen($download_link, 'r'));
        // Using the zip archive instead of tar because it doesn't extract files how we want.
        $zip = new \ZipArchive();
        $zip->open($file_name);
        $zip->extractTo($drupal_root . '/themes/custom/');
        $zip->close();
        // For some reason, the tar and zip archives don't preserve symlinks.
        // Re symlink the components directory.
        $fs->remove([ $theme_root . '/components' ]);
        $fs->symlink('source/02-components', $theme_root . '/components');
        // Delete the .gitignore file.
        unlink($theme_root . '/.gitignore');
        // Delete the zip.
        unlink($file_name);
        echo "Downloaded {$theme} to themes/custom/{$theme}. Please run npm install\n";
      }
      catch (Exception $e) {
        echo "Failed to download $theme\n";
        return;
      }
    }
    else {
      echo "Theme $theme already exists in themes/custom\n";
    }
  }
}
