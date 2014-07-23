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
 *   description = @Translation("Displays entity browser as a standalone form.")
 * )
 */
class Standalone extends PluginBase implements EntityBrowserDisplayInterface {

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
}
