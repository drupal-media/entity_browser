<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay\NewDisplay.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\SelectionDisplay;

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
    $form['#attached']['library'][] = 'entity_browser/entity_reference';

    $selected_entities = &$form_state->getStorage()['entity_browser']['selected_entities'];
    $ids = array_keys($selected_entities);

    $form['current'] = [
      '#theme_wrappers' => ['container'],
      '#attributes' => ['class' => ['entities-list']],
      'items' => array_map(
        function($id) use ($selected_entities) {
          $entity = $selected_entities[$id];
          return [
            '#theme_wrappers' => ['container'],
            '#attributes' => [
              'class' => ['item-container'],
              'data-entity-id' => $entity->id()
            ],
            'display' => ['#markup' => $entity->label()],
            'remove_button' => [
              '#type' => 'submit',
              '#value' => $this->t('Remove'),
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
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'use_selected') {
      $this->selectionDone($form_state);
    }
  }

}
