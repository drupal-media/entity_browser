<?php

namespace Drupal\entity_browser_entity_form\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\WidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "entity_form",
 *   label = @Translation("Entity form"),
 *   description = @Translation("Provides entity form widget.")
 * )
 */
class EntityForm extends WidgetBase {

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_type_manager);
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
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
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'entity_type' => NULL,
      'bundle' => NULL,
    ) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    if (empty($this->configuration['entity_type']) || empty($this->configuration['bundle'])) {
      return [
        '#markup' => t('Entity type or bundle are no configured correctly.'),
      ];
    }

    return [
      'inline_entity_form' => [
        '#type' => 'inline_entity_form',
        '#op' => 'add',
        '#entity_type' => $this->configuration['entity_type'],
        '#bundle' => $this->configuration['bundle'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $this->selectEntities([$element['inline_entity_form']['#entity']], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $parents = ['table', $this->uuid(), 'form'];
    $entity_type = $form_state->hasValue(array_merge($parents, ['entity_type'])) ? $form_state->getValue(array_merge($parents, ['entity_type'])) : $this->configuration['entity_type'];
    $bundle = $form_state->hasValue(array_merge($parents, ['bundle', 'select'])) ? $form_state->getValue(array_merge($parents, ['bundle', 'select'])) : $this->configuration['bundle'];

    $definitions = $this->entityTypeManager->getDefinitions();
    $entity_types = array_combine(
      array_keys($definitions),
      array_map(function (EntityTypeInterface $item) {
        return $item->getLabel();
      }, $definitions)
    );

    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => $entity_types,
      '#default_value' => $entity_type,
      '#ajax' => [
        'wrapper' => 'bundle-wrapper',
        'callback' => [$this, 'updateBundle'],
      ],
    ];

    $bundles = [];
    if ($entity_type) {
      $definitions = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
      $bundles = array_map(function ($item) {
        return $item['label'];
      }, $definitions);
    }

    $form['bundle'] = [
      '#type' => 'container',
      'select' => [
        '#type' => 'select',
        '#title' => $this->t('Bundle'),
        '#options' => $bundles,
        '#default_value' => $bundle,
      ],
      '#attributes' => ['id' => 'bundle-wrapper'],
    ];

    return $form;
  }

  /**
   * AJAX callback for bundle dropdown update.
   */
  public function updateBundle($form, FormStateInterface $form_state) {
    return $form['widgets']['table'][$this->uuid()]['form']['bundle'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['bundle'] = $this->configuration['bundle']['select'];
  }

}
