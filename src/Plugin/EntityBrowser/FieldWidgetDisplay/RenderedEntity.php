<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay\RenderedEntity.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\entity_browser\FieldWidgetDisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays the fully rendered entity.
 *
 * @EntityBrowserFieldWidgetDisplay(
 *   id = "rendered_entity",
 *   label = @Translation("Rendered entity"),
 *   description = @Translation("Displays fully rendered entity.")
 * )
 */
class RenderedEntity extends PluginBase implements FieldWidgetDisplayInterface, ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   Entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity) {
    return $this->entityManager->getViewBuilder($entity->getEntityTypeId())->view($entity, $this->configuration['view_mode']);
  }

}
