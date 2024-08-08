<?php

namespace Drupal\kinetic;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Kinetic Base Layout class. Add all section configuration here!
 *
 * @internal
 *   Plugin classes are internal.
 */
class KineticLayout extends LayoutDefault implements ContainerFactoryPluginInterface {
  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'bg_color' => 'none',
      'content_width' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label']['#description'] = $this->t('This label will be used as a title for this layout in the Layout Builder UI. Additionally, it will add an id to the layout wrapper element to use for jumplinks.');
    $configuration = $this->getConfiguration();

    // set kinetic:section to hook up settings.
    $form['bg_color'] = [
      '#type' => 'select',
      '#title' => $this->t('Background Color'),
      '#options' => [
        'none' => 'None',
      ],
      '#default_value' => $configuration['bg_color'],
    ];

    $form['content_width'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Width'),
      '#description' => $this->t('Select the size of the content width (bg color included)'),
      '#options' => [
        'container' => 'Default',
        'container-narrow' => 'Narrow',
        'container-fluid' => 'Full',
      ],
      '#default_value' => $configuration['content_width'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // If the form item is disabled, set config value to NULL.
    foreach(Element::children($form) as $item) {
      if ($form_state->hasValue($item)) {
        $is_disabled = isset($form[$item]['#access']) && !$form[$item]['#access'];
        $this->configuration[$item] = $is_disabled ? NULL : $form_state->getValue($item);
      }
    }
  }

}
