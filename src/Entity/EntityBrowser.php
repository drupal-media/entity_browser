<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Entity\EntityBrowser.
 */

namespace Drupal\entity_browser\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Plugin\DefaultPluginBag;
use Drupal\Core\Plugin\DefaultSinglePluginBag;
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
   * The display plugin configuration.
   *
   * @var array
   */
  public $display;

  /**
   * Display plugin bag.
   *
   * @var \Drupal\Core\Plugin\DefaultSinglePluginBag
   */
  protected $displayBag;

  /**
   * The array of widgets for this entity browser.
   *
   * @var array
   */
  protected $widgets;

  /**
   * Holds the collection of widgetss that are used by this entity browser.
   *
   * @var \Drupal\Core\Plugin\DefaultPluginBag
   */
  protected $widgetsBag;

  /**
   * The selection display plugin configuration.
   *
   * @var array
   */
  public $selection_display;

  /**
   * Selection display plugin bag.
   *
   * @var \Drupal\Core\Plugin\DefaultSinglePluginBag
   */
  protected $selectionDisplayBag;

  /**
   * The widget selector plugin configuration.
   *
   * @var array
   */
  public $widget_selector;

  /**
   * Widget selector plugin bag.
   *
   * @var \Drupal\Core\Plugin\DefaultSinglePluginBag
   */
  protected $widgetSelectorBag;

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
    return $this->displayPluginBag()->get($this->display['id']);
  }

  /**
   * Returns display plugin bag.
   *
   * @return \Drupal\Core\Plugin\DefaultSinglePluginBag
   *   The tag plugin bag.
   */
  protected function displayPluginBag() {
    if (!$this->displayBag) {
      $this->displayBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.entity_browser.display'), $this->display['id'], $this->display);
    }
    return $this->displayBag;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplay(array $display) {
    $this->display = $display;
    $this->displayPluginBag()->addInstanceId($display['id']);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginBags() {
    return array(
      'widgets' => $this->getWidgets(),
      'widget_selector' => $this->widgetSelectorPluginBag(),
      'display' => $this->displayPluginBag(),
      'selection_display' => $this->selectionDisplayPluginBag(),
    );
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

  /**
   * Returns selection display plugin bag.
   *
   * @return \Drupal\Core\Plugin\DefaultSinglePluginBag
   *   The tag plugin bag.
   */
  protected function selectionDisplayPluginBag() {
    if (!$this->selectionDisplayBag) {
      $this->selectionDisplayBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.entity_browser.selection_display'), $this->selection_display['id'], $this->selection_display);
    }
    return $this->selectionDisplayBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionDisplay() {
    return $this->selectionDisplayPluginBag()->get($this->selection_display['id']);
  }

  /**
   * {@inheritdoc}
   */
  public function setSelectionDisplay(array $selection_display) {
    $this->selection_display = $selection_display;
    $this->selectionDisplayPluginBag()->addInstanceId($selection_display['id']);
    return $this;
  }

  /**
   * Returns widget selector plugin bag.
   *
   * @return \Drupal\Core\Plugin\DefaultSinglePluginBag
   *   The tag plugin bag.
   */
  protected function widgetSelectorPluginBag() {
    if (!$this->widgetSelectorBag) {
      $this->widgetSelectorBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.entity_browser.widget_selector'), $this->widget_selector['id'], $this->widget_selector);
    }
    return $this->widgetSelectorBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetSelector() {
    return $this->widgetSelectorPluginBag()->get($this->widget_selector['id']);
  }

  /**
   * {@inheritdoc}
   */
  public function setWidgetSelector(array $widget_selector) {
    $this->widget_selector = $widget_selector;
    $this->widgetSelectorPluginBag()->addInstanceId($widget_selector['id']);
    return $this;
  }

}
