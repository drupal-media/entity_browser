<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Display\Modal.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\DisplayAjaxInterface;
use Drupal\entity_browser\DisplayBase;
use Drupal\entity_browser\DisplayRouterInterface;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\RegisterJSCallbacks;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\entity_browser\Ajax\SelectEntitiesCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Presents entity browser in an Modal.
 *
 * @EntityBrowserDisplay(
 *   id = "modal",
 *   label = @Translation("Modal"),
 *   description = @Translation("Displays entity browser in a Modal."),
 *   uses_route = TRUE
 * )
 */
class Modal extends DisplayBase implements DisplayRouterInterface, DisplayAjaxInterface {

  /**
   * Current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * UUID generator interface.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * UIID string.
   *
   * @var string
   */
  protected $uuid = NULL;

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
   * @param \Drupal\Core\Routing\RouteMatchInterface
   *   The currently active route match object.
   * @param \Drupal\Component\Uuid\UuidInterface
   *   UUID generator interface.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, RouteMatchInterface $current_route_match, UuidInterface $uuid, CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
    $this->currentRouteMatch = $current_route_match;
    $this->uuidGenerator = $uuid;
    $this->currentPath = $current_path;
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
      $container->get('current_route_match'),
      $container->get('uuid'),
      $container->get('path.current')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'width' => 650,
      'height' => 500,
      'link_text' => t('Select entities'),
    ) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function displayEntityBrowser() {
    $uuid = $this->getUuid();
    /** @var \Drupal\entity_browser\Events\RegisterJSCallbacks $event */
    $event = $this->eventDispatcher->dispatch(Events::REGISTER_JS_CALLBACKS, new RegisterJSCallbacks($this->configuration['entity_browser_id'], $uuid));
    $original_path = $this->currentPath->getPath();
    return [
      '#theme_wrappers' => ['container'],
      'link' => [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->configuration['link_text'],
        '#attributes' => [
          'href' => Url::fromRoute('entity_browser.' . $this->configuration['entity_browser_id'], [], [
            'query' => [
              'uuid' => $uuid,
              'original_path' => $original_path,
            ]
          ])->toString(),
          'class' => ['entity-browser-modal', 'use-ajax'],
          'data-uuid' => $uuid,
          'data-dialog-options' => json_encode(
            array(
              'width' => $this->configuration['width'],
              'height' => $this->configuration['height'],
            )
          ),
          'data-dialog-type' => 'modal',
          'data-original-path' => $original_path,
        ],
        '#attached' => [
          'library' => ['core/drupal.ajax', 'entity_browser/modal'],
          'drupalSettings' => [
            'entity_browser' => [
              'modal' => [
                'uuid' => $uuid,
                'js_callbacks' => $event->getCallbacks(),
                'original_path' => $original_path,
              ]
            ]
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function selectionCompleted(array $entities) {
    $this->entities = $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function addAjax(array &$form) {
    // Set a wrapper container to replace the form on ajax callback.
    $form['#prefix'] = '<div id="entity-browser-form">';
    $form['#suffix'] = '</div>';

    // Add the browser id to use in the FormAjaxController.
    $form['browser_id'] = array(
      '#type' => 'hidden',
      '#value' => $this->configuration['entity_browser_id'],
    );

    $form['actions']['submit']['#ajax'] = array(
      'callback' => array($this, 'widgetAjaxCallback'),
      'wrapper' =>  'entity-browser-form',
    );
  }

/**
   * Ajax callback for entity browser form.
   *
   * Allows the entity browser form to submit the form via ajax.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function widgetAjaxCallback(array &$form, FormStateInterface $form_state) {
    // If we've got any validation error, print out the form again.
    if ($form_state->hasAnyErrors()) {
      return $form;
    }

    $commands = $this->getAjaxCommands($form_state);
    $response = new AjaxResponse();
    foreach ($commands as $command) {
      $response->addCommand($command);
    }

    return $response;
  }

  /**
   * Helper function to return commands to return in AjaxResponse
   *
   * @return array
   */
  public function getAjaxCommands(FormStateInterface $form_state) {
    $entities = array_map(function(EntityInterface $item) {return [$item->id(), $item->uuid(), $item->getEntityTypeId()];}, $form_state->get(['entity_browser', 'selected_entities']));
    $commands = array();
    $commands[] = new SelectEntitiesCommand($this->uuid, $entities);
    $commands[] = new CloseDialogCommand();

    return $commands;
  }

  /**
   * {@inheritdoc}
   */
  public function path() {
    return '/entity-browser/modal/' . $this->configuration['entity_browser_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid() {
    if (empty($this->uuid)) {
      $this->uuid = $this->uuidGenerator->generate();
    }
    return $this->uuid;
  }

  public function __sleep() {
    return array();
  }
}
