<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay\NoDisplay.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay;

use Drupal\Component\Plugin\PluginBase;
use Drupal\entity_browser\EntityBrowserSelectionDisplayInterface;

/**
 * Does not show current selection and immediately delivers selected entities.
 *
 * @EntityBrowserSelectionDisplay(
 *   id = "no_display",
 *   label = @Translation("No selection display"),
 *   description = @Translation("Skips current selection display and immediately delivers selected entities.")
 * )
 */
class NoDisplay extends PluginBase implements EntityBrowserSelectionDisplayInterface {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function label() {
    $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getForm() {
    return array();
  }

}
