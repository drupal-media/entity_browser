<?php

/**
 * Contains \Drupal\entity_browser\DisplayBase.
 */

namespace Drupal\entity_browser\DisplayBase;

use Drupal\Component\Plugin\PluginBase;
use Drupal\entity_browser\DisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base implementation for display plugins.
 */
abstract class DisplayBase extends PluginBase implements DisplayInterface {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * Selected entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities = array();

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs display plugin.
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

}
