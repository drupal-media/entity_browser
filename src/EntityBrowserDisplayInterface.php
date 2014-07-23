<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserDisplayInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for entity browser displays.
 */
interface EntityBrowserDisplayInterface extends PluginInspectionInterface {

  /**
   * Returns the display label.
   *
   * @return string
   *   The display label.
   */
  public function label();

}
