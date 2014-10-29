<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Display\IFrame.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Page\HtmlFragment;
use Drupal\Core\Page\HtmlPage;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\DisplayBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Presents entity browser in an iFrame.
 *
 * @EntityBrowserDisplay(
 *   id = "iframe",
 *   label = @Translation("iFrame"),
 *   description = @Translation("Displays entity browser in an iFrame."),
 *   uses_route = TRUE
 * )
 */
class IFrame extends DisplayBase implements DisplayRouterInterface {

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
  protected $uuid;

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
   * @return \Drupal\Core\Routing\RouteMatchInterface
   *   The currently active route match object.
   * @return \Drupal\Component\Uuid\UuidInterface
   *   UUID generator interface.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, RouteMatchInterface $current_route_match, UuidInterface $uuid) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
    $this->currentRouteMatch = $current_route_match;
    $this->uuid = $uuid;
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
      $container->get('uuid')
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
    $uuid = $this->uuid->generate();
    return [
      '#theme_wrappers' => ['container'],
      'link' => [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->configuration['link_text'],
        '#attributes' => [
          'href' => '#browser',
          'class' => ['entity-browser-handle', 'entity-browser-iframe'],
          'data-uuid' => $uuid,
        ],
        '#attached' => [
          'js' => [
            drupal_get_path('module', 'entity_browser') . '/js/entity_browser.iframe.js',
            [
              'type' => 'setting',
              'data' => [
                'entity_browser' => [
                  'iframe' => [
                    $uuid => [
                      'src' => Url::fromRoute('entity_browser.' . $this->configuration['entity_browser_id'], [], ['query' => ['uuid' => $uuid]])->toString(),
                      'width' => $this->configuration['width'],
                      'height' => $this->configuration['height'],
                    ]
                  ]
                ]
              ],
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
    $this->eventDispatcher->addListener(KernelEvents::RESPONSE, [$this, 'propagateSelection']);
  }

  /**
   * KernelEvents::RESPONSE listener. Intercepts default response and injects
   * response that will trigger JS to propagate selected entities upstream.
   *
   * @param FilterResponseEvent $event
   *   Response event.
   */
  public function propagateSelection(FilterResponseEvent $event) {
    $render = [
      'labels' => [
        '#markup' => 'Labels: ' . implode(', ', array_map(function (EntityInterface $item) {return $item->label();}, $this->entities)),
        '#attached' => [
          'js' => [
            [
              'type' => 'setting',
              'data' => [
                'entity_browser' => [
                  'iframe' => [
                    'entities' => array_map(function (EntityInterface $item) {return [$item->id(), $item->uuid(), $item->getEntityTypeId()];}, $this->entities),
                    'uuid' => $_GET['uuid'],
                  ],
                ],
              ],
            ],
            drupal_get_path('module', 'entity_browser') . '/js/entity_browser.iframe_selection.js',
          ],
          'library' => [
            'core/drupalSettings',
          ],
        ],
      ],
    ];

    // TODO: Use custom HTML template to remove unecessary items.
    $content = new HtmlPage('');
    $content->setContent(drupal_render($render));
    drupal_process_attached($render);

    $event->setResponse(new Response(\Drupal::service('html_page_renderer')->render($content)));
  }

  /**
   * {@inheritdoc}
   */
  public function path() {
    return '/entity-browser/iframe/' . $this->configuration['entity_browser_id'];
  }

}
