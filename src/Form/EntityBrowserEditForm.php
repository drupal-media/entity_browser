<?php

/**
 * @file
 * Contains Drupal\entity_browser\Form\EntityBroserEditForm.
 */

namespace Drupal\entity_browser\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class EntityBrowserEditForm
 *
 * Provides the edit form for Entity Browser.
 *
 */
class EntityBrowserEditForm extends EntityBrowserForm {

  /**
   * Returns the actions provided by this form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update Entity Browser');
    return $actions;
  }

}
