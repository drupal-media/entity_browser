<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Events.
 */

namespace Drupal\entity_browser;

/**
 * Contains all events thrown by entity browser.
 */
final class Events {

  /**
   * The SELECED event occurs when enities are selected in currently active
   * widget.
   *
   * @var string
   */
  const SELECTED = 'entity_browser.selected';

}
