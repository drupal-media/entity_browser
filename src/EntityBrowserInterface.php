<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormInterface;

/**
 * Provides an interface defining an entity browser entity.
 */
interface EntityBrowserInterface extends ConfigEntityInterface, FormInterface {

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
   * @return \Drupal\entity_browser\DisplayInterface
   *   The display.
   */
  public function getDisplay();

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
   * @return \Drupal\entity_browser\WidgetsBag
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
   * @return \Drupal\entity_browser\SelectionDisplayInterface
   *   The display.
   */
  public function getSelectionDisplay();

  /**
   * Returns the widget selector.
   *
   * @return \Drupal\entity_browser\EntityBrowserWidgetSelectorInterface
   *   The widget selector.
   */
  public function getWidgetSelector();

  /**
   * Returns currently selected entities.
   *
   * @return array
   *   Array of currently selected entities.
   */
  public function getSelectedEntities();

  /**
   * Sets currently selected entities.
   *
   * @param array $entities
   *   Entities that are currently selected.
   */
  public function setSelectedEntities(array $entities);

  /**
   * Adds entities to currently selected entities.
   *
   * @param array $entities
   *   Entities to be added to the list of currently selected entities.
   */
  public function addSelectedEntities(array $entities);

  /**
   * Indicates completed selection.
   *
   * Selection process is done and currently selected entities are sent to the
   * initiating code.
   */
  public function selectionCompleted();

  /**
   * Gets route that matches this display.
   *
   * @return \Symfony\Component\Routing\Route|bool
   *   Route object or FALSE if no route is used.
   */
  public function route();

}
