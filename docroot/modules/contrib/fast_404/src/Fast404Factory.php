<?php

namespace Drupal\fast404;

use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Fast404: A value object for manager Fast 404 logic.
 *
 * @package Drupal\fast404
 */
class Fast404Factory implements Fast404FactoryInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Fast404 constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The current request stack.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(RequestStack $request_stack, ConfigFactory $config_factory) {
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance() {

    $fast404 = new Fast404($this->requestStack->getCurrentRequest());
    $lang_negotiation_config = $this->configFactory->get('language.negotiation');

    if ($lang_negotiation_config) {
      $lang_negotiation_url_info = $lang_negotiation_config->get('url');
      if (!empty($lang_negotiation_url_info['source']) && $lang_negotiation_url_info['source'] == 'path_prefix') {
        $fast404->setLanguageNegotiationUrlInfo($lang_negotiation_url_info);
      }
    }

    return $fast404;
  }

}
