<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Form\Browser.
 */

namespace Drupal\entity_browser\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\entity_browser\Ajax\EntityBrowserDialogUpdate;
use Drupal\views\Views;

/**
 * Provides an image dialog for text editors.
 */
class EntityBrowserDialog extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_dialog';
  }

  /**
   * {@inheritdoc}
   *
   * @param string $library
   *   The library that is used by default. @todo implement it
   */
  public function buildForm(array $form, array &$form_state, $library = NULL) {
    $view_name = 'test_browser';
    $display_name = 'entity_reference_1';
    $match = NULL;
    $match_operator = 'CONTAINS';
    $limit = 0;

    $view = Views::getView($view_name);
    if (!$view || !$view->access($display_name)) {
      $form['library']['#markup'] = t('The reference view %view_name cannot be found.', array('%view_name' => $view_name));
    }
    else {
      $view->setDisplay($display_name);

      // Pass options to the display handler to make them available later.
      $options = array(
        'match' => $match,
        'match_operator' => $match_operator,
        'limit' => $limit,
        'ids' => NULL,
      );
      $view->displayHandlers->get($display_name)->setOption('entity_reference_options', $options);
      $result = $view->executeDisplay($display_name);
      if ($result) {
        foreach ($view->result as $row) {
          $entity = $row->_entity;
          $return[$entity->id()] = $entity->label();
        }
        $form['library'] = array(
          '#type' => 'checkboxes',
          '#options' => $return,
        );
        $form['field_info'] = array(
          '#type' => 'value',
          '#value' => array(
            'field_name' => $this->getRequest()->get('field_name'),
            'field_column' => $this->getRequest()->get('field_column'),
            'entity_type' => $entity->getEntityType()->id(),
          ),
        );
      }
    }

    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['choose'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Choose'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => array($this, 'submitForm'),
        'event' => 'click',
      ),
    );
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => array($this, 'submitForm'),
        'event' => 'click',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $response = new AjaxResponse();

    if (FALSE) {
      $status_messages = array('#theme' => 'status_messages');
      $output = drupal_render($form);
      $output = '<div>' . drupal_render($status_messages) . $output . '</div>';
      $response->addCommand(new HtmlCommand('#editor-image-dialog-form', $output));
    }
    else {
      if ($form_state['values']['op'] == $this->t('Choose')) {
        $values = $form_state['values']['field_info'];
        $values['items'] = array_keys(array_filter($form_state['values']['library']));
        $response->addCommand(new EntityBrowserDialogUpdate($values));
      }
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

}
