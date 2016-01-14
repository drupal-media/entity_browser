<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Form\WidgetsConfig.
 */

namespace Drupal\entity_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\WidgetManager;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WidgetsConfig extends FormBase {

  /**
   * Entity browser widget plugin manager.
   *
   * @var \Drupal\entity_browser\WidgetManager
   */
  protected $widgetManager;

  /**
   * Tempstore Factory for keeping track of values in each step of the wizard.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempStore;

  /**
   * WidgetsConfig constructor.
   * @param \Drupal\entity_browser\WidgetManager $widget_manager
   * @param \Drupal\user\SharedTempStoreFactory $temp_store
   * @param null $tempstore_id
   * @param null $machine_name
   */
  function __construct(WidgetManager $widget_manager, SharedTempStoreFactory $temp_store, $tempstore_id = NULL, $machine_name = NULL) {
    $this->widgetManager = $widget_manager;
    $this->tempStore = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.entity_browser.widget'),
      $container->get('user.shared_tempstore')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_widgets_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $form_state->getTemporaryValue('wizard')['entity_browser'];

    $widgets = [];
    foreach ($this->widgetManager->getDefinitions() as $plugin_id => $plugin_definition) {
      $widgets[$plugin_id] = $plugin_definition['label'];
    }
    $default_widgets = [];
    foreach ($entity_browser->getWidgets() as $widget) {
      /** @var \Drupal\entity_browser\WidgetInterface $widget */
      $default_widgets[] = $widget->id();
    }
    $form['widget'] = [
      '#type' => 'select',
      '#title' => $this->t('Add widget plugin'),
      '#options' => ['_none_' => '- ' . $this->t('Select a widget to add it') . ' -'] + $widgets,
      '#ajax' => [
        'callback' => [get_class($this), 'addWidgetCallback'],
        'wrapper' => 'widgets',
        'event' => 'change'
      ],
      '#executes_submit_callback' => TRUE,
      '#submit' => [[get_class($this), 'submitAddWidget']],
      '#limit_validation_errors' => [['widget']],
    ];
    $form_state->unsetValue('widget');

    $form['widgets'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Form'),
        $this->t('Operations'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('There are no widgets.'),
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'variant-weight',
      ]],
    ];

    /** @var \Drupal\entity_browser\WidgetInterface $widget */
    foreach ($entity_browser->getWidgets() as $widget) {
      $row = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
      ];
      $row['label'] = [
        '#type' => 'textfield',
        '#default_value' => $widget->label(),
        '#title' => $this->t('Label'),
      ];
      $row['form'] = [];
      $row['form'] = $widget->buildConfigurationForm($row['form'], $form_state);
      $row['weight'] = [
        '#type' => 'weight',
        '#default_value' => $widget->getWeight(),
        '#title' => $this->t('Weight for @widget widget', ['@widget' => $widget->label()]),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => ['variant-weight'],
        ],
      ];
      $form['widgets'][$widget->uuid()] = $row;
    }
    $form['#attached']['library'][] = 'entity_browser/widgets';
    return $form;
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param null $tempstore_id
   */
  public static function submitAddWidget($form, FormStateInterface $form_state, $tempstore_id = NULL) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $cached_values['entity_browser'];
    $widget = $form_state->getValue('widget');
    $entity_browser->addWidget([
      'id' => $widget,
      'label' => $widget,
      'weight' => 0,
      // Configuration will be set on the widgets page.
      'settings' => [],
    ]);
    \Drupal::service('user.shared_tempstore')
      ->get('entity_browser.config')
      ->set($entity_browser->id(), $cached_values);
    $form_state->setRebuild();
  }

  /**
   * @param $form
   * @param $form_state
   * @return mixed
   */
  public static function addWidgetCallback($form, $form_state) {
    return $form['widgets'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $form_state->getTemporaryValue('wizard')['entity_browser'];
    /** @var \Drupal\entity_browser\WidgetInterface $widget */
    foreach ($entity_browser->getWidgets() as $widget) {
      $widget->validateConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $form_state->getTemporaryValue('wizard')['entity_browser'];
    /** @var \Drupal\entity_browser\WidgetInterface $widget */
    foreach ($entity_browser->getWidgets() as $widget) {
      $widget->submitConfigurationForm($form, $form_state);
    }
  }

}
