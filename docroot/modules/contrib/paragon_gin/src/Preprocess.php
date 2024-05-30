<?php

namespace Drupal\paragon_gin;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Preprocess implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  private $localTaskManager;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * @var \Drupal\Core\Routing\AdminContext
   */
  private $adminContext;

  /**
   * EntityTypeInfo constructor.
   *
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $local_task_manager
   *   Local task plugin manager.
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $local_task_manager
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   */
  public function __construct(LocalTaskManagerInterface $local_task_manager, RouteMatchInterface $route_match, AdminContext $admin_context) {
    $this->localTaskManager = $local_task_manager;
    $this->routeMatch = $route_match;
    $this->adminContext = $admin_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.local_task'),
      $container->get('current_route_match'),
      $container->get('router.admin_context')
    );
  }

  public function menuLocalTasks(&$variables) {
    if (isset($variables['primary']['content_moderation.workflows:node.latest_version_tab'])) {
      if (!$variables['primary']['content_moderation.workflows:node.latest_version_tab']['#access'] instanceof AccessResultAllowed) {
        return;
      }
      if (isset($variables['primary']['entity.node.canonical'])) {
        $variables['primary']['entity.node.canonical']['#link']['title'] = t('View Current');
      }
      $variables['primary']['test'] = [
        '#type' => 'html_tag',
        '#tag' => 'li',
        '#value' => 'Lastest Draft',
        '#attributes' => [
          'class' => [
            'tab-moderation-group',
            'tabs__tab',
            'js-tab',
            'tabs__content-moderation-draft-label',
          ],
        ],
        '#weight' => $variables['primary']['content_moderation.workflows:node.latest_version_tab']['#weight'] - 1,
      ];

      $nested_keys = [
        'layout_builder_ui:layout_builder.overrides.node.view',
        'entity.node.edit_form',
        'content_moderation.workflows:node.latest_version_tab',
      ];

      $last_found = FALSE;
      foreach ($nested_keys as $key) {
        if (isset($variables['primary'][$key])) {
          $variables['primary'][$key]['#attributes'] = $variables['primary'][$key]['#attributes'] ?? [];
          $variables['primary'][$key]['#attributes']['class'] = $variables['primary'][$key]['#attributes']['class'] ?? [];
          $variables['primary'][$key]['#attributes']['class'][] = 'tab-moderation-group';
          if (!$last_found) {
            $variables['primary'][$key]['#attributes']['class'][] = 'tab-moderation-group-last';
            $last_found = TRUE;
          }
        }
      }
    }
  }

  public function menuLocalTask(&$variables) {
    $variables['link']['#options']['attributes']['class'][] = 'tabs__link';
    $variables['link']['#options']['attributes']['class'][] = 'js-tabs-link';
  }


  public function toolbar(&$variables) {
    $variables['is_admin_route'] = (bool) $this->adminContext->isAdminRoute();

    // Hide 'edit' toggle.
    unset($variables['tabs']['contextual']);
    $local_tasks = $this->buildLocalTasks([]);
    $variables['local_tasks'] = $local_tasks;
  }

  protected function buildLocalTasks() {
    $local_tasks = \Drupal::service('plugin.manager.menu.local_task')
      ->getLocalTasks(\Drupal::service('current_route_match')
        ->getRouteName(), 0);

    $this->localTaskManager = \Drupal::service('plugin.manager.menu.local_task');


    $cacheability = new CacheableMetadata();
    $cacheability->addCacheableDependency($this->localTaskManager);
    $tabs = [
      '#theme' => 'menu_local_tasks',
    ];

    $links = $this->localTaskManager->getLocalTasks(\Drupal::service('current_route_match')
      ->getRouteName(), 0);
    $cacheability = $cacheability->merge($links['cacheability']);
    // Do not display single tabs.
    $tabs += [
      '#primary' => count(Element::getVisibleChildren($links['tabs'])) > 1 ? $links['tabs'] : [],
    ];

    $build = [];
    $cacheability->applyTo($build);
    if (empty($tabs['#primary']) && empty($tabs['#secondary'])) {
      return $build;
    }
    return $build + $tabs;
  }
}
