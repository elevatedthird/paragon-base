<?php

namespace Drupal\paragraphs_browser\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for paragraph browser type forms.
 */
class BrowserTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $paragraphs_browser_type = $this->entity;

    if ($paragraphs_browser_type->isNew()) {
      $form['#title'] = (t('Add new paragraphs browser type'));
    }
    else {
      $form['#title'] = (t('Edit %label paragraphs browser type', [
        '%label' => $paragraphs_browser_type->label(),
      ]));
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $paragraphs_browser_type->label(),
      '#description' => $this->t("Label for the paragraphs browser type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $paragraphs_browser_type->id(),
      '#machine_name' => [
        'exists' => 'paragraphs_browser_type_load',
      ],
      '#disabled' => !$paragraphs_browser_type->isNew(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $paragraphs_browser_type = $this->entity;
    $status = $paragraphs_browser_type->save();

    if ($status) {
      $this->messenger()->addStatus($this->t('Saved the %label paragraphs browser type.', [
        '%label' => $paragraphs_browser_type->label(),
      ]));
    }
    else {
      $this->messenger()->addStatus($this->t('The %label paragraphs browser was not saved.', [
        '%label' => $paragraphs_browser_type->label(),
      ]));
    }

    $form_state->setRedirect('entity.paragraphs_browser_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $form = parent::actions($form, $form_state);

    return $form;
  }

}
