<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Display\IFrame.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\DisplayInterface;
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
class IFrame extends PluginBase implements DisplayInterface, DisplayRouterInterface {

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
  protected $entities;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function displayEntityBrowser() {
    return array(
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#value' => '',
      '#attributes' => array(
        'src' => Url::fromRoute('entity_browser.' . $this->configuration['entity_browser_id'])->toString(),
        'width' => empty($this->configuration['iframe_width']) ? 650 : $this->configuration['iframe_width'],
        'height' => empty($this->configuration['iframe_height']) ? 500 : $this->configuration['iframe_height'],
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function selectionCompleted(array $entities) {
    $this->entities = $entities;
    \Drupal::service('event_dispatcher')->addListener(KernelEvents::RESPONSE, [$this, 'propagateSelection']);
  }

  /**
   * KernelEvents::RESPONSE listener. Intercepts default response and injects
   * response that will trigger JS to propagate selected entities upstream.
   *
   * @param FilterResponseEvent $event
   *   Response event.
   */
  public function propagateSelection(FilterResponseEvent $event) {
    // TODO use real implementation.
    $content = 'Labels: ' . implode(', ', array_map(function (EntityInterface $item) {return $item->label();}, $this->entities));
    $event->setResponse(new Response($content));
  }

  /**
   * {@inheritdoc}
   */
  public function path() {
    return '/entity-browser/iframe/' . $this->configuration['entity_browser_id'];
  }

}
