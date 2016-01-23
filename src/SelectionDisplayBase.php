<?php

/**
 * Contains \Drupal\entity_browser\SelectionDisplayBase.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\SelectionDoneEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base implementation for selection display plugins.
 */
abstract class SelectionDisplayBase extends PluginBase implements SelectionDisplayInterface, ContainerFactoryPluginInterface {

  use PluginConfigurationFormTrait;

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

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
   * @param \Drupal\Core\Entity\EntityManagerInterface
   *   Entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventDispatcher = $event_dispatcher;
    $this->entityManager = $entity_manager;
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
      $container->get('entity.manager')
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
    return array_diff_key(
      $this->configuration,
      ['entity_browser_id' => 0]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
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
  public function label() {
    $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {}

  /**
   * Marks selection as done - sets value in form state and dispatches event.
   */
  protected function selectionDone(FormStateInterface $form_state) {
    $form_state->set(['entity_browser', 'selection_completed'], TRUE);
    $this->eventDispatcher->dispatch(
      Events::DONE,
      new SelectionDoneEvent(
        $this->configuration['entity_browser_id'],
        $form_state->get(['entity_browser', 'instance_uuid'])
      ));
  }

}
