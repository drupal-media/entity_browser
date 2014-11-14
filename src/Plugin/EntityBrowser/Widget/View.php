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
  public function getForm(array &$original_form, FormStateInterface $form_state) {
    $storage = &$form_state->getStorage();
    if (empty($storage['view']) || $form_state->isRebuilding()) {
      $storage['view'] = \Drupal::service('entity.manager')
        ->getStorage('view')
        ->load('nodes_for_selection')
        ->getExecutable();
    }
    $form['view'] = $storage['view']->executeDisplay('entity_browser_1');

    // When rebuilding makes no sense to keep checkboxes that were previously
    // selected.
    if ($form_state->isRebuilding()) {
      foreach (Element::children($form['view']['entity_browser_select']) as $child) {
        $form['view']['entity_browser_select'][$child]['#value'] = 0;
      }
    }

    if (empty($storage['view']->field['entity_browser_select'])) {
      return [
        // TODO - link to view admin page if allowed to.
        '#markup' => t('Entity browser select form field not found on a view. Go fix it!'),
      ];
    }

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => t('Select'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $selected_rows = array_keys(array_filter($form_state->getValue('entity_browser_select')));
    $entities = [];
    $storage = $form_state->getStorage();
    foreach ($selected_rows as $row) {
      $entities[] = $storage['view']->result[$row]->_entity;
    }

    $this->selectEntities($entities);
  }

}
