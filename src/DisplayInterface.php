<?php

/**
 * @file
 * Contains \Drupal\entity_browser\DisplayInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for entity browser displays.
 */
interface DisplayInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the display label.
   *
   * @return string
   *   The display label.
   */
  public function label();

  /**
   * Displays entity browser.
   *
   * This is the "entry point" for every non-entity browser code to interact
   * with it. It will take care about displaying entity browser in one way or
   * another.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function displayEntityBrowser();

  /**
   * Indicates completed selection.
   *
   * Entity browser will call this function when selection is done. Display
   * plugin is responsible for fetching selected entities and sending them to
   * the initiating code.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *
   */
  public function selectionCompleted(array $entities);

  /**
   * Gets the uuid for this display.
   *
   * @return string
   *   The uuid string.
   */
  public function getUuid();

  /**
   * Set validators.
   *
   * Saves Entity Browser Widget validators in key/value storage if an identical
   * set of constraints is not already stored there.
   *
   * @param array $validators
   *   An array where keys are validator ids and values configurations for them.
   *
   * @return string
   *   The hash generated from hashing the validators array.
   */
  public function setValidators(array $validators);

  /**
   * Get validators.
   *
   * @param \Drupal\entity_browser\string $hash
   *   The hash generated from hashing the validators array.
   *
   * @return mixed
   *   An array where keys are validator ids and values configurations for them
   *   or empty array if no validators are stored.
   */
  public function getValidators(string $hash);
}
