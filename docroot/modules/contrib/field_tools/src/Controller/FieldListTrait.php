<?php

namespace Drupal\field_tools\Controller;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides helpers for controllers showing lists of fields.
 */
trait FieldListTrait {

  /**
   * Gets a URL for the current page with a sort order query parameter.
   *
   * Assumes:
   *  - $this->requestStack is an injected service
   *  - $this->currentRoute is the current route name.
   *
   * @param $sort
   *  The name of a field on field_storage_config entities that can be used for
   *  sorting.
   *
   * @return \Drupal\Core\Url
   *  A URL object.
   */
  protected function getSortQueryURL($sort) {
    // Preserve current query parameters, except for the sort.
    $query_params = $this->requestStack->getCurrentRequest()->query->all();

    $query_params['sort'] = $sort;

    return Url::fromRoute($this->currentRoute, [], [
      'query' => $query_params,
    ]);
  }

  /**
   * Builds a filter for host entity types and bundles.
   *
   * @return array
   *   The form element.
   */
  protected function buildHostEntityBundleFilter() {
    $bundle_info = $this->entityBundleInfo->getAllBundleInfo();

    $bundle_options = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Only look at content entities.
      if ($entity_type->getGroup() != 'content') {
        continue;
      }

      // WTF some broken entity types?
      if (!isset($bundle_info[$entity_type_id])) {
        continue;
      }

      if (count($bundle_info[$entity_type_id]) > 1) {
        $bundle_options["$entity_type_id::all"] = $entity_type->getLabel() . ' - *' . $this->t('all') . '*';
      }

      foreach ($bundle_info[$entity_type_id] as $bundle_name => $entity_bundle_info) {
        $bundle_options["$entity_type_id:$bundle_name"] = $entity_type->getLabel() . ' - ' . $entity_bundle_info['label'];
      }
    }

    $element = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Host entity and bundle'),
      '#options' => $bundle_options,
      '#default_value' => $this->requestStack->getCurrentRequest()->query->all('host_entity_bundle') ?? [],
    ];

    return $element;
  }

  /**
   * Gets the actions for the filter form.
   *
   * Assumes:
   *  - $this->currentRoute is the current route name.
   */
  protected function getFormActions() {
    $form_actions = [
      '#tree' => FALSE,
      '#type' => 'actions',
    ];
    $form_actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply filter'),
      '#button_type' => 'primary',
      // Prevent op from showing up in the query string.
      '#name' => '',
    ];
    $form_actions['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset'),
      '#url' => Url::fromRoute($this->currentRoute),
    ];

    return $form_actions;
  }

  /**
   * #after_build callback.
   *
   * Cleans up the GET query parameters.
   */
  public static function afterBuild(array $form, FormStateInterface $form_state) {
    unset($form['form_token']);
    unset($form['form_build_id']);
    unset($form['form_id']);

    return $form;
  }

}
