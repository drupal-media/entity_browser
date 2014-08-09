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

  /**
   * Display entity browser.
   *
   * This is the "entry point" for every non-entity browser code to interact
   * with it. It will take care about displaying entity browser in one way or
   * another.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function displayEntityBrowser();

  /**
   * Indicates completed selection.
   *
   * Entity browser will call this function when selection is done. Display
   * plugin is responsible for fetching selected entities and sending them to
   * the initiating code.
   */
  public function selectionCompleted();

}
