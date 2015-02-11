<?php

/**
 * @file
 * Contains \Drupal\entity_browser\FieldWidgetDisplayInterface.
 */

namespace Drupal\entity_browser;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the interface for entity browser field widget display plugins.
 */
interface FieldWidgetDisplayInterface extends PluginInspectionInterface {

  /**
   * Builds and gets render array for the entity.
   *
   * @param EntityInterface $entity
   *   Entity to be displayed.
   *
   * @return array
   *   Render array that is to be used to display the entity in field widget.
   */
  public function view(EntityInterface $entity);

}
