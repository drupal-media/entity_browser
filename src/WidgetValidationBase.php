<?php

/**
 * @file
 * Contains \Drupal\entity_browser\WidgetValidationBase.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Base implementation for widget validation plugins.
 */
abstract class WidgetValidationBase extends PluginBase implements WidgetValidationInterface {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $entities, $options = []) {}

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }
}
