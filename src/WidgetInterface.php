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
   * Returns the widget id.
   *
   * @return string
   *   The widget id.
   */
  public function id();

  /**
   * Returns the widget UUID.
   *
   * @return string
   *   The widget UUID.
   */
  public function uuid();

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
   * @param array $aditional_widget_parameters
   *   Additional parameters that we want to pass to the widget.
   *
   * @return array
   *   Form structure.
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters);

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
   * Run widget validators.
   *
   * @param array $entities
   *   Array of entity ids to validate.
   * @param array $validators
   *   Array of additional widget validator ids.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface
   *   A list of constraint violations. If the list is empty, validation
   *   succeeded.
   */
  public function runWidgetValidators(array $entities, $validators = []);

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
