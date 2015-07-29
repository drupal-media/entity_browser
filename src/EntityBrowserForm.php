<?php

/**
 * @file
 * Contains \Drupal\entity_browser\EntityBrowserForm.
 */

namespace Drupal\entity_browser;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\DisplayAjaxInterface;

/**
 * The entity browser form.
 */
class EntityBrowserForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_' . $this->entity->id() . '_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $this->entity;
    $form['selected_entities'] = array(
      '#type' => 'value',
      '#value' => array_map(function(EntityInterface $item) {return $item->id();}, $entity_browser->getSelectedEntities()),
    );

    $form['#browser_parts'] = array(
      'widget_selector' => 'widget_selector',
      'widget' => 'widget',
      'selection_display' => 'selection_display',
    );
    $entity_browser->getWidgetSelector()->setDefaultWidget($entity_browser->getCurrentWidget($form_state));
    $form[$form['#browser_parts']['widget_selector']] = $entity_browser->getWidgetSelector()->getForm($form, $form_state);
    $form[$form['#browser_parts']['widget']] = $entity_browser->getWidgets()->get($entity_browser->getCurrentWidget($form_state))->getForm($form, $form_state, $entity_browser->getAdditionalWidgetParameters());

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => t('Select'),
      ],
    ];

    $form['#attached'] = [
      'library' =>
        [
          'entity_browser/tabs',
        ]
    ];

    $form[$form['#browser_parts']['selection_display']] = $entity_browser->getSelectionDisplay()->getForm($form, $form_state);

    if ($entity_browser->getDisplay() instanceOf DisplayAjaxInterface) {
      $entity_browser->getDisplay()->addAjax($form);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $this->entity;
    $entity_browser->getWidgetSelector()->validate($form, $form_state);
    $entity_browser->getWidgets()->get($entity_browser->getCurrentWidget($form_state))->validate($form, $form_state);
    $entity_browser->getSelectionDisplay()->validate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = $this->entity;
    $original_widget = $entity_browser->getCurrentWidget($form_state);
    if ($new_widget = $entity_browser->getWidgetSelector()->submit($form, $form_state)) {
      $entity_browser->setCurrentWidget($new_widget, $form_state);
    }

    // Only call widget submit if we didn't change the widget.
    if ($original_widget == $entity_browser->getCurrentWidget($form_state)) {
      $entity_browser->getWidgets()->get($entity_browser->getCurrentWidget($form_state))->submit($form[$form['#browser_parts']['widget']], $form, $form_state);
      $entity_browser->getSelectionDisplay()->submit($form, $form_state);
    }

    // Save the selected entities to the form state.
    $form_state->set('selected_entities', $entity_browser->getSelectedEntities());

    if (!$entity_browser->isSelectionCompleted()) {
      $form_state->setRebuild();
    }
    else {
      $entity_browser->getDisplay()->selectionCompleted($entity_browser->getSelectedEntities());
    }
  }

}
