<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\WidgetValidation\EntityType.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\WidgetValidation;

use Drupal\entity_browser\WidgetValidationBase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validates that the widget returns the appropriate number of elements.
 *
 * @EntityBrowserWidgetValidation(
 *   id = "entity_type",
 *   label = @Translation("Entity type validator"),
 * )
 */
class EntityType extends WidgetValidationBase {
  /**
   * {@inheritdoc}
   */
  public function validate(array $entities, $options = []) {
    $data_definition = \Drupal::typedDataManager()->createDataDefinition('entity_reference');
    $plugin_definition = $this->getPluginDefinition();
    $data_definition->addConstraint($plugin_definition['constraint'], $options);

    $violations = new ConstraintViolationList([]);
    foreach ($entities as $entity) {
      $validation_result = \Drupal::typedDataManager()->create($data_definition, $entity)->validate();
      $violations->addAll($validation_result);
    }

    return $violations;
  }
}
