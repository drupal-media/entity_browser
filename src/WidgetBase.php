<?php

/**
 * Contains \Drupal\entity_browser\WidgetBase.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for widget plugins.
 */
abstract class WidgetBase extends PluginBase implements EntityBrowserWidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->configuration['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->configuration['weight'];
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->configuration['weight'] = $weight;
    return $this;
  }

}
