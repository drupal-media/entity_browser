<?php

/**
 * @file
 * Contains \Drupal\entity_browser\SelectionDisplayInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for entity browser selection displays.
 */
interface SelectionDisplayInterface extends PluginInspectionInterface {

  /**
   * Returns the selection display label.
   *
   * @return string
   *   The selection display label.
   */
  public function label();

  /**
   * Returns selection display form.
   *
   * @return array
   *   Form structure.
   */
  public function getForm();

}
