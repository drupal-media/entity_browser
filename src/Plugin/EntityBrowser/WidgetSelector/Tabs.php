<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector\Tabs.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector;

use Drupal\entity_browser\WidgetSelectorBase;
use Drupal\Core\Form\FormStateInterface;

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
  public function getForm(array &$form, FormStateInterface &$form_state) {
    // TODO - Implement.
    return array();
  }


}
