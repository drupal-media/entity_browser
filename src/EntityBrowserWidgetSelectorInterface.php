<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserWidgetSelectorInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for entity browser widget selectors.
 */
interface EntityBrowserWidgetSelectorInterface extends PluginInspectionInterface {

  /**
   * Returns the widget selector label.
   *
   * @return string
   *   The widget label.
   */
  public function label();

  /**
   * Returns the unique ID representing the widget selector.
   *
   * @return string
   *   The widget ID.
   */
  public function getUuid();

}
