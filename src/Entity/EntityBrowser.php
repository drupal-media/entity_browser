<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Entity\EntityBrowser.
 */

namespace Drupal\entity_browser\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_browser\EntityBrowserDisplayInterface;
use Drupal\entity_browser\EntityBrowserInterface;

/**
 * Defines an entity browser configuration entity.
 *
 * @ConfigEntityType(
 *   id = "entity_browser",
 *   label = @Translation("Entity browser"),
 *   admin_permission = "administer entity browsers",
 *   config_prefix = "browser",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label"
 *   },
 * )
 */
class EntityBrowser extends ConfigEntityBase implements EntityBrowserInterface, EntityInterface {

  /**
   * The name of the entity browser.
   *
   * @var string
   */
  public $name;

  /**
   * The entity browser label.
   *
   * @var string
   */
  public $label;

  /**
   * The display plugin.
   *
   * @var \Drupal\entity_browser\EntityBrowserDisplayInterface
   */
  public $display;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name');
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplay() {
    return $this->get('display');
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplay(EntityBrowserDisplayInterface $display) {
    $this->set('display', $display);
    return $this;
  }

}
