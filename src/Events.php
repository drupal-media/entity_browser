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

  /**
   * The DONE event occurs when selection process is done. While it can be emitted
   * by any part of the system that will usually be done by selection display plugin.
   *
   * @var string
   */
  const DONE = 'entity_browser.done';

}
