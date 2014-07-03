<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Annotation\EntityBrowserTab.
 */

namespace Drupal\entity_browser\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an entity browser tab annotation object.
 *
 * @see hook_entity_browser_tab_info_alter()
 *
 * @Annotation
 */
class EntityBrowserTab extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the tab.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A brief description of the tab.
   *
   * This will be shown when adding or configuring this tab.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

  /**
   * Holds the collection of image effects that are used by this image style.
   *
   * @var \Drupal\image\ImageEffectBag
   */
  protected $effectsBag;


}
