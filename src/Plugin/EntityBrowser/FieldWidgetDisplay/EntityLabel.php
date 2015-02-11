<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay\EntityLabel.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\entity_browser\FieldWidgetDisplayInterface;

/**
 * Displays a label of the entity.
 *
 * @EntityBrowserFieldWidgetDisplay(
 *   id = "label",
 *   label = @Translation("Entity label"),
 *   description = @Translation("Displays entity with a label.")
 * )
 */
class EntityLabel extends PluginBase implements FieldWidgetDisplayInterface {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity) {
    return $entity->label();
  }

}
