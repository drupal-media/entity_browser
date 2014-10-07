<?php

/**
 * @file
 * Contains \Drupal\entity_browser\SelectionDisplayInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for entity browser selection displays.
 */
interface SelectionDisplayInterface extends PluginInspectionInterface {

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
   * @return array
   *   Form structure.
   */
  public function getForm();

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
