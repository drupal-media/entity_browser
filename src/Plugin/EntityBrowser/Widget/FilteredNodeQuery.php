<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\FilteredNodeQuery.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Uses an EntityQuery to provide a filtered node listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "filtered_node_query",
 *   label = @Translation("Filtered node listing"),
 *   description = @Translation("Provides a node listing filtered by type and title.")
 * )
 */
class FilteredNodeQuery extends WidgetBase {
  /**
   * {inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    $node_types = array('0' => t('- Any -')) + $this->getNodeTypes();

    // Filter by Type and Title.
    $form['filter'] = array(
      '#type' => 'fieldset',
      '#title' => t('Filter by:'),
    );
    $form['filter']['bundle'] = array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => $node_types,
      '#default_value' => 0,
      '#ajax' => array(
        'callback' => 'Drupal\entity_browser\Plugin\EntityBrowser\Widget\FilteredNodeQuery::updateMatchingNodes',
        'wrapper' => 'entity-query-matches',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ),
    );
    $form['filter']['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title contains'),
      '#default_value' => '',
      '#ajax' => array(
        'callback' => 'Drupal\manh_entity_browser\Plugin\EntityBrowser\Widget\FilteredNodeQuery::updateMatchingNodes',
        'wrapper' => 'entity-query-matches',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ),
    );

    // List of nodes.
    $form['entities'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="entity-query-matches">',
      '#suffix' => '</div>',
    );
    $form['entities'] += $this->getMatchingNodesElement($form_state);

    return $form;
  }

  /**
   * Returns array of node types.
   *
   * @return array
   */
  private static function getNodeTypes() {
    $types = array();
    foreach (\Drupal::entityManager()->getBundleInfo('node') as $bundle => $info) {
      $types[$bundle] = $info['label'];
    }
    return $types;
  }

  /**
   * Returns array of nodes matching the filter.
   *
   * @param string $bundle
   * @param string $title_substring
   * @return array
   *   Array of Node objects.
   */
  private static function getMatchingNodes($bundle = NULL, $title_substring = NULL) {
    $nodes = array();
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->pager(25);
    if (!empty($bundle)) {
      $query->condition('type', $bundle);
    }
    if (!empty($title_substring)) {
      $query->condition('title', $title_substring, 'CONTAINS');
    }
    $nids = $query->execute();
    if (!empty($nids)) {
      $nodes = entity_load_multiple('node', $nids);
    }

    return $nodes;
  }

  /**
   * Returns a form element with nodes matching the current filter selections.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   *   Form element.
   */
  private static function getMatchingNodesElement(FormStateInterface $form_state) {
    $node_types = self::getNodeTypes();
    $matching_nodes = self::getMatchingNodes($form_state->getValue('bundle'), $form_state->getValue('title'));
    $header = array(
      'title' => array('data' => t('Title')),
      'type' => array('data' => t('Type')),
    );
    $options = array();
    foreach ($matching_nodes as $node) {
      $options[$node->id()] = array(
        'title' => $node->getTitle(),
        'type' => $node_types[$node->getType()],
      );
    }

    return array(
      'match' => array(
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $options,
        '#empty' => t('No matching items.'),
        '#multiple' => TRUE,
        '#js_select' => FALSE,
      ),
      'pager' => array(
        '#theme' => 'pager',
      ),
    );
  }

  /**
   * Ajax callback for updating matching nodes.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return AjaxResponse
   */
  public static function updateMatchingNodes(array $form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#entity-query-matches', $form['widget']['entities']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $selected_rows = array_keys(array_filter($form_state->getValue('match')));
    $entities = [];
    if (!empty($selected_rows)) {
      $entities = entity_load_multiple('node', $selected_rows);
    }
    // Dispatch entity selection event.
    $this->selectEntities($entities);
  }
}