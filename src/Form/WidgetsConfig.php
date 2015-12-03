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
  protected $tempstore;

  /**
   * Constructs GeneralInfoConfig form class.
   *
   * @param \Drupal\entity_browser\DisplayManager $display_manager
   *   Entity browser display plugin manager.
   * @param \Drupal\entity_browser\WidgetSelectorManager $widget_selector
   *   Entity browser widget selector plugin manager.
   * @param \Drupal\entity_browser\SelectionDisplayManager
   *   Entity browser selection display plugin manager.
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
      $row['operations'] = [];
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
    return $form;
  }

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
