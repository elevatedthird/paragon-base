<?php

namespace Drupal\kinetic;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configurable one column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class KineticLayoutTwoColumn extends KineticLayout {

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $build['#attributes']['class'] = [
      'layout',
      $this->getPluginDefinition()->getTemplate(),
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $configuration = $this->getConfiguration();

    // Add unique settings here...

    // Or, remove settings you don't need from the base Kinetic layout.
    $form['content_width']['#access'] = FALSE;
    return $form;
  }

}
