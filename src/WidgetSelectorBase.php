<?php

/**
 * Contains \Drupal\entity_browser\WidgetSelectorBase.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

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
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * Gets first widget based on weights.
   *
   * @param \Drupal\entity_browser\WidgetsBag $widgets
   *   Widgets bag.
   *
   * @return \Drupal\entity_browser\WidgetInterface
   *   First widget.
   */
  protected function getFirstWidget(WidgetsBag $widgets) {
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
