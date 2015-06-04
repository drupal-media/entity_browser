<?php

/**
 * @file
 * Contains \Drupal\entity_browser\WidgetSelectorBase.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for widget selector plugins.
 */
abstract class WidgetSelectorBase extends PluginBase implements WidgetSelectorInterface {


  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * Available widgets.
   *
   * @var array()
   */
  protected $widgets_options;

  /**
   * ID of the default widget.
   *
   * @var string
   */
  protected $defaultWidget;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->widget_ids = $this->configuration['widget_ids'];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultWidget() {
    return $this->defaultWidget;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultWidget($widget) {
    $this->defaultWidget = $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {}

}
