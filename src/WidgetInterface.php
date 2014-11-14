<?php

/**
 * @file
 * Contains \Drupal\entity_browser\WidgetInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for entity browser widgets.
 */
interface WidgetInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns the widget label.
   *
   * @return string
   *   The widget label.
   */
  public function label();

  /**
   * Returns the widget's weight.
   *
   * @return int
   *   Widget's weight.
   */
  public function getWeight();

  /**
   * Sets the widget's weight.
   *
   * @param int $weight
   *   New plugin weight.
   *
   * @return \Drupal\entity_browser\WidgetInterface
   *   This object.
   */
  public function setWeight($weight);

  /**
   * Returns widget form.
   *
   * @param array $original_form
   *   Entire form bult up to this point. Form elements for widget should generally
   *   not be added directly to it but returned from funciton as a separated
   *   unit.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return array
   *   Form structure.
   */
  public function getForm(array &$original_form, FormStateInterface $form_state);

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
   * @param array $element
   *   Widget part of the form.
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state);

}
