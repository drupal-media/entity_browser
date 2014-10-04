<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector\Tabs.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector;

use Drupal\entity_browser\WidgetsBag;
use Drupal\entity_browser\WidgetSelectorBase;

/**
 * Displays entity browser widgets as tabs.
 *
 * @EntityBrowserWidgetSelector(
 *   id = "tabs",
 *   label = @Translation("Tabs"),
 *   description = @Translation("Displays entity browser widgets as tabs.")
 * )
 */
class Tabs extends WidgetSelectorBase {

  /**
   * {@inheritdoc}
   */
  public function getForm(WidgetsBag $widgets) {
    // TODO - Implement.
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentWidget(WidgetsBag $widgets) {
    return '';
  }

}
