<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an entity browser entity.
 */
interface EntityBrowserInterface extends ConfigEntityInterface {

  /**
   * Returns the entity browser name.
   *
   * @return string
   *   The name of the entity browser.
   */
  public function getName();

  /**
   * Sets the name of the entity browser.
   *
   * @param string $name
   *   The name of the entity browser.
   *
   * @return \Drupal\entity_browser\EntityBrowserInterface
   *   The class instance this method is called on.
   */
  public function setName($name);

  /**
   * Returns the display.
   *
   * @return \Drupal\entity_browser\EntityBrowserDisplayInterface
   *   The display.
   */
  public function getDisplay();

  /**
   * Sets the display.
   *
   * @param array $display
   *   The display configuration.
   *
   * @return \Drupal\entity_browser\EntityBrowserInterface
   *   The class instance this method is called on.
   */
  public function setDisplay(array $display);

  /**
   * Returns a specific widget.
   *
   * @param string $widget
   *   The widget ID.
   *
   * @return \Drupal\entity_browser\EntityBrowserWidgetInterface
   *   The widget object.
   */
  public function getWidget($widget);

  /**
   * Returns the widgets for this entity browser.
   *
   * @return \Drupal\Core\Plugin\DefaultPluginBag
   *   The tag plugin bag.
   */
  public function getWidgets();

  /**
   * Saves a widget for this entity browser.
   *
   * @param array $configuration
   *   An array of widget configuration.
   *
   * @return string
   *   The widget ID.
   */
  public function addWidget(array $configuration);

  /**
   * Deletes a widget from this entity browser.
   *
   * @param \Drupal\entity_browser\EntityBrowserWidgetInterface $widget
   *   The widget object.
   *
   * @return $this
   */
  public function deleteWidget(EntityBrowserWidgetInterface $widget);

  /**
   * Returns the selection display.
   *
   * @return \Drupal\entity_browser\EntityBrowserSelectionDisplayInterface
   *   The display.
   */
  public function getSelectionDisplay();

  /**
   * Sets the selection display.
   *
   * @param array $selection_display
   *   The selection display configuration.
   *
   * @return \Drupal\entity_browser\EntityBrowserInterface
   *   The class instance this method is called on.
   */
  public function setSelectionDisplay(array $selection_display);


  /**
   * Returns the widget selector.
   *
   * @return \Drupal\entity_browser\EntityBrowserWidgetSelectorInterface
   *   The widget selector.
   */
  public function getWidgetSelector();

  /**
   * Sets the widget selector.
   *
   * @param array $widget_selector
   *   The widget selector configuration.
   *
   * @return \Drupal\entity_browser\EntityBrowserInterface
   *   The class instance this method is called on.
   */
  public function setWidgetSelector(array $widget_selector);

}
