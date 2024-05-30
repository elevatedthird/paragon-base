<?php

namespace Drupal\layout_builder_browser_block_library\Controller;

use Drupal\Core\Url;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder_browser\Controller\BrowserController;

/**
 * Class OverriddenBrowserController.
 *
 * Extends the BrowserController class to provide an additional link to
 * custom block libraries.
 */
class OverriddenBrowserController extends BrowserController {

  /**
   * {@inheritdoc}
   */
  public function browse(SectionStorageInterface $section_storage, $delta, $region) {
    $build = parent::browse($section_storage, $delta, $region);
    $build['#attached']['library'][] = 'layout_builder_browser_block_library/browser_block_library';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBlocks(SectionStorageInterface $section_storage, $delta, $region, array $blocks) {
    $links = [];
    $block_ids_by_bundle = [];
    $block_entities = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['reusable' => TRUE]);
    foreach ($block_entities as $block_entity) {
      $block_ids_by_bundle[$block_entity->bundle()][] = 'block_content:' . $block_entity->uuid();
    }
    foreach ($blocks as $block_id => $block) {
      $attributes = $this->getAjaxAttributes();
      $attributes['class'][] = 'js-layout-builder-block-link';
      $attributes['class'][] = 'layout-builder-browser-block-item';

      $block_render_array = [];
      if (isset($block["layout_builder_browser_data"]->image_path) && trim($block["layout_builder_browser_data"]->image_path) != '') {
        $block_render_array['image'] = [
          '#theme' => 'image',
          '#uri' => $block["layout_builder_browser_data"]->image_path,
          '#alt' => $block['layout_builder_browser_data']->image_alt,
        ];
      }
      $block_render_array['label'] = ['#markup' => $block["layout_builder_browser_data"]->label()];
      $link = [
        '#theme' => 'lb_browser_block',
        '#title' => $block_render_array,
        '#url' => Url::fromRoute('layout_builder.add_block',
          [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'region' => $region,
            'plugin_id' => $block_id,
          ]
        ),
        '#attributes' => $attributes,
      ];

      $pieces = explode(':', $block_id);
      $bundle = $pieces[1] ?? NULL;
      if ($block['id'] == 'inline_block') {
        $attributes = $this->getAjaxAttributes();
        $attributes['class'][] = 'browse-library-icon';
        $attributes['class'][] = 'align-right';
        if (!isset($block_ids_by_bundle[$bundle])) {
          $attributes['class'][] = 'browse-library-empty';
        }
        $browse_link = [
          '#type' => 'link',
          '#title' => $this->t('Browse library'),
          '#url' => Url::fromRoute('layout_builder_browser_block_library.choose_block_library',
            [
              'section_storage_type' => $section_storage->getStorageType(),
              'section_storage' => $section_storage->getStorageId(),
              'delta' => $delta,
              'region' => $region,
              'plugin_id' => $block_id,
              'bundle' => $bundle,
            ]
          ),
          '#attributes' => $attributes,
        ];
        $link['#browse_link'] = $browse_link;
      }

      $links[] = $link;
    }
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAjaxAttributes() {
    if ($this->isAjax()) {
      return [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'dialog',
        'data-dialog-renderer' => 'off_canvas',
      ];
    }
    return [];
  }

}
