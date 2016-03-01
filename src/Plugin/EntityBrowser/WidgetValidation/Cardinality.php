<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetValidation\Cardinality.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetValidation;

use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\entity_browser\WidgetValidationBase;

/**
 * Validates that the widget returns the appropriate number of elements.
 *
 * @EntityBrowserWidgetValidation(
 *   id = "cardinality",
 *   label = @Translation("Not empty validator"),
 *   constraint = "Count"
 * )
 */
class Cardinality extends WidgetValidationBase {
  /**
   * {@inheritdoc}
   */
  public function validate(array $entities, $options = []) {
    $data_definition = ListDataDefinition::create('integer');
    $plugin_definition = $this->getPluginDefinition();
    $data_definition->addConstraint($plugin_definition['constraint'], $options);

    $ids = [];
    foreach ($entities as $entity) {
      $ids[] = $entity->id();
    }

    $typed_data = \Drupal::typedDataManager()->create($data_definition, $ids);
    return $typed_data->validate();
  }
}
