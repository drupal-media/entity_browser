<?php

/**
 * Contains \Drupal\entity_browser\WidgetSelectorBase.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginBase;
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
   * Available widgets
   *
   * @var array()
   */
  protected $widgets_options;

  /**
   * Id of Current Widget.
   *
   * @var string
   */
  protected $currentWidget;

  /**
   * {@inheritdoc}
   */
  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->widget_options = $this->configuration['widget_options'];
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
  public function getCurrentWidget() {
    if (!$this->currentWidget) {
      $this->currentWidget = $this->getFirstWidget($this->widget_options);
    }

    return $this->currentWidget;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentWidget($widget) {
    $this->currentWidget = $widget;
  }

  /**
   * Gets first widget based on weights.
   *
   * @param array $widget_options
   *   Array of all the widgets.
   *
   * @return array
   *   First element of the array.
   */
  protected function getFirstWidget(array $widget_options) {
    reset($widget_options);
    
    return key($widget_options);
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
