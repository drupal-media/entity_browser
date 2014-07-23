<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector\Standalone.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Tabs;

use Drupal\Component\Plugin\PluginBase;
use Drupal\entity_browser\EntityBrowserWidgetSelectorInterface;

/**
 * Displays entity browser widgets as tabs.
 *
 * @EntityBrowserWidgetSelector(
 *   id = "tabs",
 *   label = @Translation("Tabs"),
 *   description = @Translation("Displays entity browser widgets as tabs.")
 * )
 */
class Tabs extends PluginBase implements EntityBrowserWidgetSelectorInterface {

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
    return $this->label;
  }
}
