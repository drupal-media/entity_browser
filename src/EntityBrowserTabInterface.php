<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserTabInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for entity browser tabs.
 */
interface EntityBrowserTabInterface extends PluginInspectionInterface {

  /**
   * Returns the tab label.
   *
   * @return string
   *   The tab label.
   */
  public function label();

  /**
   * Returns the unique ID representing the tab.
   *
   * @return string
   *   The tab ID.
   */
  public function getUuid();

}
