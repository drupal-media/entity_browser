<?php

/**
 * Contains \Drupal\entity_browser\WidgetBase.
 */

namespace Drupal\entity_browser;

use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\Events\EntitySelectionEvent;
use Drupal\entity_browser\Events\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for widget plugins.
 */
abstract class WidgetBase extends PluginBase implements WidgetInterface, ContainerFactoryPluginInterface {

  use PluginConfigurationFormTrait;

  /**
   * Plugin id.
   *
   * @var string
   */
  protected $id;

  /**
   * Plugin uuid.
   *
   * @var string
   */
  protected $uuid;
  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * Plugin weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The Expirable key value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface
   */
  private $keyValue;

  /**
   * The Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager, KeyValueStoreExpirableInterface $key_value, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventDispatcher = $event_dispatcher;
    $this->entityManager = $entity_manager;
    $this->setConfiguration($configuration);
    $this->keyValue = $key_value;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity.manager'),
      $container->get('keyvalue.expirable')->get('entity_browser'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'settings' => array_diff_key(
        $this->configuration,
        ['entity_browser_id' => 0]
      ),
      'uuid' => $this->uuid(),
      'weight' => $this->getWeight(),
      'label' => $this->label(),
      'id' => $this->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration += [
      'settings' => [],
      'uuid' => '',
      'weight' => '',
      'label' => '',
      'id' => '',
    ];

    $this->configuration = $configuration['settings'] + $this->defaultConfiguration();
    $this->label = $configuration['label'];
    $this->weight = $configuration['weight'];
    $this->uuid = $configuration['uuid'];
    $this->id = $configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function uuid() {
    return $this->uuid;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareEntities(FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();

    if (in_array('submit', $trigger['#array_parents'])) {
      $entities = $this->prepareEntities($form_state);
      $violations = $this->runWidgetValidators($entities);
      if (!empty($violations)) {
        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violation */
        foreach ($violations as $violation) {
          $form_state->setError($form['widget']['upload'], $violation->getMessage());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function runWidgetValidators(array $entities, $validators = []) {
    // @todo Implement a centralized way to get arguments from path for all widgets?
    if ($validators_hash = $this->request->get('validators')) {
      $passed_validators = $this->keyValue->get($validators_hash);

      if (!empty($passed_validators)) {
        $validators += $passed_validators;
      }
    }

    foreach ($validators as $validator_id => $options) {
      /** @var \Drupal\entity_browser\WidgetValidationInterface $widget_validator */
      $widget_validator = \Drupal::service('plugin.manager.entity_browser.widget_validation')->createInstance($validator_id, []);
    }
    return $widget_validator->validate($entities, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {}

  /**
   * Dispatches event that informs all subscribers about new selected entities.
   *
   * @param array $entities
   *   Array of entities.
   */
  protected function selectEntities(array $entities, FormStateInterface $form_state) {
    $selected_entities = &$form_state->get(['entity_browser', 'selected_entities']);
    $selected_entities = array_merge($selected_entities, $entities);

    $this->eventDispatcher->dispatch(
      Events::SELECTED,
      new EntitySelectionEvent(
        $this->configuration['entity_browser_id'],
        $form_state->get(['entity_browser', 'instance_uuid']),
        $entities
      ));
  }

}
