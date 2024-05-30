<?php

namespace Drupal\fast404;

/**
 * Fast404: A value object for manager Fast 404 logic.
 *
 * @package Drupal\fast404
 */
interface Fast404FactoryInterface {

  /**
   * Creates a pre-configured instance of Fast404.
   *
   * @return \Drupal\fast404\Fast404
   *   A fully configured Fast404 instance.
   */
  public function createInstance();

}
