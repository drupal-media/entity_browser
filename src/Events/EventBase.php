<?php

/**
 * @file
 * Definition of Drupal\entity_browser\Events\EventBase.
 */

namespace Drupal\entity_browser\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Base implementation of entity browser events.
 */
class EventBase extends Event {

  /**
   * Entity browser id
   *
   * @var string
   */
  protected $entityBrowserID;

  /**
   * Constructs a EntitySelectionEvent object.
   *
   * @param string $entity_browser_id
   *   Entity browser ID.
   */
  public function __construct($entity_browser_id) {
    $this->entityBrowserID = $entity_browser_id;
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

}
