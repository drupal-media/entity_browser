<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReference.
 */

namespace Drupal\entity_browser\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\RegisterJSCallbacks;
use Drupal\entity_browser\FieldWidgetDisplayManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Plugin implementation of the 'entity_reference' widget for entity browser.

 * @FieldWidget(
 *   id = "entity_browser_entity_reference",
 *   label = @Translation("Entity browser"),
 *   description = @Translation("Uses entity browser to select entities."),
 *   multiple_values = TRUE,
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReference extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Entity manager service
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Field widget display plugin manager.
   *
   * @var \Drupal\entity_browser\FieldWidgetDisplayManager
   */
  protected $fieldDisplayManager;

  /**
   * Constructs widget plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   Entity manager service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\entity_browser\FieldWidgetDisplayManager $field_display_manager
   *   Field widget display plugin manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityManagerInterface $entity_manager, EventDispatcherInterface $event_dispatcher, FieldWidgetDisplayManager $field_display_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityManager = $entity_manager;
    $this->fieldDisplayManager = $field_display_manager;

    $event_dispatcher->addListener(Events::REGISTER_JS_CALLBACKS, [$this, 'registerJSCallback']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity.manager'),
      $container->get('event_dispatcher'),
      $container->get('plugin.manager.entity_browser.field_widget_display')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'entity_browser' => NULL,
      'field_widget_display' => NULL,
      'field_widget_display_settings' => [],
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $browsers = [];
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    foreach ($this->entityManager->getStorage('entity_browser')->loadMultiple() as $browser) {
      $browsers[$browser->id()] = $browser->label();
    }

    $element['entity_browser'] = [
      '#title' => t('Entity browser'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('entity_browser'),
      '#options' => $browsers,
    ];

    $displays = [];
    foreach ($this->fieldDisplayManager->getDefinitions() as $id => $definition) {
      $displays[$id] = $definition['label'];
    }

    $id = Html::getUniqueId('field-' . $this->fieldDefinition->getName() . '-display-settings-wrapper');
    $element['field_widget_display'] = [
      '#title' => t('Entity display plugin'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('field_widget_display'),
      '#options' => $displays,
      '#validate' => [[$this, 'submitFieldWidgetDisplay']],
      '#ajax' => [
        'callback' => array($this, 'updateSettingsAjax'),
        'wrapper' => $id,
      ],
    ];

    $element['field_widget_display_settings'] = [
      '#type' => 'fieldset',
      '#title' => t('Entity display plugin configuration'),
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $id . '">',
      '#suffix' => '</div>',
    ];

    if ($this->getSetting('field_widget_display')) {
      $element['field_widget_display_settings'] += $this->fieldDisplayManager
        ->createInstance(
          $form_state->getValue(
            ['fields', $this->fieldDefinition->getName(), 'settings_edit_form', 'settings', 'field_widget_display'],
            $this->getSetting('field_widget_display')
          ),
          $form_state->getValue(
            ['fields', $this->fieldDefinition->getName(), 'settings_edit_form', 'settings', 'field_widget_display_settings'],
            $this->getSetting('field_widget_display_settings')
          ) + ['entity_type' => $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type')]
        )
        ->settingsForm($form, $form_state);
    }

    return $element;
  }

  /**
   * Ajax callback that updates field widget display settings fieldset.
   */
  public function updateSettingsAjax(array $form, FormStateInterface $form_state) {
    return $form['fields'][$this->fieldDefinition->getName()]['plugin']['settings_edit_form']['settings']['field_widget_display_settings'];
  }

    /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $entity_browser_id = $this->getSetting('entity_browser');
    $field_widget_display = $this->getSetting('field_widget_display');

    if (empty($entity_browser_id)) {
      return [t('No entity browser selected.')];
    }
    else {
      $browser = $this->entityManager->getStorage('entity_browser')
        ->load($entity_browser_id);
      $summary[] = t('Entity browser: @browser', ['@browser' => $browser->label()]);
    }

    if (!empty($field_widget_display)) {
      $plugin = $this->fieldDisplayManager->getDefinition($field_widget_display);
      $summary[] = t('Entity display: @name', ['@name' => $plugin['label']]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $entity_storage = $this->entityManager->getStorage($entity_type);
    $field_widget_display = $this->fieldDisplayManager->createInstance(
      $this->getSetting('field_widget_display'),
      $this->getSetting('field_widget_display_settings') + ['entity_type' => $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type')]
    );

    $ids = [];
    if (($trigger = $form_state->getTriggeringElement()) && !empty($trigger['#ajax']['event']) && $trigger['#ajax']['event'] == 'entity_browser_value_updated' && in_array($this->fieldDefinition->getName(), $trigger['#parents'])) {
      if ($value = $form_state->getValue($trigger['#parents'])) {
        $ids = explode(' ', $value);
      }
    }
    else {
      foreach ($items as $item) {
        $ids[] = $item->target_id;
      }
    }
    $ids = array_filter($ids);

    $hidden_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName() . '-target-id');
    $details_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName());
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $this->entityManager->getStorage('entity_browser')->load($this->getSetting('entity_browser'));

    $element += [
      '#id' => $details_id,
      '#type' => 'details',
      '#open' => !empty($ids),
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        // We need to repeat ID here as it is otherwise skipped when rendering.
        '#attributes' => ['id' => $hidden_id],
        '#default_value' => $ids,
        // #ajax is officially not supported for hidden elements but if we
        // specify event manually it works.
        '#ajax' => [
          'callback' => array($this, 'selectEntitiesCallback'),
          'wrapper' => $details_id,
          'event' => 'entity_browser_value_updated',
        ],
      ],
      'entity_browser' => $entity_browser->getDisplay()->displayEntityBrowser(),
      '#attached' => [
        'library' => ['entity_browser/entity_reference'],
        'drupalSettings' => [
          'entity_browser' => [
            'field_settings' => [
              $entity_browser->getDisplay()->getUuid() => [
                'cardinality' => $this->fieldDefinition->getFieldStorageDefinition()->getCardinality(),
              ],
            ],
          ],
        ],
      ],
      'current' => [
        '#theme' => 'item_list',
        '#items' => array_map(
          function($id) use ($entity_storage, $field_widget_display) {
            $entity = $entity_storage->load($id);
            $display = $field_widget_display->view($entity);

            if (is_string($display)) {
              $display = [
                '#markup' => $display
              ];
            }

            $display['#wrapper_attributes']['data-entity-id'] = $entity->id();
            return $display;
          },
          $ids
        ),
        '#attached' => ['library' => ['core/jquery.ui.sortable']],
        '#attributes' => ['class' => ['entities-list']],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $ids = empty($values['target_id']) ? [] : explode(' ', trim($values['target_id']));
    $return = [];
    foreach ($ids as $id) {
      $return[]['target_id'] = $id;
    }

    return $return;
  }

  /**
   * Registers JS callback that gets entities from entity browser and updates
   * form values accordingly.
   */
  public function registerJSCallback(RegisterJSCallbacks $event) {
    if ($event->getBrowserID() == $this->getSetting('entity_browser')) {
      $event->registerCallback('Drupal.entityBrowser.selectionCompleted');
    }
  }

  /**
   * AJAX form callback for hidden value updated event.
   */
  public function selectEntitiesCallback(array &$form, FormStateInterface $form_state) {
    $parents = array_splice($form_state->getTriggeringElement()['#array_parents'], 0, -2);
    return NestedArray::getValue($form, $parents);
  }

}
