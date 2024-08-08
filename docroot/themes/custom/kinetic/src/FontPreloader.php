<?php

namespace Drupal\kinetic;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FontPreloader.
 *
 * Looks for fonts defined in the theme's info.yml file and attaches them to the page based on best practice for
 * the font provider.  Local fonts are preferred from a performance perspective, but Google Fonts and Typekit are also
 * acceptable. Remote fonts are supported but discouraged outside of these providers.
 */
class FontPreloader implements ContainerInjectionInterface {

  /**
   * The theme extension list.
   */
  protected $themeExtensionList;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a new FontPreloader object.
   *
   * @param ThemeExtensionList $theme_extension_list
   *  The theme extension list.
   * @param FileSystemInterface $file_system
   * The file system.
   */
  public function __construct(ThemeExtensionList $theme_extension_list, FileSystemInterface $file_system) {
    $this->themeExtensionList = $theme_extension_list;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.list.theme'),
      $container->get('file_system'),
    );
  }

  /**
   * Attach fonts to page attachments.
   *
   * Loops through the fonts defined in the theme's info.yml file and attach them.
   *
   * @param $attachments
   *  The page attachments.
   */
  public function attach(&$attachments) {
    $callbacks = [
      'attachLocalFont',
      'attachGoogleFont',
      'attachTypekitFont',
      'attachRemoteFont',
    ];
    $active_theme = \Drupal::theme()->getActiveTheme();
    if (!empty($active_theme->getExtension()->info['preload-fonts']) && is_array($active_theme->getExtension()->info['preload-fonts'])) {
      foreach ($active_theme->getExtension()->info['preload-fonts'] as $value) {
        foreach ($callbacks as $callback) {
          if ($this->$callback($attachments, $value)) {
            break;
          }
        }
      }
    }
  }

  /**
   * Validate and attach local font.
   *
   * @param $attachments
   *   The page attachments.
   * @param $value
   *   The font value.
   * @return bool
   *   TRUE if the font was attached, FALSE otherwise.
   */
  public function attachLocalFont(&$attachments, $value): bool  {
    // todo: fix path like fonts/blah.tff
    if (!UrlHelper::isExternal($value)) {
      // Get active theme path.
      $active_theme_path = \Drupal::theme()->getActiveTheme()->getPath();
      // If not @ referenced theme, set font URI.
      if (!str_starts_with($value, '@')) {
        $uri = $active_theme_path . '/' . ltrim($value, '/');
      }
      // If @ referenced theme, set font URI.
      elseif (preg_match('/^\@([^\/]+)(\/.+)$/', $value, $match)) {
        $uri = $this->themeExtensionList->getPath($match[1]) . $match[2];
      }
      // If uri set and file exists, preload font.
      if (isset($uri) && file_exists($uri)) {
        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'preload',
          'as' => 'font',
          'href' => \Drupal::service('file_url_generator')->generateString($uri),
          'type' => 'font/' . pathinfo($uri, PATHINFO_EXTENSION),
          'crossorigin' => TRUE,
        ], FALSE];
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Validate and attach Google Font.
   *
   * @param $attachments
   *   The page attachments.
   * @param $value
   *   The font value.
   * @return bool
   *   TRUE if the font was attached, FALSE otherwise.
   */
  public function attachGoogleFont(&$attachments, $value): bool {
    if (str_contains($value, 'fonts.gstatic.com') || str_contains($value, 'fonts.googleapis.com')) {
      // Decide if we should force https.
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'preconnect',
        'href' => 'https://fonts.googleapis.com',
        ], FALSE];
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'preconnect',
        'href' => 'https://fonts.gstatic.com',
        'crossorigin' => TRUE,
        ], FALSE];

      if (str_contains($value, 'fonts.googleapis.com/css')) {
        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'preload',
          'as' => 'style',
          'type' => 'text/css',
          'href' => $value,
          'crossorigin' => TRUE,
        ], FALSE];

        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'href' => $value,
          'crossorigin' => TRUE,
        ], FALSE];
      }
      else {
        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'preload',
          'as' => 'font',
          'href' => $value,
          'type' => 'font/' . pathinfo($value, PATHINFO_EXTENSION),
          'crossorigin' => TRUE,
        ], FALSE];
      }


      return TRUE;
    }
    return FALSE;
  }

  /**
   * Validate and attach Typekit Font
   *
   * @param $attachments
   *   The page attachments.
   * @param $value
   *   The font value.
   * @return bool
   *   TRUE if the font was attached, FALSE otherwise.
   */
  public function attachTypekitFont(&$attachments, $value): bool  {
    if (str_contains($value, 'use.typekit.net') ) {
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'preconnect',
        'href' => "https://use.typekit.net",
        'crossorigin' => TRUE,
      ], FALSE];
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'preconnect',
        'href' => "https://p.typekit.net",
        'crossorigin' => TRUE,
      ], FALSE];
      if(str_contains($value, '.css')) {
        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'preload',
          'as' => 'style',
          'type' => 'text/css',
          'href' => $value,
          'crossorigin' => TRUE,
        ], FALSE];
        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'href' => $value,
          'crossorigin' => TRUE,
        ], FALSE];
      }
      else {
        $attachments['#attached']['html_head_link'][] = [[
          'rel' => 'preload',
          'as' => 'font',
          'href' => $value,
          'type' => 'font/' . pathinfo($value, PATHINFO_EXTENSION),
          'crossorigin' => TRUE,
        ], FALSE];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Validate and attach remote font.
   *
   * @param $attachments
   *   The page attachments.
   * @param $value
   *   The font value.
   * @return bool
   *   TRUE if the font was attached, FALSE otherwise.
   */
  public function attachRemoteFont(&$attachments, $value): bool  {
    if (UrlHelper::isExternal($value)) {
      // Add preconnect for font
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'preconnect',
        'href' => parse_url($value, PHP_URL_SCHEME) . '://' . parse_url($value, PHP_URL_HOST),
        'crossorigin' => TRUE,
        ], FALSE];
      // Add preload for font
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'preload',
        'as' => 'font',
        'href' => $value,
        'type' => 'font/' . pathinfo($value, PATHINFO_EXTENSION),
        'crossorigin' => TRUE,
      ], FALSE];
      return TRUE;
    }
    return FALSE;
  }
}
