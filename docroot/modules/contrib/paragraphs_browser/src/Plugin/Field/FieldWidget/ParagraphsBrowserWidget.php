<?php

/**
 * @file
 * Paragraphs Previewer widget implementation for paragraphs.
 */

namespace Drupal\paragraphs_browser\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Plugin implementation of the 'entity_reference paragraphs' widget.
 *
 * We hide add / remove buttons when translating to avoid accidental loss of
 * data because these actions effect all languages.
 *
 * @FieldWidget(
 *   id = "paragraphs_browser",
 *   label = @Translation("Paragraphs Browser EXPERIMENTAL"),
 *   description = @Translation("An experimental paragraphs inline form widget with a previewer."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParagraphsBrowserWidget extends ParagraphsWidget {

  use ParagraphsBrowserWidgetTrait;

  /**
   * Generated UUID.
   *
   * @var string
   */
  protected string $uuid;

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $elements = parent::form($items, $form, $form_state, $get_delta);

    if ($elements) {
      // Add the widget class of the parent widget so that our widget is
      // styled the same.
      $elements['#attributes']['class'][] = 'field--widget-paragraphs';
    }

    return $elements;
  }

  /**
   * Returns select options for a plugin setting.
   *
   * This is done to allow
   * \Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget::settingsSummary()
   * to access option labels. Not all plugin setting are available.
   *
   * @param string $setting_name
   *   The name of the widget setting. Supported settings:
   *   - "edit_mode"
   *   - "closed_mode"
   *   - "autocollapse"
   *   - "add_mode"
   *
   * @return array|null
   *   An array of setting option usable as a value for a "#options" key.
   *
   * @see \Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget::settingsSummary()
   */
  protected function getSettingOptions($setting_name) {
    $options = parent::getSettingOptions($setting_name);
    switch($setting_name) {
      case 'add_mode':
        $options['paragraphs_browser'] = $this->t('Paragraphs Browser');
        break;
    }

    return $options;
  }

}
