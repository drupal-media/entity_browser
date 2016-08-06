<?php

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\DisplayBase;
use Drupal\entity_browser\DisplayRouterInterface;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\RegisterJSCallbacks;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\entity_browser\Ajax\SelectEntitiesCommand;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\entity_browser\Events\AlterEntityBrowserDisplayData;

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
class Modal extends DisplayBase implements DisplayRouterInterface {

  /**
   * Current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

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
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   UUID generator interface.
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface $selection_storage
   *   The selection storage.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The currently active route match object.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, UuidInterface $uuid, KeyValueStoreExpirableInterface $selection_storage, RouteMatchInterface $current_route_match, CurrentPathStack $current_path, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $uuid, $selection_storage);
    $this->currentRouteMatch = $current_route_match;
    $this->currentPath = $current_path;
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
      $container->get('uuid'),
      $container->get('entity_browser.selection_storage'),
      $container->get('current_route_match'),
      $container->get('path.current'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'width' => '650',
      'height' => '500',
      'link_text' => $this->t('Select entities'),
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function displayEntityBrowser(array $element, FormStateInterface $form_state, array &$complete_form, array $persistent_data = []) {
    parent::displayEntityBrowser($element, $form_state, $complete_form, $persistent_data);
    $js_event_object = new RegisterJSCallbacks($this->configuration['entity_browser_id'], $this->getUuid());
    $js_event_object->registerCallback('Drupal.entityBrowser.selectionCompleted');
    $js_event = $this->eventDispatcher->dispatch(Events::REGISTER_JS_CALLBACKS, $js_event_object);
    $original_path = $this->currentPath->getPath();

    $data = [
      'query_parameters' => [
        'query' => [
          'uuid' => $this->getUuid(),
          'original_path' => $original_path,
        ],
      ],
      'attributes' => [
        'data-uuid' => $this->getUuid(),
      ],
    ];
    $event_object = new AlterEntityBrowserDisplayData($this->configuration['entity_browser_id'], $this->getUuid(), $this->getPluginDefinition(), $form_state, $data);
    $event = $this->eventDispatcher->dispatch(Events::ALTER_BROWSER_DISPLAY_DATA, $event_object);
    $data = $event->getData();
    return [
      '#theme_wrappers' => ['container'],
      'path' => [
        '#type' => 'hidden',
        '#value' => Url::fromRoute('entity_browser.' . $this->configuration['entity_browser_id'], [], $data['query_parameters'])->toString(),
      ],
      'open_modal' => [
        '#type' => 'submit',
        '#value' => $this->configuration['link_text'],
        '#limit_validation_errors' => [],
        '#submit' => [],
        '#name' => implode('_', $element['#eb_parents']),
        '#ajax' => [
          'callback' => [$this, 'openModal'],
          'event' => 'click',
        ],
        '#executes_submit_callback' => FALSE,
        '#attributes' => $data['attributes'],
        '#attached' => [
          'library' => ['core/drupal.dialog.ajax', 'entity_browser/modal'],
          'drupalSettings' => [
            'entity_browser' => [
              'modal' => [
                $this->getUuid() => [
                  'uuid' => $this->getUuid(),
                  'js_callbacks' => $js_event->getCallbacks(),
                  'original_path' => $original_path,
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Generates the content and opens the modal.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An ajax response.
   */
  public function openModal(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = $triggering_element['#parents'];
    array_pop($parents);
    $parents = array_merge($parents, ['path']);
    $input = $form_state->getUserInput();
    $src = NestedArray::getValue($input, $parents);

    $field_name = $triggering_element['#parents'][0];
    $element_name = $this->configuration['entity_browser_id'];
    $name = 'entity_browser_iframe_' . $element_name;
    $content = [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'src' => $src,
        'class' => 'entity-browser-modal-iframe',
        'width' => '100%',
        'height' => $this->configuration['height'] - 90,
        'frameborder' => 0,
        'style' => 'padding:0',
        'name' => $name,
        'id' => $name,
      ],
    ];
    $html = drupal_render($content);

    $response = new AjaxResponse();
    $response->addCommand(new OpenDialogCommand('#' . Html::getUniqueId($field_name . '-' . $element_name . '-dialog'), $this->configuration['link_text'], $html, [
      'width' => 'auto',
      'height' => 'auto',
      'modal' => TRUE,
      'maxWidth' => $this->configuration['width'],
      'maxHeight' => $this->configuration['height'],
      'fluid' => 1,
      'autoResize' => 0,
      'resizable' => 0,
    ]));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function addAjax(array &$form) {
    // Set a wrapper container to replace the form on ajax callback.
    $form['#prefix'] = '<div id="entity-browser-form">';
    $form['#suffix'] = '</div>';

    // Add the browser id to use in the FormAjaxController.
    $form['browser_id'] = [
      '#type' => 'hidden',
      '#value' => $this->configuration['entity_browser_id'],
    ];

    $form['actions']['submit']['#ajax'] = [
      'callback' => [$this, 'widgetAjaxCallback'],
      'wrapper' => 'entity-browser-form',
    ];
  }

  /**
   * Ajax callback for entity browser form.
   *
   * Allows the entity browser form to submit the form via ajax.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Response.
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
   * Helper function to return commands to return in AjaxResponse.
   *
   * @return array
   *   An array of ajax commands.
   */
  public function getAjaxCommands(FormStateInterface $form_state) {
    $entities = array_map(function(EntityInterface $item) {
      return [$item->id(), $item->uuid(), $item->getEntityTypeId()];
    }, $form_state->get(['entity_browser', 'selected_entities']));

    $commands = [];
    $commands[] = new SelectEntitiesCommand($this->uuid, $entities);

    return $commands;
  }

  /**
   * KernelEvents::RESPONSE listener.
   *
   * Intercepts default response and injects
   * response that will trigger JS to propagate selected entities upstream.
   *
   * @param FilterResponseEvent $event
   *   Response event.
   */
  public function propagateSelection(FilterResponseEvent $event) {
    $render = [
      'labels' => [
        '#markup' => 'Labels: ' . implode(', ', array_map(function (EntityInterface $item) {
          return $item->label();
        }, $this->entities)),
        '#attached' => [
          'library' => ['entity_browser/modal_selection'],
          'drupalSettings' => [
            'entity_browser' => [
              'modal' => [
                'entities' => array_map(function (EntityInterface $item) {
                  return [$item->id(), $item->uuid(), $item->getEntityTypeId()];
                }, $this->entities),
                'uuid' => $this->request->query->get('uuid'),
              ],
            ],
          ],
        ],
      ],
    ];

    $event->setResponse(new Response(\Drupal::service('bare_html_page_renderer')->renderBarePage($render, 'Entity browser', 'page')));
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
  public function __sleep() {
    return ['configuration'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width of the modal'),
      '#default_value' => $configuration['width'],
      '#description' => $this->t('Empty value for responsive width.'),
    ];
    $form['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height of the modal'),
      '#default_value' => $configuration['height'],
      '#description' => $this->t('Empty value for responsive height.'),
    ];
    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $configuration['link_text'],
    ];
    return $form;
  }

}
