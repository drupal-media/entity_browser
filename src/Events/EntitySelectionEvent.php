<?php

/**
 * @file
 * Definition of Drupal\entity_browser\Events\EntitySelectionEvent.
 */

namespace Drupal\entity_browser\Events;

/**
 * Represents entity selection as event.
 */
class EntitySelectionEvent extends EventBase {

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
    parent::__construct($entity_browser_id);
    $this->entities = $entities;
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
