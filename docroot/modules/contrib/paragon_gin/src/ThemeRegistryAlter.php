<?php

namespace Drupal\paragon_gin;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ExtensionList;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ThemeRegistryAlter implements ContainerInjectionInterface {

  /**
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected ModerationInformationInterface $moderationInformation;

  /**
   * @var \Drupal\Core\Extension\ExtensionList
   */
  private $extensionList;

  /**
   * EntityTypeInfo constructor.
   *
   * @param \Drupal\Core\Extension\ExtensionList $extension_list
   *   Module extension list service.
   */
  public function __construct(ExtensionList $extension_list) {
    $this->extensionList = $extension_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.list.module'),
    );
  }

  public function themeRegistryAlter(&$theme_registry) {
    $templates_path = $this->extensionList->getPath('paragon_gin') . '/templates';
    $theme_registry['toolbar']['path'] = $templates_path;
    $theme_registry['menu__toolbar']['path'] = $templates_path;
    $theme_registry['menu_local_tasks']['path'] = $templates_path;
    $theme_registry['menu_local_task']['path'] = $templates_path;
  }

}
