<?php

/**
 * @file
 * Definition of Drupal\entity_browser\EntitySelectionEvent.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Represents entity selection as event.
 */
class EntitySelectionEvent extends Event {

  /**
   * Entity browser id
   *
   * @var string
   */
  protected $entityBrowserID;

  /**
   * Entities being selected.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities;

  /**
   * Constructs a EntitySelectionEvent object.
   *
   * @param string $entity_browser_id
   *   Entity browser ID.
   */
  public function __construct($entity_browser_id, array $entities) {
    $this->entityBrowserID = $entity_browser_id;
    $this->entities = $entities;
  }

  /**
   * Gets the entity browser ID:
   *
   * @return string
   *   Entity browser ID.
   */
  public function getBrowserID() {
    return $this->entityBrowserID;
  }

  /**
   * Gets selected entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getEntities() {
    return $this->entities;
  }

}
