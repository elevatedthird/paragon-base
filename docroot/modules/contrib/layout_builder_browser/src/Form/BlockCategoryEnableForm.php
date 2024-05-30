<?php

namespace Drupal\layout_builder_browser\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the category enable form.
 */
class BlockCategoryEnableForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $form = parent::create($container);

    $form->setMessenger(
      $container->get('messenger')
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to enable the %category category?', ['%category' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.layout_builder_browser_blockcat.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Enable');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Only enabled categories are displayed in the browser. This action can be undone from the categories administration page.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->enable()->save();
    $this->messenger()
      ->addStatus($this->t('The %category category has been enabled.', ['%category' => $this->entity->label()]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
