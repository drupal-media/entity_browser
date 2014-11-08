<?php

/**
 * @file
 * Contains \Drupal\entity_browser\WidgetSelectorInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\LazyPluginCollection;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for entity browser widget selectors.
 */
interface WidgetSelectorInterface extends PluginInspectionInterface {

  /**
   * Returns the widget selector label.
   *
   * @return string
   *   The widget label.
   */
  public function label();

  /**
   * Returns widget selector form.
   *
   * @return array
   *   Form structure.
   */
  public function getForm(array &$form, FormStateInterface &$form_state);

  /**
   * Returns the widget that is currently selected.
   *
   * @return \Drupal\entity_browser\WidgetInterface
   *   Currently selected widget.
   */
  public function getCurrentWidget();

  /**
   * Sets the current widget.
   *
   * @param WidgetInterface $widget
   *   Widget to set as the current widget.
   */
  public function setCurrentWidget(WidgetInterface $widget);

  /**
   * Validates form.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function validate(array &$form, FormStateInterface $form_state);

  /**
   * Submits form.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function submit(array &$form, FormStateInterface $form_state);

}
