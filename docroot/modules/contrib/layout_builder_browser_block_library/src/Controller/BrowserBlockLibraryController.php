<?php

namespace Drupal\layout_builder_browser_block_library\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BrowserBlockLibraryController.
 *
 * A layout builder dialog to return blocks from the custom block library by
 * bundle.
 */
class BrowserBlockLibraryController implements ContainerInjectionInterface {

  use AjaxHelperTrait;
  use LayoutBuilderContextTrait;
  use LayoutBuilderHighlightTrait;
  use StringTranslationTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * ChooseBlockController constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(BlockManagerInterface $block_manager, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->blockManager = $block_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Returns a page title.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   * @param string $bundle
   *   The block bundle.
   *
   * @return string
   *   A the page title.
   */
  public function getTitle(SectionStorageInterface $section_storage, $delta, $region, $bundle) {
    $label = $this->entityTypeManager->getStorage('block_content_type')
      ->load($bundle)
      ->label();
    return $this->t("Select '@bundle' block from library", ['@bundle' => $label]);
  }

  /**
   * Provides the UI for choosing a block from the library.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   * @param string $bundle
   *   The block bundle.
   *
   * @return array
   *   A render array.
   */
  public function browse(SectionStorageInterface $section_storage, $delta, $region, $bundle) {
    $build = [];
    $build['#prefix'] = "<div class='lbb-block-library'>";
    $build['#suffix'] = '</div>';

    $definitions = $this->blockManager->getFilteredDefinitions('layout_builder', $this->getPopulatedContexts($section_storage), [
      'section_storage' => $section_storage,
      'delta' => $delta,
      'region' => $region,
    ]);
    $blocks = $this->entityTypeManager->getStorage('block_content')
      ->loadByProperties(['type' => $bundle, 'reusable' => TRUE]);

    $ids = array_map(function ($block) {
      return 'block_content:' . $block->uuid();
    }, $blocks);
    $ids = array_flip($ids);

    $block_content_definitions = array_intersect_key($definitions, $ids);
    if ($block_content_definitions) {
      $build['filter'] = [
        '#type' => 'search',
        '#title' => $this->t('Filter by block name'),
        '#title_display' => 'invisible',
        '#size' => 30,
        '#placeholder' => $this->t('Filter by block name'),
        '#attributes' => [
          'class' => ['js-layout-builder-filter'],
          'title' => $this->t('Enter a part of the block name to filter by.'),
        ],
      ];
    }
    $build['block_categories']['_custom_block_library'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['js-layout-builder-categories', 'block-categories', 'browser-block-library-blocks', 'clearfix'],
        'data-layout-builder-target-highlight-id' => $this->blockAddHighlightId($delta, $region),
      ],
      '#title' => $this->t('Custom Block Library'),
      'links' => [],
    ];
    if ($block_content_definitions) {
      $attributes = $this->getAjaxAttributes();
      $attributes['class'][] = 'js-layout-builder-block-link';
      $attributes['class'][] = 'layout-builder-browser-block-item';
      foreach ($block_content_definitions as $block_id => $definition) {
        $build['block_categories']['_custom_block_library']['links'][] = [
          '#type' => 'link',
          '#title' => [
            'label' => [
              '#markup' => $definition['admin_label'],
            ],
          ],
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
      }
    }
    else {
      $label = $this->entityTypeManager->getStorage('block_content_type')
        ->load($bundle)
        ->label();

      $build['block_categories']['_custom_block_library']['message'] = [
        '#type' => 'container',
        'empty' => [
          '#theme' => 'status_messages',
          '#message_list' => [
            'warning' => [
              $this->t("No blocks of type '%bundle' in your library. ", ['%bundle' => $label]),
            ],
          ],
          '#status_headings' => [
            'status' => t('Status message'),
            'error' => t('Error message'),
            'warning' => t('Warning message'),
          ],
          '#weight' => -10,
        ],
      ];
    }
    $build['back_button'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('layout_builder.choose_block',
        [
          'section_storage_type' => $section_storage->getStorageType(),
          'section_storage' => $section_storage->getStorageId(),
          'delta' => $delta,
          'region' => $region,
        ]
      ),
      '#title' => $this->t('Back'),
      '#attributes' => $this->getAjaxAttributes(),
    ];

    $build['#attached']['library'][] = 'layout_builder_browser_block_library/browser_block_library';
    return $build;
  }

  /**
   * Gets a render array of block links.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   * @param array $blocks
   *   The information for each block.
   *
   * @return array
   *   The block links render array.
   */
  protected function getBlocks(SectionStorageInterface $section_storage, $delta, $region, array $blocks) {
    $links = [];

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
        '#type' => 'link',
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

      $links[] = $link;
      if ($block['id'] == 'inline_block') {
        $link = [
          '#type' => 'link',
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
          '#attributes' => $this->getAjaxAttributes(),
        ];
      }
    }
    return $links;
  }

  /**
   * Get dialog attributes if an ajax request.
   *
   * @return array
   *   The attributes array.
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
