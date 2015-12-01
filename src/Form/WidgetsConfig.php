<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Form\WidgetsConfig.
 */

namespace Drupal\entity_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class WidgetsConfig extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_widgets_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $form_state->getTemporaryValue('wizard')['entity_browser'];

    /** @var \Drupal\entity_browser\WidgetInterface $widget */
    foreach ($entity_browser->getWidgets() as $widget) {
      $widget_form = [
        '#type' => 'fieldset',
        '#title' => $widget->label(),
        '#tree' => TRUE,
      ];
      $widget_form = $widget->buildConfigurationForm($widget_form, $form_state);
      $form[$widget->id()] = $widget_form;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $form_state->getTemporaryValue('wizard')['entity_browser'];
    /** @var \Drupal\entity_browser\WidgetInterface $widget */
    foreach ($entity_browser->getWidgets() as $widget) {
      $widget->validateConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $form_state->getTemporaryValue('wizard')['entity_browser'];
    /** @var \Drupal\entity_browser\WidgetInterface $widget */
    foreach ($entity_browser->getWidgets() as $widget) {
      $widget->submitConfigurationForm($form, $form_state);
    }
  }

}
