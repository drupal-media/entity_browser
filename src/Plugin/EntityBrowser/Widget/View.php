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

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    // TODO - do we need better error handling for view and view_display (in case
    // either of those is nonexistent or display not of correct type)?
    $storage = &$form_state->getStorage();
    if (empty($storage['view']) || $form_state->isRebuilding()) {
      $storage['view'] = $this->entityManager
        ->getStorage('view')
        ->load($this->configuration['view'])
        ->getExecutable();
    }

    if (!empty($this->configuration['arguments'])) {
      if (!empty($aditional_widget_parameters['path_parts'])) {
        // Compare values from configuration with path parts.
        $arguments = array_intersect_key($aditional_widget_parameters['path_parts'], array_flip($this->configuration['arguments']));
        $storage['view']->setArguments(array_values($arguments));
      }
    }

    $form['view'] = $storage['view']->executeDisplay($this->configuration['view_display']);
    if (empty($storage['view']->field['entity_browser_select'])) {
      return [
        // TODO - link to view admin page if allowed to.
        '#markup' => t('Entity browser select form field not found on a view. Go fix it!'),
      ];
    }

    // When rebuilding makes no sense to keep checkboxes that were previously
    // selected.
    if (!empty($form['view']['entity_browser_select']) && $form_state->isRebuilding()) {
      foreach (Element::children($form['view']['entity_browser_select']) as $child) {
        $form['view']['entity_browser_select'][$child]['#value'] = 0;
      }
    }

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
