<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Display\IFrame.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Page\HtmlFragment;
use Drupal\Core\Page\HtmlPage;
use Drupal\Core\Url;
use Drupal\entity_browser\DisplayBase;
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
   * {@inheritdoc}
   */
  public function displayEntityBrowser() {
    return array(
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#value' => '',
      '#attributes' => array(
        'src' => Url::fromRoute('entity_browser.' . $this->configuration['entity_browser_id'])->toString(),
        // @TODO enforce default values - maybe vi CRUD UI once we have it?
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
    // TODO use real implementation.

    $render = [
      'labels' => [
        '#markup' => 'Labels: ' . implode(', ', array_map(function (EntityInterface $item) {return $item->label();}, $this->entities)),
        '#attached' => [
          'js' => [
            0 => [
              'type' => 'setting',
              'data' => [
                'entity_browser' => [
                  'iframe' => array_map(function (EntityInterface $item) {return [$item->id(), $item->uuid(), $item->getEntityTypeId()];}, $this->entities),
                ],
              ],
            ],
          ],
        ],
      ],
    ];

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
