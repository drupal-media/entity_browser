<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Entity\EntityBrowser.
 */

namespace Drupal\entity_browser\Entity;

use Drupal\Component\Utility\SafeMarkup;
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
use Drupal\entity_browser\DisplayRouterInterface;
use Drupal\entity_browser\WidgetsCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Route;
use Drupal\entity_browser\DisplayAjaxInterface;

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
  public $display_configuration = [];

  /**
   * Display lazy plugin collection.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $displayCollection;

  /**
   * The array of widgets for this entity browser.
   *
   * @var array
   */
  protected $widgets;

  /**
   * Holds the collection of widgets that are used by this entity browser.
   *
   * @var \Drupal\entity_browser\WidgetsCollection
   */
  protected $widgetsCollection;

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
  public $selection_display_configuration = [];

  /**
   * Selection display plugin collection.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $selectionDisplayCollection;

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
  public $widget_selector_configuration = [];

  /**
   * Widget selector plugin collection.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $widgetSelectorCollection;

  /**
   * Currently selected entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $selectedEntities = [];

  /**
   * Indicates selection is done.
   *
   * @var bool
   */
  protected $selectionCompleted = FALSE;

  /**
   * Additional widget parameters.
   *
   * @var array
   */
  protected $additional_widget_parameters = [];

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
   *   The tag plugin collection.
   */
  protected function displayPluginCollection() {
    if (!$this->displayCollection) {
      $this->display_configuration['entity_browser_id'] = $this->id();
      $this->displayCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.display'), $this->display, $this->display_configuration);
    }
    return $this->displayCollection;
  }

  /**
   * Returns the plugin collections used by this entity.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection[]
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
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
    if (!$this->widgetsCollection) {
      foreach ($this->widgets as &$widget) {
        $widget['settings']['entity_browser_id'] = $this->id();
      }
      $this->widgetsCollection = new WidgetsCollection(\Drupal::service('plugin.manager.entity_browser.widget'), $this->widgets);
      $this->widgetsCollection->sort();
    }
    return $this->widgetsCollection;
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
   * {@inheritdoc}
   */
  public function getCurrentWidget(FormStateInterface $form_state) {
    // Do not use has() as that returns TRUE if the value is NULL.
    if (!$form_state->get('entity_browser_current_widget')) {
      $form_state->set('entity_browser_current_widget', $this->getFirstWidget());
    }

    return $form_state->get('entity_browser_current_widget');
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentWidget($widget, FormStateInterface $form_state) {
    $form_state->set('entity_browser_current_widget', $widget);
  }

  /**
   * Gets first widget based on weights.
   *
   * @return string
   *   First widget instance ID.
   */
  protected function getFirstWidget() {
    $instance_ids = $this->getWidgets()->getInstanceIds();
    return reset($instance_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function resetWidgets(FormStateInterface $form_state) {
    $form_state->set('entity_browser_current_widget', NULL);
    $this->getWidgets()->sort();
    $this->widgetSelectorCollection = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function addAdditionalWidgetParameters(array $parameters) {
    $this->additional_widget_parameters += $parameters;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdditionalWidgetParameters() {
    return $this->get('additional_widget_parameters');
  }

  /**
   * Returns selection display plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The tag plugin collection.
   */
  protected function selectionDisplayPluginCollection() {
    if (!$this->selectionDisplayCollection) {
      $this->selection_display_configuration['entity_browser_id'] = $this->id();
      $this->selectionDisplayCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.selection_display'), $this->selection_display, $this->selection_display_configuration);
    }
    return $this->selectionDisplayCollection;
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
   *   The tag plugin collection.
   */
  protected function widgetSelectorPluginCollection() {
    if (!$this->widgetSelectorCollection) {
      $options = array();
      foreach ($this->getWidgets()->getInstanceIds() as $id) {
        $options[$id] = $this->getWidgets()->get($id)->label();
      }
      $this->widget_selector_configuration['widget_ids'] = $options;
      $this->widgetSelectorCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.entity_browser.widget_selector'), $this->widget_selector, $this->widget_selector_configuration);
    }
    return $this->widgetSelectorCollection;
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
    $this->getSelectionDisplay()->setSelectedEntities($this->selectedEntities);
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectedEntities(array $entities) {
    $this->selectedEntities = array_merge($this->selectedEntities, $entities);
    $this->getSelectionDisplay()->setSelectedEntities($this->selectedEntities);
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
    // When entity browser gets unserialized we end up with two instances of the
    // class and we must be sure only unserialized one is subscribed to events.
    foreach ([Events::SELECTED, Events::DONE] as $event) {
      $existing = $event_dispatcher->getListeners($event);
      foreach ($existing as $listener) {
        if (count($listener) == 2 && $listener[0] instanceof EntityBrowserInterface && $listener[0]->id() == $this->id()) {
          $event_dispatcher->removeListener($event, $listener);
        }
      }
    }

    $event_dispatcher->addListener(Events::SELECTED, [$this, 'onSelected']);
    $event_dispatcher->addListener(Events::DONE, [$this, 'selectionCompleted']);
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

    $form['selected_entities'] = array(
      '#type' => 'value',
      '#value' => array_map(function(EntityInterface $item) {return $item->id();}, $this->getSelectedEntities())
    );

    $form['#browser_parts'] = array(
      'widget_selector' => 'widget_selector',
      'widget' => 'widget',
      'selection_display' => 'selection_display',
    );
    $this->getWidgetSelector()->setDefaultWidget($this->getCurrentWidget($form_state));
    $form[$form['#browser_parts']['widget_selector']] = $this->getWidgetSelector()->getForm($form, $form_state);
    $form[$form['#browser_parts']['widget']] = $this->getWidgets()->get($this->getCurrentWidget($form_state))->getForm($form, $form_state, $this->getAdditionalWidgetParameters());

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => t('Select'),
      ],
    ];

    $form[$form['#browser_parts']['selection_display']] = $this->getSelectionDisplay()->getForm($form, $form_state);

    if ($this->getDisplay() instanceOf DisplayAjaxInterface) {
      $this->getDisplay()->addAjax($form);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->getWidgetSelector()->validate($form, $form_state);
    $this->getWidgets()->get($this->getCurrentWidget($form_state))->validate($form, $form_state);
    $this->getSelectionDisplay()->validate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $original_widget = $this->getCurrentWidget($form_state);
    if ($new_widget = $this->getWidgetSelector()->submit($form, $form_state)) {
      $this->setCurrentWidget($new_widget, $form_state);
    }

    // Only call widget submit if we didn't change the widget.
    if ($original_widget == $this->getCurrentWidget($form_state)) {
      $this->getWidgets()->get($this->getCurrentWidget($form_state))->submit($form[$form['#browser_parts']['widget']], $form, $form_state);
      $this->getSelectionDisplay()->submit($form, $form_state);
    }
    
    // Save the selected entities to the form state.
    $form_state->set('selected_entities', $this->getSelectedEntities());

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
    // TODO: Allow displays to define more than just path.
    // See: https://www.drupal.org/node/2364193

    $display = $this->getDisplay();
    if ($display instanceof DisplayRouterInterface) {
      $path = $display->path();
      return new Route(
        $path,
        [
          '_controller' => 'Drupal\entity_browser\Controllers\StandalonePage::page',
          '_title_callback' => 'Drupal\entity_browser\Controllers\StandalonePage::title',
          'entity_browser_id' => $this->id(),
        ],
        [
          '_permission' => 'access ' . SafeMarkup::checkPlain($this->id()) . ' entity browser pages',
        ],
        [
          '_admin_route' => TRUE,
        ]
      );
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
    unset($this->widget_selector_configuration['widget_ids']);
  }

  /**
   * Prevent plugin collections from being serialized and correctly serialize
   * selected entities.
   */
  function __sleep() {
    // Save configuration for all plugins.
    $this->widgets = $this->getWidgets()->getConfiguration();
    $this->widget_selector_configuration = $this->widgetSelectorPluginCollection()->getConfiguration();
    $this->display_configuration = $this->widgetSelectorPluginCollection()->getConfiguration();
    $this->selection_display_configuration = $this->selectionDisplayPluginCollection()->getConfiguration();

    // For selected entites only store entity type and id.
    $this->_selected_entities = [];
    foreach ($this->selectedEntities as $entity) {
      $this->_selected_entities[] = [
        'type' => $entity->getEntityTypeId(),
        'id' => $entity->id(),
      ];
    }

    return array_diff(
      array_keys(get_object_vars($this)),
      [
        'widgetsCollection',
        'widgetSelectorCollection',
        'displayCollection',
        'selectionDisplayCollection',
        'selectedEntities'
      ]
    );
  }

  /**
   * Re-register event listeners and load selected entities.
   */
  function __wakeup() {
    $this->subscribeEvents(\Drupal::service('event_dispatcher'));

    $this->selectedEntities = [];
    foreach ($this->_selected_entities as $entity) {
      $this->selectedEntities[] = \Drupal::entityManager()->getStorage($entity['type'])->load($entity['id']);
    }
    $this->getSelectionDisplay()->setSelectedEntities($this->selectedEntities);
  }
}
