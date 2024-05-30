<?php

namespace Drupal\paragraphs_browser\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class EntityBrowserController.
 *
 * @package Drupal\paragraphs_browser\Controller
 */
class ParagraphsBrowserController extends ControllerBase {
  private $modal_height;
  private $modal_width;

  /**
   * Route callback that returns the Paragraphs Browser form within a modal.
   *
   * @return string
   *   Returns the Ajax response to open dialog.
   */
  public function paragraphsBrowserSelect($field_config, $paragraphs_browser_type, $uuid) {
    $this->setModalHeightAndWidth($field_config);

    $form = \Drupal::formBuilder()->getForm('Drupal\paragraphs_browser\Form\ParagraphsBrowserForm', $field_config, $paragraphs_browser_type, $uuid);

    $form['#attached']['library'][] = 'paragraphs_browser/modal';
    $title = $this->t('Browse');
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($title, $form, ['modal' => TRUE, 'width' => $this->modal_width, 'height' => $this->modal_height]));
    return $response;
  }

  /**
   * @param $field_config
   */
  private function setModalHeightAndWidth($field_config) {
    $field_name  = $field_config->getName();
    $entity_type = $field_config->getTargetEntityTypeId();
    $bundle      = $field_config->getTargetBundle();

    $settings = \Drupal::service('entity_display.repository')->getFormDisplay($entity_type, $bundle, 'default');

    $modal_height = !empty($settings->getComponent($field_name)) ? $settings->getComponent($field_name)['settings']['modal_height'] : '';
    $modal_width = !empty($settings->getComponent($field_name)) ? $settings->getComponent($field_name)['settings']['modal_width'] : '';

    if ($modal_height !== "auto"){
      $modal_height = (int)$modal_height;
    }

    // Use default height and width if either of these values is empty
    $this->modal_height = !empty($modal_height) ? $modal_height : 'auto';
    $this->modal_width  = !empty($modal_width) ? $modal_width : '80%';
  }
}
