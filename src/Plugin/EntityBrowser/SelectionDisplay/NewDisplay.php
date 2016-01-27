<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay\NewDisplay.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
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
    $form['#attached']['library'][] = 'entity_browser/new_display';

    $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);
    $ids = array_keys($selected_entities);

    $hidden_id = Html::getUniqueId('edit-selected-target-id');
    $details_id = Html::getUniqueId('edit-selected');

    $form += [
      '#id' => $details_id,
      '#type' => 'details',
      '#open' => !empty($ids),
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        // We need to repeat ID here as it is otherwise skipped when rendering.
        '#attributes' => ['id' => $hidden_id],
        '#default_value' => $ids,
        // #ajax is officially not supported for hidden elements but if we
        // specify event manually it works.
        '#ajax' => [
          'callback' => [get_class($this), 'updateWidgetCallback'],
          'wrapper' => $details_id,
          'event' => 'entity_browser_selected_updated',
        ],
      ],
    ];

    $form['current'] = [
      '#theme_wrappers' => ['container'],
      '#attributes' => ['class' => ['selected-entities-list']],
      'items' => array_map(
        function($id) use ($selected_entities, $details_id) {
          $entity = $selected_entities[$id];
          return [
            '#theme_wrappers' => ['container'],
            '#attributes' => [
              'class' => ['selected-item-container'],
              'data-entity-id' => $entity->id()
            ],
            'display' => ['#markup' => $entity->label()],
            'remove_button' => [
              '#type' => 'submit',
              '#value' => $this->t('Remove'),
              '#ajax' => [
                'callback' => [get_class($this), 'updateWidgetCallback'],
                'wrapper' => $details_id,
              ],
              '#submit' => [[get_class($this), 'removeItemSubmit']],
              '#name' => 'remove_' . $id,
              '#attributes' => ['data-entity-id' => $id]
            ],
          ];
        },
        $ids
      ),
    ];
    $form['use_selected'] = array(
      '#type' => 'submit',
      '#value' => t('Use selected'),
      '#name' => 'use_selected',
    );

    return $form;
  }

  /**
   * Submit callback for remove buttons.
   */
  public static function removeItemSubmit(&$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#attributes']['data-entity-id'])) {
      $id = $triggering_element['#attributes']['data-entity-id'];
      $parents = array_slice($triggering_element['#parents'], 0, -4);
      $array_parents = array_slice($triggering_element['#array_parents'], 0, -4);

      // Find and remove correct entity.
      $values = explode(' ', $form_state->getValue(array_merge($parents, ['target_id'])));
      $values = array_filter(
        $values,
        function($item) use ($id) { return $item != $id; }
      );
      $values = implode(' ', $values);

      // Set new value for this widget.
      $target_id_element = &NestedArray::getValue($form, array_merge($array_parents, ['target_id']));
      $form_state->setValueForElement($target_id_element, $values);
      NestedArray::setValue($form_state->getUserInput(), $target_id_element['#parents'], $values);

      // Rebuild form.
      $form_state->setRebuild();
    }
  }

  /**
   * AJAX form callback.
   */
  public static function updateWidgetCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    // AJAX requests can be triggered by hidden "target_id" element when entities
    // are added or by one of the "Remove" buttons. Depending on that we need to
    // figure out where root of the widget is in the form structure and use this
    // information to return correct part of the form.
    if (!empty($trigger['#ajax']['event']) && $trigger['#ajax']['event'] == 'entity_browser_selected_updated') {
      $parents = array_slice($trigger['#array_parents'], 0, -2);
    }
    elseif ($trigger['#type'] == 'submit' && strpos($trigger['#name'], '_remove_')) {
      $parents = array_slice($trigger['#array_parents'], 0, -4);
    }

    return NestedArray::getValue($form, $parents);
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
