<?php

/**
 * @file
 * Provide views data for entity_browser.module.
 */

/**
* Implements hook_views_data_alter().
*/
function entity_browser_views_data_alter(&$data) {
  $entity_manager = \Drupal::entityManager();
  foreach ($entity_manager->getDefinitions() as $entity_type_name => $entity_type) {
    if (!empty($data[$entity_type->getBaseTable()])) {
      $entity_keys = $entity_type->get('entity_keys');
      $data[$entity_type->getBaseTable()]['entity_browser_select'] = array(
        'title' => t('Entity browser bulk select form'),
        'help' => t('Add a form element that lets you use a view as a base to select entities in entity browser.'),
        'field' => array(
          'id' => 'entity_browser_select',
          'real field' => $entity_keys['id'],
        ),
      );
    }
  }
}
