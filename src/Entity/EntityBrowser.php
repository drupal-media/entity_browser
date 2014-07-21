<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Entity\EntityBrowser.
 */

namespace Drupal\entity_browser\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Plugin\DefaultPluginBag;
use Drupal\entity_browser\EntityBrowserDisplayInterface;
use Drupal\entity_browser\EntityBrowserInterface;
use Drupal\entity_browser\EntityBrowserWidgetInterface;

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
class EntityBrowser extends ConfigEntityBase implements EntityBrowserInterface, EntityWithPluginBagsInterface {

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
   * The array of widgets for this entity browser.
   *
   * @var array
   */
  protected $widgets = array();

  /**
   * Holds the collection of widgetss that are used by this entity browser.
   *
   * @var \Drupal\Core\Plugin\DefaultPluginBag
   */
  protected $widgetsBag;

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

  /**
   * {@inheritdoc}
   */
  public function getPluginBags() {
    return array('widgets' => $this->getWidgets());
  }

  /**
   * {@inheritdoc}
   */
  public function getWidget($widget) {
    return $this->getWidgets()->get($widget);
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgets() {
    if (!$this->widgetsBag) {
      $this->widgetsBag = new DefaultPluginBag(\Drupal::service('plugin.manager.entity_browser.widget'), $this->widgets);
      $this->widgetsBag->sort();
    }
    return $this->widgetsBag;
  }

  /**
   * {@inheritdoc}
   */
  public function addWidget(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getWidgets()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteWidget(EntityBrowserWidgetInterface $widget) {
    $this->getWidgets()->removeInstanceId($widget->getUuid());
    $this->save();
    return $this;
  }

}
