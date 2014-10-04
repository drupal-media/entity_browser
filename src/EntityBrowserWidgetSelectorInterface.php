<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserWidgetSelectorInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginBag;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for entity browser widget selectors.
 */
interface EntityBrowserWidgetSelectorInterface extends PluginInspectionInterface {

  /**
   * Returns the widget selector label.
   *
   * @return string
   *   The widget label.
   */
  public function label();

  /**
   * Returns widget selector form.
   *
   * @return array
   *   Form structure.
   */
  public function getForm(WidgetsBag $widgets);

  /**
   * Returns ID of the widget that is currently selected.
   *
   * @param \Drupal\entity_browser\WidgetsBag $widgets
   *   Widgets plugin bag.
   *
   * @return \Drupal\entity_browser\EntityBrowserWidgetInterface
   *   Currently selected widget.
   */
  public function getCurrentWidget(WidgetsBag $widgets);

}
