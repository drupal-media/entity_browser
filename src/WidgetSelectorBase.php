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
   * @var \Drupal\entity_browser\WidgetsCollection
   */
  protected $widgets;

  /**
   * Current Widget.
   *
   * @var \Drupal\entity_browser\WidgetInterface
   */
  protected $currentWidget;

  /**
   * {@inheritdoc}
   */
  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->widgets = $this->configuration['widgets'];
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
      $this->currentWidget = $this->getFirstWidget($this->widgets);
    }

    return $this->currentWidget;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentWidget(WidgetInterface $widget) {
    $this->currentWidget = $widget;
  }

  /**
   * Gets first widget based on weights.
   *
   * @param \Drupal\entity_browser\WidgetsCollection $widgets
   *   Widgets bag.
   *
   * @return \Drupal\entity_browser\WidgetInterface
   *   First widget.
   */
  protected function getFirstWidget(WidgetsCollection $widgets) {
    if ($widgets->count() > 1) {
      $widgets->sort();
    }

    $widgets->rewind();
    return $widgets->current();
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
