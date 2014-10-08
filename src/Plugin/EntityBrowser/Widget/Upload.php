<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\Upload.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Component\Plugin\PluginBase;
use Drupal\entity_browser\EntityBrowserWidgetInterface;
use Drupal\entity_browser\WidgetBase;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "upload",
 *   label = @Translation("Upload"),
 *   description = @Translation("Adds an upload field browser's widget.")
 * )
 */
class Upload extends WidgetBase {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * Plugin weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getForm() {
    // TODO - Implement.
    $element['upload'] = array(
      '#name' => 'files[' . implode('_', $element['#parents']) . ']',
      '#type' => 'file',
      '#title' => t('Choose a file'),
      '#title_display' => 'invisible',
      '#size' => 22,
      '#theme_wrappers' => array(),
      '#weight' => 0,
    );

    return $element;
  }

}
