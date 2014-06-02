<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\entity_reference\selection\EntityBrowserSelection.
 */

namespace Drupal\entity_browser\Plugin\entity_reference\selection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\entity_reference\Plugin\Type\Selection\SelectionInterface;
use Drupal\views\Views;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "entity_browser",
 *   label = @Translation("Entity browser: Choose from one or more libraries"),
 *   group = "entity_browser",
 *   weight = 0
 * )
 */
class EntityBrowserSelection implements SelectionInterface {

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface
   */
  protected $fieldDefinition;

  /**
   * The entity object, or NULL
   *
   * @var \Drupal\Core\Entity\EntityInterface|null
   */
  protected $entity;

  /**
   * The loaded View object.
   *
   * @var \Drupal\views\ViewExecutable;
   */
  protected $view;

  /**
   * Constructs a View selection handler.
   */
  public function __construct(FieldDefinitionInterface $field_definition, EntityInterface $entity = NULL) {
    $this->fieldDefinition = $field_definition;
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function settingsForm(FieldDefinitionInterface $field_definition) {
    $selection_handler_settings = $field_definition->getSetting('handler_settings') ?: array();
    $entity_browser_settings = !empty($selection_handler_settings['entity_browser']) ? $selection_handler_settings['entity_browser'] : array();
    $displays = Views::getApplicableViews('entity_reference_display');
    // Filter views that list the entity type we want, and group the separate
    // displays by view.
    $entity_type = \Drupal::entityManager()->getDefinition($field_definition->getSetting('target_type'));
    $options = array();
    foreach ($displays as $data) {
      list($view, $display_id) = $data;
      if ($view->storage->get('base_table') == $entity_type->getBaseTable()) {
        $name = $view->storage->get('id');
        $display = $view->storage->get('display');
        $options[$name . ':' . $display_id] = $name . ' - ' . $display[$display_id]['display_title'];
      }
    }

    // The value of the 'view_and_display' select below will need to be split
    // into 'view_name' and 'view_display' in the final submitted values, so
    // we massage the data at validate time on the wrapping element (not
    // ideal).
    $plugin = new static($field_definition);
    $form['entity_browser']['#element_validate'] = array(array($plugin, 'settingsFormValidate'));

    if ($options) {
      $default = !empty($entity_browser_settings['libraries']) ? $entity_browser_settings['libraries'] : NULL;
      $form['entity_browser']['libraries'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Libraries used to select the entities'),
        '#required' => TRUE,
        '#options' => $options,
        '#default_value' => $default,
        '#description' => '<p>' . t('Choose the libraries that select the entities that can be referenced.<br />A library is a view with a display of type "Entity Reference".') . '</p>',
      );

      $default = !empty($entity_browser_settings['arguments']) ? implode(', ', $entity_browser_settings['arguments']) : '';
      $form['entity_browser']['arguments'] = array(
        '#type' => 'textfield',
        '#title' => t('View arguments'),
        '#default_value' => $default,
        '#required' => FALSE,
        '#description' => t('Provide a comma separated list of arguments to pass to the view.'),
      );
    }
    else {
      $form['entity_browser']['no_view_help'] = array(
        '#markup' => '<p>' . t('No eligible libraries were found. <a href="@create">Create a view</a> with an <em>Entity Reference</em> display, or add such a display to an <a href="@existing">existing view</a>.', array(
            '@create' => url('admin/structure/views/add'),
            '@existing' => url('admin/structure/views'),
          )) . '</p>',
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $handler_settings = $this->fieldDefinition->getSetting('handler_settings');
    $display_name = $handler_settings['view']['display_name'];
    $arguments = $handler_settings['view']['arguments'];
    $result = array();
    if ($this->initializeView($match, $match_operator, $limit)) {
      // Get the results.
      $result = $this->view->executeDisplay($display_name, $arguments);
    }

    $return = array();
    if ($result) {
      foreach($this->view->result as $row) {
        $entity = $row->_entity;
        $return[$entity->bundle()][$entity->id()] = $entity->label();
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function countReferenceableEntities($match = NULL, $match_operator = 'CONTAINS') {
    $this->getReferenceableEntities($match, $match_operator);
    return $this->view->pager->getTotalItems();
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    $handler_settings = $this->fieldDefinition->getSetting('handler_settings');
    $display_name = $handler_settings['view']['display_name'];
    $arguments = $handler_settings['view']['arguments'];
    $result = array();
    if ($this->initializeView(NULL, 'CONTAINS', 0, $ids)) {
      // Get the results.
      $entities = $this->view->executeDisplay($display_name, $arguments);
      $result = array_keys($entities);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function validateAutocompleteInput($input, &$element, &$form_state, $form, $strict = TRUE) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function entityQueryAlter(SelectInterface $query) {}

  /**
   * Element validate; Check View is valid.
   */
  public function settingsFormValidate($element, &$form_state, $form) {
    if (empty($element['libraries']['#value'])) {
      \Drupal::formBuilder()->setError($element, $form_state, t('The Entity Browser entity selection mode requires a view.'));
      return;
    }

    // Explode the 'arguments' string into an actual array. Beware, explode()
    // turns an empty string into an array with one empty string. We'll need an
    // empty array instead.
    $arguments_string = trim($element['arguments']['#value']);
    if ($arguments_string === '') {
      $arguments = array();
    }
    else {
      // array_map() is called to trim whitespaces from the arguments.
      $arguments = array_map('trim', explode(',', $arguments_string));
    }

    $value = array('libraries' => $element['libraries']['#value'], 'arguments' => $arguments);
    \Drupal::formBuilder()->setValue($element, $value, $form_state);
  }

}
