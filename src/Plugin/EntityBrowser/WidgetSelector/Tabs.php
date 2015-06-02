<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector\Tabs.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetSelector;

use Drupal\entity_browser\WidgetSelectorBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays entity browser widgets as tabs.
 *
 * @EntityBrowserWidgetSelector(
 *   id = "tabs",
 *   label = @Translation("Tabs"),
 *   description = @Translation("Displays entity browser widgets as tabs.")
 * )
 */
class Tabs extends WidgetSelectorBase {

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form = array(), FormStateInterface &$form_state = NULL) {
    // Set a wrapper container for us to replace the form on ajax call.
    $form['#prefix'] = '<div id="entity-browser-form">';
    $form['#suffix'] = '</div>';

    foreach ($this->widget_ids as $id => $label) {
      $element[$label] = array(
        '#type' => 'button',
        '#value' => $label,
        '#disabled' => $this->getDefaultWidget(),
        '#executes_submit_callback' => TRUE,
        '#limit_validation_errors' => array(array($label)),
        '#ajax' => array(
          'callback' => array($this, 'changeWidgetCallback'),
          'wrapper' => 'entity-browser-form',
        ),
      );
    }

    $element['change'] = array(
      '#type' => 'submit',
      '#name' => 'change',
      '#value' => t('Change'),
      '#attributes' => array('class' => array('js-hide')),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    foreach ($this->widget_ids as $id => $label) {
      if ($form_state->hasValue($label)) {
        return $id;
      }
    }
  }

  /**
   * AJAX callback to refresh form.
   *
   * @param array $form
   *   Form.
   * @param FormStateInterface $form_state
   *   Form state object.
   *
   * @return array
   *   Form element to replace.
   */
  public function changeWidgetCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
