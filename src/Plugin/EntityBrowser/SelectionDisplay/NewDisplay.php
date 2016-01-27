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
    $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);

    $form = [];
    $form['#attached']['library'][] = 'entity_browser/new_display';
    $form['selected'] = [
      '#theme_wrappers' => ['container'],
      '#attributes' => ['class' => ['selected-entities-list']],
    ];
    $form['selected']['weights'] = [
      '#type' => 'hidden',
      '#attributes' => ['class' => ['selected-entities-weights']]
    ];
    foreach ($selected_entities as $id => $entity) {
      $form['selected']['items'][$id] = [
        '#theme_wrappers' => ['container'],
        '#attributes' => [
          'class' => ['selected-item-container'],
          'data-entity-id' => $id
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
   */
  public static function removeItemSubmit(&$form, FormStateInterface $form_state) {
    $id = $form_state->getTriggeringElement()['#attributes']['data-entity-id'];
    $selected_entities = $form_state->get(['entity_browser', 'selected_entities']);
    unset($selected_entities[$id]);
    $form_state->set(['entity_browser', 'selected_entities'], $selected_entities);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    $order = explode(" ", $form_state->getValue('weights'));
    $selected = $form_state->get(['entity_browser', 'selected_entities']);
    $new_order = [];
    if ($form_state->getTriggeringElement()['#name'] == 'use_selected') {
      if(!empty($order)){
        foreach($order as $key => $value) {
          $new_order[$key] = $selected[$value];
        }
        $form_state->set(['entity_browser', 'selected_entities'], $new_order);
      }
      $this->selectionDone($form_state);
    }
  }

}
