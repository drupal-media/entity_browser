<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector\Single.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector;

use Drupal\entity_browser\WidgetsBag;
use Drupal\entity_browser\WidgetSelectorBase;

/**
 * Displays only first widget.
 *
 * @EntityBrowserWidgetSelector(
 *   id = "drop_down",
 *   label = @Translation("Drop down widget"),
 *   description = @Translation("Displays the widgets in a drop down.")
 * )
 */
class DropDown extends WidgetSelectorBase {

  /**
   * {@inheritdoc}
   */
  public function getForm(WidgetsBag $widgets) {

    foreach ($widgets->getInstanceIds() as $id) {
      $options[$id] = $widgets->get($id)->label();
    }

    $element['widgets'] = array(
    	'#type' => 'select',
      '#options' => $options,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentWidget(WidgetsBag $widgets) {
    return $this->getFirstWidget($widgets->sort());
  }

}