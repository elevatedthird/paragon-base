<?php

namespace Drupal\layout_builder_limit;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\OverridesSectionStorageInterface;

class FormAlter {

  /**
   * Appends layout builder limit settings to configure section form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   */
  public function configureSectionFormAlter(&$form, FormStateInterface $form_state) {

    $overridden = FALSE;
    // Get delta from route.
    $delta = \Drupal::routeMatch()->getParameter('delta');
    $sectionStorage =  $form_state->getFormObject()->getSectionStorage();

    if ($sectionStorage instanceof OverridesSectionStorageInterface) {
      $overridden = TRUE;
    }

    // Do not add the settings form when the user does not have permission.
    if (!$overridden && !\Drupal::currentUser()->hasPermission('manage layout builder limit settings on default')) {
      return;
    }

    // Do not show settings in case the user has no permission to override them.
    if ($overridden && !\Drupal::currentUser()->hasPermission('manage layout builder limit settings on overrides')) {
      return;
    }

    // Try to get section from storage, if section doesn't exist, we can exit.
    try {
      $section = $sectionStorage->getSection($delta);
    }
    catch (\Exception $ignored) {
      return;
    }

    $configuration = $section->getThirdPartySetting('layout_builder_limit', 'limit', LayoutBuilderLimit::DEFAULT_CONFIGURATION);
    // Set current values from form.
    $values = $form_state->getValues();
    if (isset($values['layout_builder_limit']['scope'])) {
      $configuration['scope'] = $values['layout_builder_limit']['scope'];
      if (isset($values['layout_builder_limit']['settings'][$configuration['scope']])) {
        $configuration['settings'][$configuration['scope']] = $values['layout_builder_limit']['settings'][$configuration['scope']];
      }
    }
    // Get configuration array and stub default settings.
    $configuration = LayoutBuilderLimit::getDefaultConfiguration($section, $configuration);

    $scope = $configuration['scope'];
    $settings = $configuration['settings'] ?? [];

    $form['layout_builder_limit'] = [
      '#title' => t('Limit settings'),
      '#type' => 'details',
      '#weight' => 0,
    ];

    $wrapper_id = 'layout-builder-limit-scope-settings';

    $form['layout_builder_limit']['scope'] = [
      '#title' => t('Limit by'),
      '#type' => 'select',
      '#options' => [
        LayoutBuilderLimit::LIMIT_DISABLED => t('None'),
        LayoutBuilderLimit::LIMIT_REGION => t('Region'),
        LayoutBuilderLimit::LIMIT_SECTION => t('Section'),
      ],
      '#ajax' => [
        'callback' => [$this, 'updateScopeSettingsAjax'],
        'wrapper' => $wrapper_id,
        'trigger_as' => [
          'name' => $wrapper_id,
        ],
      ],
      '#default_value' => $scope,
    ];

    $form['layout_builder_limit']['scope_update'] = [
      '#type' => 'submit',
      '#value' => t('Update scope'),
      '#name' => $wrapper_id,
      '#submit' => [[$this, 'updateScopeSettings']],
      '#ajax' => [
        'callback' => [$this, 'updateScopeSettingsAjax'],
        'wrapper' => $wrapper_id,
      ],
      '#attributes' => [
        'class' => ['js-hide']
      ]
    ];

    $form['layout_builder_limit']['settings'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $wrapper_id,
      ],
    ];

    // Attach settings forms based on scope.
    switch ($scope) {
      case LayoutBuilderLimit::LIMIT_REGION:
        foreach  ($section->getLayout()->getPluginDefinition()->getRegions() as $region_id => $region) {
          $form['layout_builder_limit']['settings']['region'][$region_id] = [
            '#type' => 'fieldset',
            '#title' => t('Region "%label" limits', ['%label' => $region['label']]),
          ];
          self::attachSettingsForm($form['layout_builder_limit']['settings']['region'][$region_id], $settings['region'][$region_id]);
        }
        break;
      case LayoutBuilderLimit::LIMIT_SECTION:
        $form['layout_builder_limit']['settings']['section'] = [
          '#type' => 'fieldset',
          '#title' => t('Section limits'),
        ];
        self::attachSettingsForm($form['layout_builder_limit']['settings']['section'], $settings['section']);
        break;
    }

    $form['layout_builder_limit_delta'] = [
      '#type' => 'value',
      '#value' => $delta,
    ];

    array_unshift($form['#submit'], [$this, 'submitConfigureSectionForm']);
  }

  /**
   * Provides a '#submit' for saving Layout Build Limit settings to section.
   */
  public function submitConfigureSectionForm($form, FormStateInterface $form_state) {
    $sectionStorage =  $form_state->getFormObject()->getSectionStorage();
    $section = $sectionStorage->getSection($form_state->getValue('layout_builder_limit_delta'));
    $settings = LayoutBuilderLimit::getDefaultConfiguration($section, $form_state->getValue('layout_builder_limit'));
    $section->setThirdPartySetting('layout_builder_limit', 'limit', $settings);
  }

  /**
   * Provides a '#submit' callback for changing scope.
   */
  public static function updateScopeSettings(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Provides an '#ajax' callback for changing scope.
   */
  public static function updateScopeSettingsAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    // Go one level up in the form, to the widgets container.
    return $element['settings'];
  }


  /**
   * Provides a simple settings form for sections and regions.
   *
   * @param array $element
   *   The form element array.
   * @param array $settings
   *   The settings array.
   */
  public static function attachSettingsForm(&$element, $settings) {
    $element['minimum_enabled'] = [
      '#title' => t('Enable Minimum'),
      '#title_display' => 'after',
      '#type' => 'checkbox',
      '#default_value' => $settings['minimum_enabled'],
    ];

    $element['minimum'] = [
      '#title' => t('Minimum'),
      '#type' => 'number',
      '#description' => t('Require at least this many components.'),
      '#min' => 1,
      '#default_value' => $settings['minimum'],
      '#size' => 4,
    ];

    $element['maximum_enabled'] = [
      '#title' => t('Enable Maximum'),
      '#title_display' => 'after',
      '#type' => 'checkbox',
      '#default_value' => $settings['maximum_enabled'],
    ];

    $element['maximum'] = [
      '#title' => t('Maximum'),
      '#type' => 'number',
      '#min' => 1,
      '#description' => t('Restrict components to a maximum.'),
      '#default_value' => $settings['maximum'],
      '#size' => 4,
    ];
    $element['#process'][] = [get_called_class(), 'processSettingsForm'];
    $element['#element_validate'][] = [get_called_class(), 'validateSettingsForm'];
  }

  /**
   * Provides a '#process' callback for showing enabled settings only.
   */
  public static function processSettingsForm(&$element, FormStateInterface $form_state, array &$form) {
    $input_name = 'layout_builder_limit[' . implode('][', array_slice($element['#array_parents'], 1)) . ']';
    $element['minimum']['#states'] = [
      'visible' => [
        ":input[name='{$input_name}[minimum_enabled]']" => ['checked' => TRUE],
      ]
    ];
    $element['maximum']['#states'] = [
      'visible' => [
        ":input[name='{$input_name}[maximum_enabled]']" => ['checked' => TRUE],
      ]
    ];
    return $element;
  }

  /**
   * Provides validation for section and region settings.
   *
   * @param array $element
   *   The form element array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $form
   *   The complete form.
   */
  public static function validateSettingsForm(&$element, FormStateInterface $form_state, array &$form) {
    if ($values = NestedArray::getValue($form_state->getValues(), $element['#parents'])) {
      if ($values['minimum_enabled'] && empty($values['minimum'])) {
        $form_state->setError($element['minimum'], t('Minimum value required.'));
      }
      if ($values['maximum_enabled'] && empty($values['maximum'])) {
        $form_state->setError($element['maximum'], t('Maximum value required.'));
      }
      if($values['minimum_enabled'] && $values['minimum'] && $values['maximum_enabled'] && $values['maximum']) {
        if($values['maximum'] < $values['minimum']) {
          $form_state->setError($element['maximum'], t('The maximum limit must be greater than or equal the minimum limit.'));
        }
      }
    }
  }

}
