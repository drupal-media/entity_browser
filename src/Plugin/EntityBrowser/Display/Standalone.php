<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Display\Standalone.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\entity_browser\DisplayBase;
use Drupal\entity_browser\DisplayRouterInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Presents entity browser as a standalone form.
 *
 * @EntityBrowserDisplay(
 *   id = "standalone",
 *   label = @Translation("Standalone form"),
 *   description = @Translation("Displays entity browser as a standalone form."),
 *   uses_route = TRUE
 * )
 */
class Standalone extends DisplayBase implements DisplayRouterInterface {

  /**
   * {@inheritdoc}
   */
  public function displayEntityBrowser(FormStateInterface $form_state) {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function selectionCompleted(array $entities) {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function path() {
    return $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function setUuid($uuid) {
    // @TODO Implement it.
  }

}
