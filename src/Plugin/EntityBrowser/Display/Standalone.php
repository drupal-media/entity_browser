<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Display\Standalone.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Display;

use Drupal\Component\Plugin\PluginBase;
use Drupal\entity_browser\EntityBrowserDisplayInterface;

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
class Standalone extends PluginBase implements EntityBrowserDisplayInterface, DisplayRouterInterface {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

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
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function selectionCompleted() {
    // @TODO Implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function path() {
    return $this->configuration['path'];
  }

}
