<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Entity\EntityBrowser.
 */

namespace Drupal\entity_browser\Entity;

use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\entity_browser\EntityBrowserInterface;
use Drupal\entity_browser\Events\EntitySelectionEvent;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\SelectionDoneEvent;
use Drupal\entity_browser\WidgetInterface;
use Drupal\entity_browser\Plugin\EntityBrowser\Display\DisplayRouterInterface;
use Drupal\entity_browser\WidgetsLazyPluginCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
class EntityBrowser extends ConfigEntityBase implements EntityBrowserInterface, EntityWithPluginCollectionInterface {

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
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $displayPluginCollection;

  /**
   * The array of widgets for this entity browser.
   *
   * @var array
   */
  protected $widgets;

  /**
   * Holds the collection of widgetss that are used by this entity browser.
   *
   * @var \Drupal\entity_browser\WidgetsLazyPluginCollection
   */
  protected $widgetsPluginCollection;

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
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $selectionDisplayPluginCollection;

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
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $widgetSelectorPluginCollection;

  /**
   * Currently selected entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $selectedEntities = array();

  /**
   * Indicates wether browser already subscribed to events.
   *
   * @var bool
   */
  protected $subscribedToEvents = FALSE;

  /**
   * Indicates selection is done.
   *
   * @var bool
   */
  protected $selectionCompleted = FALSE;

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
    return $this->displayPluginCollection()->get($this->display);
  }

  /**
   * Returns display plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The tag plugin bag.
   */
  protected function displayPluginCollection() {
    if (!$this->displayPluginCollection) {
      $this->display_configuration['entity_browser_id'] = $this->id();
      $this->displayPluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.display'), $this->display, $this->display_configuration);
    }
    return $this->displayPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array(
      'widgets' => $this->getWidgets(),
      'widget_selector_configuration' => $this->widgetSelectorPluginCollection(),
      'display_configuration' => $this->displayPluginCollection(),
      'selection_display_configuration' => $this->selectionDisplayPluginCollection(),
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
    if (!$this->widgetsPluginCollection) {
      foreach ($this->widgets as &$widget) {
        $widget['settings']['entity_browser_id'] = $this->id();
      }
      $this->widgetsPluginCollection = new WidgetsLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.widget'), $this->widgets);
      $this->widgetsPluginCollection->sort();
    }
    return $this->widgetsPluginCollection;
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
  public function deleteWidget(WidgetInterface $widget) {
    $this->getWidgets()->removeInstanceId($widget->getUuid());
    $this->save();
    return $this;
  }

  /**
   * Returns selection display plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The tag plugin bag.
   */
  protected function selectionDisplayPluginCollection() {
    if (!$this->selectionDisplayPluginCollection) {
      $this->selection_display_configuration['entity_browser_id'] = $this->id();
      $this->selectionDisplayPluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.selection_display'), $this->selection_display, $this->selection_display_configuration);
    }
    return $this->selectionDisplayPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionDisplay() {
    return $this->selectionDisplayPluginCollection()->get($this->selection_display);
  }

  /**
   * Returns widget selector plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The tag plugin bag.
   */
  protected function widgetSelectorPluginCollection() {
    if (!$this->widgetSelectorPluginCollection) {
      $this->widgetSelectorPluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.widget_selector'), $this->widget_selector, $this->widget_selector_configuration);
    }
    return $this->widgetSelectorPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetSelector() {
    return $this->widgetSelectorPluginCollection()->get($this->widget_selector);
  }


  /**
   * {@inheritdoc}
   */
  public function getSelectedEntities() {
    return $this->selectedEntities;
  }

  /**
   * {@inheritdoc}
   */
  public function setSelectedEntities(array $entities) {
    $this->selectedEntities = $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectedEntities(array $entities) {
    $this->selectedEntities = array_merge($this->selectedEntities, $entities);
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(EntityStorageInterface $storage) {
    parent::postCreate($storage);
    $this->subscribeEvents(\Drupal::service('event_dispatcher'));
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    parent::postLoad($storage, $entities);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    /** @var \Drupal\entity_browser\Entity\EntityBrowser $browser */
    foreach ($entities as $browser) {
      $browser->subscribeEvents($event_dispatcher);
    }
  }

  /**
   * Subscribes entity browser to events if needed.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   */
  public function subscribeEvents(EventDispatcherInterface $event_dispatcher) {
    if (!$this->subscribedToEvents) {
      $event_dispatcher->addListener(Events::SELECTED, [$this, 'onSelected']);
      $event_dispatcher->addListener(Events::DONE, [$this, 'selectionCompleted']);
      $this->subscribedToEvents = TRUE;
    }
  }

  /**
   * Responds to SELECTED event.
   *
   * @param \Drupal\entity_browser\Events\EntitySelectionEvent $event
   */
  public function onSelected(EntitySelectionEvent $event) {
    if ($event->getBrowserID() == $this->id()) {
      $this->addSelectedEntities($event->getEntities());
    }
  }

  /**
   * Responds to DONE event.
   *
   * @param \Drupal\entity_browser\Events\SelectionDoneEvent $event
   */
  public function selectionCompleted(SelectionDoneEvent $event) {
    if ($event->getBrowserID() == $this->id()) {
      $this->selectionCompleted = TRUE;
    }
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
    $form['#browser_parts'] = array(
      'widget_selector' => 'widget_selector',
      'widget' => 'widget',
      'selection_display' => 'selection_display',
    );

    $form['selected_entities'] = array(
      '#type' => 'value',
      '#value' => array_map(function(EntityInterface $item) {return $item->id();}, $this->getSelectedEntities())
    );

    $form[$form['#browser_parts']['widget_selector']] = $this->getWidgetSelector()->getForm();
    $form[$form['#browser_parts']['widget']] = $this->getWidgetSelector()->getCurrentWidget($this->getWidgets())->getForm();
    $form[$form['#browser_parts']['selection_display']] = $this->getSelectionDisplay()->getForm();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->getWidgetSelector()->validate($form, $form_state);
    $this->getWidgetSelector()->getCurrentWidget($this->getWidgets())->validate($form, $form_state);
    $this->getSelectionDisplay()->validate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->getWidgetSelector()->submit($form, $form_state);
    $this->getWidgetSelector()->getCurrentWidget($this->getWidgets())->submit($form[$form['#browser_parts']['widget']], $form, $form_state);
    $this->getSelectionDisplay()->submit($form, $form_state);

    if (!$this->selectionCompleted) {
      $form_state->setRebuild();
    }
    else {
      $this->getDisplay()->selectionCompleted($this->getSelectedEntities());
    }
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

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Entity browser ID was added when creating. No need to save that as it can
    // always be calculated.
    foreach ($this->widgets as &$widget) {
      unset($widget['settings']['entity_browser_id']);
    }
    unset($this->selection_display_configuration['entity_browser_id']);
    unset($this->display_configuration['entity_browser_id']);
  }

}
