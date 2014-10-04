<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Entity\EntityBrowser.
 */

namespace Drupal\entity_browser\Entity;

use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\DefaultSinglePluginBag;
use Drupal\entity_browser\EntityBrowserInterface;
use Drupal\entity_browser\EntityBrowserWidgetInterface;
use Drupal\entity_browser\Plugin\EntityBrowser\Display\DisplayRouterInterface;
use Drupal\entity_browser\WidgetsBag;
use Symfony\Component\Routing\Route;

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
   * The display plugin id.
   *
   * @var string
   */
  public $display;

  /**
   * The display plugin configuration.
   *
   * @var array
   */
  public $display_configuration = array();

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
   * @var \Drupal\entity_browser\WidgetsBag
   */
  protected $widgetsBag;

  /**
   * The selection display plugin ID.
   *
   * @var string
   */
  public $selection_display;

  /**
   * The selection display plugin configuration.
   *
   * @var array
   */
  public $selection_display_configuration = array();

  /**
   * Selection display plugin bag.
   *
   * @var \Drupal\Core\Plugin\DefaultSinglePluginBag
   */
  protected $selectionDisplayBag;

  /**
   * The widget selector plugin ID.
   *
   * @var string
   */
  public $widget_selector;

  /**
   * The widget selector plugin configuration.
   *
   * @var array
   */
  public $widget_selector_configuration = array();

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
    return $this->displayPluginBag()->get($this->display);
  }

  /**
   * Returns display plugin bag.
   *
   * @return \Drupal\Core\Plugin\DefaultSinglePluginBag
   *   The tag plugin bag.
   */
  protected function displayPluginBag() {
    if (!$this->displayBag) {
      $this->displayBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.entity_browser.display'), $this->display, $this->display_configuration);
    }
    return $this->displayBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginBags() {
    return array(
      'widgets' => $this->getWidgets(),
      'widget_selector_configuration' => $this->widgetSelectorPluginBag(),
      'display_configuration' => $this->displayPluginBag(),
      'selection_display_configuration' => $this->selectionDisplayPluginBag(),
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
      $this->widgetsBag = new WidgetsBag(\Drupal::service('plugin.manager.entity_browser.widget'), $this->widgets);
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
      $this->selectionDisplayBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.entity_browser.selection_display'), $this->selection_display, $this->selection_display_configuration);
    }
    return $this->selectionDisplayBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionDisplay() {
    return $this->selectionDisplayPluginBag()->get($this->selection_display);
  }

  /**
   * Returns widget selector plugin bag.
   *
   * @return \Drupal\Core\Plugin\DefaultSinglePluginBag
   *   The tag plugin bag.
   */
  protected function widgetSelectorPluginBag() {
    if (!$this->widgetSelectorBag) {
      $this->widgetSelectorBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.entity_browser.widget_selector'), $this->widget_selector, $this->widget_selector_configuration);
    }
    return $this->widgetSelectorBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetSelector() {
    return $this->widgetSelectorPluginBag()->get($this->widget_selector);
  }


  /**
   * {@inheritdoc}
   */
  public function getSelectedEntities() {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function setSelectedEntities(array $entities) {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectedEntities(array $entities) {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function selectionCompleted() {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_' . $this->id() . '_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['widget_selector'] = $this->getWidgetSelector()->getForm($this->getWidgets());
    $form['widget'] = $this->getWidgetSelector()->getCurrentWidget($this->getWidgets())->getForm();
    $form['selection_display'] = $this->getSelectionDisplay()->getForm();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function route() {
    $defaults = array(
      '_content' => 'Drupal\entity_browser\Controllers\StandalonePage::page',
      '_title_callback' => 'Drupal\entity_browser\Controllers\StandalonePage::title',
      'entity_browser_id' => $this->id(),
    );

    $requirements = array(
      '_permission' => 'access ' . String::checkPlain($this->id()) . ' entity browser pages',
    );

    $display = $this->getDisplay();
    if ($display instanceof DisplayRouterInterface) {
      $path = $display->path();
      return new Route($path, $defaults, $requirements);
    }

    return FALSE;
  }

}
