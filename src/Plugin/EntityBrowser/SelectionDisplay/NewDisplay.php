<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay\NewDisplay.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\SelectionDisplayBase;

/**
 * Show current selection and delivers selected entities.
 *
 * @EntityBrowserSelectionDisplay(
 *   id = "new_display",
 *   label = @Translation("New selection display"),
 *   description = @Translation("Show current selection display and delivers selected entities.")
 * )
 */
class NewDisplay extends SelectionDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'view' => NULL,
      'view_display' => NULL,
    ) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state) {

    $form = [];

    $storage = &$form_state->getStorage();

    //$selected_entities = $storage['entity_browser']['selected_entities'];
    $selected_entities = ['en1', 'en2', 'en3', 'en4'];

    $form['selected'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'selected'],
    ];
    foreach ($selected_entities as $key => $value) {
      $form['selected']['element'][$key] = [
        '#type' => 'label',
        '$value' => t('Entity'),
        '#title' => $value
      ];
    }

    $form['use_selected'] = array(
      '#type' => 'submit',
      '#value' => t('Use selection'),
      '#name' => 'use_selected',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'use_selected') {
      $this->selectionDone($form_state);
    }
  }

}
