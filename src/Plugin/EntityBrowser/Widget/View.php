<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\View.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\entity_browser\WidgetBase;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "view",
 *   label = @Translation("View"),
 *   description = @Translation("Uses a view to provide entity listing in a browser's widget.")
 * )
 */
class View extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'view' => NULL,
      'view_display' => NULL,
    ) + parent::defaultConfiguration();
  }

  /**drupal
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    // TODO - do we need better error handling for view and view_display (in case
    // either of those is nonexistent or display not of correct type)?
    $storage = &$form_state->getStorage();
    if (empty($storage['widget_view']) || $form_state->isRebuilding()) {
      $storage['widget_view'] = $this->entityManager
        ->getStorage('view')
        ->load($this->configuration['view'])
        ->getExecutable();
    }

    if (!empty($this->configuration['arguments'])) {
      if (!empty($aditional_widget_parameters['path_parts'])) {
        $arguments = [];
        // Map configuration arguments with original path parts.
        foreach ($this->configuration['arguments'] as $argument) {
          $arguments[] = isset($aditional_widget_parameters['path_parts'][$argument]) ? $aditional_widget_parameters['path_parts'][$argument] : '';
        }
        $storage['widget_view']->setArguments(array_values($arguments));
      }
    }

    $form['view'] = $storage['widget_view']->executeDisplay($this->configuration['view_display']);
    if (empty($storage['widget_view']->field['entity_browser_select'])) {
      return [
        // TODO - link to view admin page if allowed to.
        '#markup' => t('Entity browser select form field not found on a view. Go fix it!'),
      ];
    }

    // When rebuilding makes no sense to keep checkboxes that were previously
    // selected.
    if (!empty($form['view']['entity_browser_select']) && $form_state->isRebuilding()) {
      foreach (Element::children($form['view']['entity_browser_select']) as $child) {
        $form['view']['entity_browser_select'][$child]['#process'][] = ['\Drupal\entity_browser\Plugin\EntityBrowser\Widget\View', 'processCheckbox'];
        $form['view']['entity_browser_select'][$child]['#process'][] = ['\Drupal\Core\Render\Element\Checkbox', 'processAjaxForm'];
        $form['view']['entity_browser_select'][$child]['#process'][] = ['\Drupal\Core\Render\Element\Checkbox', 'processGroup'];
        // We need to unset the #value or the form builder will not set it.
        unset($form['view']['entity_browser_select'][$child]['#value']);
      }
    }

    return $form;
  }

  /**
   * Sets the #checked property when rebuilding form.
   *
   * Every time when we rebuild we want all checkboxes to be unchecked.
   *
   * @see \Drupal\Core\Render\Element\Checkbox::processCheckbox()
   */
  public static function processCheckbox(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#checked'] = FALSE;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $selected_rows = array_keys(array_filter($form_state->getValue('entity_browser_select')));
    $entities = [];
    $storage = $form_state->getStorage();
    foreach ($selected_rows as $row) {
      $entities[] = $storage['widget_view']->result[$row]->_entity;
    }

    $this->selectEntities($entities);
  }

}
