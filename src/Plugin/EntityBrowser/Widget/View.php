<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\View.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

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
  public function getForm() {
    // TODO - Implement.
    $element['test'] = array(
      '#type' => 'markup',
      '#markup' => 'View Widget',
    );

    return $element;
  }

}
