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

}
