<?php

/**
 * @file
 * Definition of Drupal\entity_browser\Events\SelectionDoneEvent.
 */

namespace Drupal\entity_browser\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Represents finished selection as event.
 */
class SelectionDoneEvent extends Event {

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
