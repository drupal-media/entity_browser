<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay\MultiStepDisplay.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\SelectionDisplayBase;

/**
 * Show current selection and delivers selected entities.
 *
 * @EntityBrowserSelectionDisplay(
 *   id = "multi_step_display",
 *   label = @Translation("Multi step selection display"),
 *   description = @Translation("Show current selection display and delivers selected entities.")
 * )
 */
class MultiStepDisplay extends SelectionDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state) {
    $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);

    $form = [];
    $form['#attached']['library'][] = 'entity_browser/multi_step_display';
    $form['selected'] = [
      '#theme_wrappers' => ['container'],
      '#attributes' => ['class' => ['selected-entities-list']],
      '#tree' => TRUE
    ];
    foreach ($selected_entities as $id => $entity) {
      $form['selected']['items_'. $entity->id()] = [
        '#theme_wrappers' => ['container'],
        '#attributes' => [
          'class' => ['selected-item-container'],
          'data-entity-id' => $entity->id()
        ],
        'display' => ['#markup' => $entity->label()],
        'remove_button' => [
          '#type' => 'submit',
          '#value' => $this->t('Remove'),
          '#submit' => [[get_class($this), 'removeItemSubmit']],
          '#name' => 'remove_' . $entity->id(),
          '#attributes' => [
            'data-row-id' => $id,
            'data-remove-entity' => 'items_' . $entity->id(),
          ]
        ],
        'weight' => [
          '#type' => 'hidden',
          '#default_value' => $id,
          '#attributes' => ['class' => ['weight']]
        ]
      ];
    }
    $form['use_selected'] = array(
      '#type' => 'submit',
      '#value' => t('Use selected'),
      '#name' => 'use_selected',
    );

    return $form;
  }

  /**
   * Submit callback for remove buttons.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function removeItemSubmit(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getTriggeringElement()['#attributes']['data-row-id'];
    $entity = $form_state->getTriggeringElement()['#attributes']['data-remove-entity'];
    $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);
    $selected = $form_state->getValue('selected');
    unset($selected_entities[$id]);
    unset($selected[$entity]);
    $form_state->set(['entity_browser', 'selected_entities'], $selected_entities);
    // If selected array is not empty we must keep order.
    if (!empty($selected)) {
      $weights = array_column($selected, 'weight');
      $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);
      $ordered = array_combine($weights, $selected_entities);
      ksort($ordered);
      $form_state->set(['entity_browser', 'selected_entities'], $ordered);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    $selected = $form_state->getValue('selected');
    if (!empty($selected)) {
      $weights = array_column($selected, 'weight');
      $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);

      // Selected and selected_entities is not the same when we trigering with select.
      // and we have two parts of array selected_entities, for first we got the weights.
      if ($form_state->getTriggeringElement()['#name'] == 'op') {
        $se1 = array_slice($selected_entities, 0, count($weights));
        $or1 = array_combine($weights, $se1);
        ksort($or1);
        $se2 = array_slice($selected_entities, count($weights));
        $weights_second = [];
        foreach ($se2 as $key => $value) {
          $weights_second[] = max($weights) + $key + 1;
        }
        $or2 = array_combine($weights_second, $se2);
        $ordered = array_merge($or1, $or2);
      // When triger is use_selected than arrays selected and selected_entities is same.
      } else {
        $ordered = array_combine($weights, $selected_entities);
      }

      ksort($ordered);
      $form_state->set(['entity_browser', 'selected_entities'], $ordered);
    }
    if ($form_state->getTriggeringElement()['#name'] == 'use_selected') {
      $this->selectionDone($form_state);
    }
  }

}
