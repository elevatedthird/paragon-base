<?php

namespace Drupal\layout_builder_browser\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the LayoutBuilderBrowserBlockCategory entity.
 *
 * @ConfigEntityType(
 *   id = "layout_builder_browser_block",
 *   label = @Translation("Layout builder browser block"),
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\layout_builder_browser\Form\BlockListingForm",
 *     "form" = {
 *       "add" = "Drupal\layout_builder_browser\Form\BlockForm",
 *       "edit" = "Drupal\layout_builder_browser\Form\BlockForm",
 *       "delete" =
 *   "Drupal\layout_builder_browser\Form\BlockDeleteConfirmForm",
 *       "enable" = "Drupal\layout_builder_browser\Form\BlockEnableForm",
 *       "disable" = "Drupal\layout_builder_browser\Form\BlockDisableForm",
 *     }
 *   },
 *   config_prefix = "layout_builder_browser_block",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "block_id" = "block_id",
 *     "label" = "label",
 *     "status" = "status",
 *     "weight" = "weight",
 *   },
 *   config_export = {
 *     "id",
 *     "block_id",
 *     "category",
 *     "label",
 *     "weight",
 *     "status",
 *     "image_path",
 *     "image_alt",
 *   },
 *   links = {
 *     "edit-form" =
 *   "/admin/config/content/layout-builder-browser/blocks/{layout_builder_browser_block}",
 *     "delete-form" =
 *   "/admin/config/content/layout-builder-browser/blocks/{layout_builder_browser_block}/delete",
 *     "enable" = "/admin/config/content/layout-builder-browser/blocks/{layout_builder_browser_block}/enable",
 *     "disable" = "/admin/config/content/layout-builder-browser/blocks/{layout_builder_browser_block}/disable",
 *   }
 * )
 */
class LayoutBuilderBrowserBlock extends ConfigEntityBase {

  /**
   * ID.
   *
   * @var string
   */
  public $id;

  /**
   * Block Id.
   *
   * @var string
   */
  public $block_id;

  /**
   * Category.
   *
   * @var string
   */
  public $category;

  /**
   * Label.
   *
   * @var string
   */
  protected $label;

  /**
   * Image path.
   *
   * @var string
   */
  public $image_path;

  /**
   * Image alt.
   *
   * @var string
   */
  public $image_alt;

}
