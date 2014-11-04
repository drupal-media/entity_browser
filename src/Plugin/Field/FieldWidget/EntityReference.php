<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReference.
 */

namespace Drupal\entity_browser\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\RegisterJSCallbacks;
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
   *
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityManagerInterface $entity_manager, EventDispatcherInterface $event_dispatcher) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityManager = $entity_manager;

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
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'entity_browser' => NULL,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $options = [];
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    foreach ($this->entityManager->getStorage('entity_browser')->loadMultiple() as $browser) {
      $options[$browser->id()] = $browser->label();
    }

    $element['entity_browser'] = [
      '#title' => t('Entity browser'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('entity_browser'),
      '#options' => $options,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $entity_browser_id = $this->getSetting('entity_browser');
    if (empty($entity_browser_id)) {
      return [t('No entity browser selected.')];
    }

    $browser = $this->entityManager->getStorage('entity_browser')->load($entity_browser_id);
    return [t('Entity browser: @browser', ['@browser' => $browser->label()])];

  }

  /**
   * {@inheritdoc}
   */
  function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $ids = [];
    foreach ($items as $item) {
      $ids[] = $item->target_id;
    }

    return [
      'target_id' => [
        '#type' => 'hidden',
        '#default_value' => $ids,
      ],
      'entity_browser' => $this->entityManager->getStorage('entity_browser')->load($this->getSetting('entity_browser'))->getDisplay()->displayEntityBrowser(),
      '#attached' => [
        'js' => [
          drupal_get_path('module', 'entity_browser') . '/js/entity_browser.entity_reference.js',
        ],
      ],
      'current' => [
        // TODO - create better display of current entities (possibly using a view)
        // See: https://www.drupal.org/node/2366241
        '#markup' => '<strong>'. t('Selected:') . '</strong> <div class="current-markup">' . implode(' ', $ids) . '</div>',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $ids = explode(' ', $values['target_id']);
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
      $event->registerCallback('Drupal.entityBrowserEntityReference.selectionCompleted');
    }
  }
}
