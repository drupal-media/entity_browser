<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector\Single.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector;

use Drupal\entity_browser\WidgetsCollection;
use Drupal\entity_browser\WidgetSelectorBase;

/**
 * Displays only first widget.
 *
 * @EntityBrowserWidgetSelector(
 *   id = "single",
 *   label = @Translation("Single widget"),
 *   description = @Translation("Displays first configured widget.")
 * )
 */
class Single extends WidgetSelectorBase {

  /**
   * {@inheritdoc}
   */
  public function getForm() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentWidget(WidgetsCollection $widgets) {
    return $this->getFirstWidget($widgets);
  }

}
