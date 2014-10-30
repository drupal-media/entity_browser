<?php

/**
 * @file
 * Definition of Drupal\entity_browser\Events\RegisterJSCallbacks.
 */

namespace Drupal\entity_browser\Events;

/**
 * Collects "selection completed" JS callbacks.
 */
class RegisterJSCallbacks extends EventBase {

  /**
   * JS callbacks.
   *
   * @var array
   */
  protected $callbacks = [];

  /**
   * Adds callback.
   *
   * @param string $callback
   *   Callback name.
   */
  public function registerCallback($callback) {
    $this->callbacks[] = $callback;
  }

  /**
   * Gets callbacks.
   *
   * @return array
   *   List of callbacks.
   */
  public function getCallbacks() {
    return $this->callbacks;
  }
}
