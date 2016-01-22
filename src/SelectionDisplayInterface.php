<?php

/**
 * @file
 * Contains \Drupal\entity_browser\SelectionDisplayInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for entity browser selection displays.
 */
interface SelectionDisplayInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Returns the selection display label.
   *
   * @return string
   *   The selection display label.
   */
  public function label();

  /**
   * Returns selection display form.
   *
   * @param array $original_form
   *   Entire form built up to this point. Form elements for selection display
   *   should generally not be added directly to it but returned from function
   *   as a separated unit.
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
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function submit(array &$form, FormStateInterface $form_state);

}
