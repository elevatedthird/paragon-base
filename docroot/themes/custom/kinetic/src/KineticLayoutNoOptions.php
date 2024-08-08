<?php

namespace Drupal\kinetic;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;

/**
 * Hero layout.
 *
 * @internal
 *   Plugin classes are internal.
 */
class KineticLayoutNoOptions extends LayoutDefault {

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
    // Or, remove settings you don't need from the base Kinetic layout.
//    $parentForm['content_width']['#access'] = FALSE;
    $form['label']['#description'] = $this->t('This label will be used as a title for this layout in the Layout Builder UI. Additionally, it will add an id to the layout wrapper element to use for jumplinks.');
    return $form;

  }


}
