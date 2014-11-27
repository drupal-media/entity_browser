<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Ajax\SelectEntitiesCommand.
 */

namespace Drupal\entity_browser\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * AJAX command to rerender a formatted text field without any transformation
 * filters.
 */
class SelectEntitiesCommand implements CommandInterface {

  /**
   * A unique identifier.
   *
   * @var string
   */
  protected $uuid;
  
  /**
   * A CSS selector string.
   *
   * @var array
   */
  protected $entities;
  

  /**
   * Constructs a \Drupal\entity_browser\Ajax\SelectEntities object.
   *
   * @param string $selector
   *   A CSS selector.
   */
  public function __construct($uuid, $entities) {
    $this->uuid = $uuid;
    $this->entities = $entities;
  }

  /**
   * Implements \Drupal\Core\Ajax\CommandInterface::render().
   */
  public function render() {
    return array(
      'command' => 'select_entities',
      'uuid' => $this->uuid,
      'entities' => $this->entities,
    );
  }
}
