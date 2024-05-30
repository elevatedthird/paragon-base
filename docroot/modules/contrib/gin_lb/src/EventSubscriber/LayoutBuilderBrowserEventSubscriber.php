<?php

declare(strict_types=1);

namespace Drupal\gin_lb\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class LayoutBuilderBrowserEventSubscriber.
 *
 * Add layout builder css class layout-builder-browser.
 */
class LayoutBuilderBrowserEventSubscriber implements EventSubscriberInterface {

  public const KERNEL_WEIGHT = 50;

  /**
   * Add layout-builder-browser class layout_builder.choose_block build block.
   */
  public function onView(ViewEvent $event): void {
    $request = $event->getRequest();
    $route = $request->attributes->get('_route');

    if ($route == 'layout_builder.choose_block') {
      $build = $event->getControllerResult();
      if (\is_array($build) && !isset($build['add_block'])) {
        $build['block_categories']['#attributes']['class'][] = 'layout-builder-browser';
        $event->setControllerResult($build);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::VIEW][] = ['onView', static::KERNEL_WEIGHT];
    return $events;
  }

}
