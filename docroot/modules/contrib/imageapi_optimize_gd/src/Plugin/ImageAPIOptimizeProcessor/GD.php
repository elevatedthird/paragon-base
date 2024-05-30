<?php

namespace Drupal\imageapi_optimize_gd\Plugin\ImageAPIOptimizeProcessor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorBase;

/**
 * Provides a ImageAPI Optimize processor for GD.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "imageapi_optimize_gd",
 *   label = @Translation("GD"),
 *   description = @Translation("Adjust quality of JPEG and WebP Images with GD.")
 * )
 */
class GD extends ConfigurableImageAPIOptimizeProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function applyToImage($image_uri) {
    $success = FALSE;
    // Confirm GD library exists.
    if (function_exists('imagegd2')) {
      if (in_array($this->getMimeType($image_uri), $this->configuration['file_types'])) {

        $image = $this->getImageFactory()->get($image_uri, 'gd');
        if (!$image->isValid()) {
          return FALSE;
        }
        // Get the correct function based on file type.
        $function = 'image' . image_type_to_extension($image->getToolkit()->getType(), FALSE);
        if (function_exists($function)) {
          // Convert stream wrapper URI to normal path.
          $destination = \Drupal::service('file_system')->realpath($image_uri);
          $success = $function($image->getToolkit()
            ->getResource(), $destination, $this->configuration['quality']);
        }
      }
    }
    else {
      $this->logger->notice('The PHP GD library must be installed for the ImageAPI Optimize GD module to process images.');
    }
    return $success;
  }

  /**
   * Returns the image factory.
   *
   * @return \Drupal\Core\Image\ImageFactory
   *   The image factory.
   */
  protected function getImageFactory() {
    return \Drupal::service('image.factory');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'quality' => 75,
      'file_types' => ['image/jpeg'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['quality'] = [
      '#type' => 'number',
      '#title' => $this->t('Image quality'),
      '#description' => $this->t('Specify the image quality.'),
      '#default_value' => $this->configuration['quality'],
      '#required' => TRUE,
      '#min' => 1,
      '#max' => 100,
    ];
    $form['file_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('File Types'),
      '#options' => ['image/jpeg' => 'JPEG', 'image/webp' => 'WebP'],
      '#default_value' => $this->configuration['file_types'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['quality'] = $form_state->getValue('quality');
    $this->configuration['file_types'] = array_filter($form_state->getValue('file_types'));
  }

}
