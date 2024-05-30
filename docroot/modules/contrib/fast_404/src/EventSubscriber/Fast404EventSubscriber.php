<?php

namespace Drupal\fast404\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Site\Settings;
use Drupal\fast404\Fast404FactoryInterface;
use Drupal\fast404\Fast404;

/**
 * Class Fast404EventSubscriber.
 *
 * @package Drupal\fast404\EventSubscriber
 */
class Fast404EventSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $requestStack;

  /**
  * The config factory.
  *
  * @var \Drupal\fast404\Fast404FactoryInterface
  */
  protected $fast404Factory;

  /**
   * Constructs a new Fast404EventSubscriber instance.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The Request Stack.
   * @param \Drupal\fast404\Fast404FactoryInterface $fast404Factory
   */
  public function __construct(RequestStack $request_stack, Fast404FactoryInterface $fast404Factory) {
    $this->requestStack = $request_stack;
    $this->fast404Factory = $fast404Factory;
  }

  /**
   * Ensures Fast 404 output returned if applicable.
   */
  public function onKernelRequest(RequestEvent $event) {
    $fast_404 = $this->fast404Factory->createInstance();
    // @var \Drupal\fast404\Fast404

    $fast_404->extensionCheck();
    if ($fast_404->isPathBlocked()) {
      $event->setResponse($fast_404->response(TRUE));
    }

    $fast_404->pathCheck();
    if ($fast_404->isPathBlocked()) {
      $event->setResponse($fast_404->response(TRUE));
    }
  }

  /**
   * Ensures Fast 404 output returned upon NotFoundHttpException.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The response for exception event.
   */
  public function onNotFoundException(ExceptionEvent $event) {
    // Check to see if we will completely replace the Drupal 404 page.
    if (Settings::get('fast404_not_found_exception', FALSE)) {
      if ($event->getThrowable() instanceof NotFoundHttpException) {
        $fast_404 = new Fast404($event->getRequest());
        $event->setResponse($fast_404->response(TRUE));
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 100];
    $events[KernelEvents::EXCEPTION][] = ['onNotFoundException', 0];
    return $events;
  }

}
