<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\Field\FieldWidget\SingleEntityReference.
 */

namespace Drupal\entity_browser\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
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
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'entity_reference' widget for entity browser.
 * @FieldWidget(
 *   id = "entity_browser_single_entity_reference",
 *   label = @Translation("Entity browser (single)"),
 *   description = @Translation("Uses entity browser to select one entity at a time."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class SingleEntityReference extends WidgetBase implements ContainerFactoryPluginInterface {

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
    $target_id = NULL;
    $entity = NULL;
    $field_widget_display = $this->fieldDisplayManager->createInstance(
      $this->getSetting('field_widget_display'),
      $this->getSetting('field_widget_display_settings') + ['entity_type' => $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type')]
    );
    if ($form_state->isRebuilding()) {
      $form_values = $form_state->getValues();
      if (!empty($form_values[$this->fieldDefinition->getName()][$delta]['target_id'])) {
        $target_id = $form_values[$this->fieldDefinition->getName()][$delta]['target_id'];
        $entity_type = $this->fieldDefinition->getFieldStorageDefinition()
          ->getSetting('target_type');
        $entity_storage = $this->entityManager->getStorage($entity_type);
        $entity = $entity_storage->load($target_id);
      }
    }
    else {
      $referenced_entities = $items->referencedEntities();
      $entity = isset($referenced_entities[$delta]) ? $referenced_entities[$delta] : NULL;
      $target_id = empty($entity) ? NULL : $entity->id();
    }

    $hidden_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName() . '-' . $delta . '-target-id');
    $details_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName() . '-' . $delta);

    // Get info from currently-selected entity.
    $display = [];
    if (!empty($entity)) {
      $display = $field_widget_display->view($entity);
      if (is_string($display)) {
        $display = [
          '#markup' => $display
        ];
      }
      $display['#wrapper_attributes']['data-entity-id'] = $entity->id();
    }

    // Load entity browser element.
    $entity_browser_display = $this->entityManager->getStorage('entity_browser')
      ->load($this->getSetting('entity_browser'))
      ->getDisplay();

    $element += [
      '#id' => $details_id,
      '#type' => 'container',
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        // We need to repeat ID here as it is otherwise skipped when rendering.
        '#attributes' => ['id' => $hidden_id],
        '#default_value' => $target_id,
        // #ajax is officially not supported for hidden elements but if we
        // specify event manually it works.
        '#ajax' => [
          'callback' => array($this, 'selectEntitiesCallback'),
          'wrapper' => $details_id,
          'event' => 'entity_browser_value_updated',
        ],
      ],
      'entity_browser' => $entity_browser_display->displayEntityBrowser(),
      '#attached' => ['library' => ['entity_browser/entity_reference_single']],
      'current' => $display,
    ];

    // @todo This will only work with Modal. See if we can make it more generic.
    $element['entity_browser']['link']['#attributes']['href'] =
      Url::fromRoute('entity_browser.' . $entity_browser_display->getConfiguration()['entity_browser_id'], [], [
        'query' => [
          'uuid' => $element['entity_browser']['link']['#attributes']['data-uuid'],
          'original_path' => $element['entity_browser']['link']['#attributes']['data-original-path'],
          'hidden_id' => $hidden_id,
        ]
      ])->toString();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Nothing to massage.
    return $values;
  }

  /**
   * Registers JS callback that gets entities from entity browser and updates
   * form values accordingly.
   */
  public function registerJSCallback(RegisterJSCallbacks $event) {
    if ($event->getBrowserID() == $this->getSetting('entity_browser')) {
      $event->registerCallback('Drupal.entityBrowserEntityReferenceSingle.selectionCompleted');
    }
  }

  /**
   * AJAX form callback for hidden value updated event.
   */
  public function selectEntitiesCallback(array &$form, FormStateInterface $form_state) {
    $result = $form[$this->fieldDefinition->getName()];
    return $result;
  }

}
