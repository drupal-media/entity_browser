<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Controllers\StandalonePage.
 */

namespace Drupal\entity_browser\Controllers;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Standalone entity browser page.
 */
class StandalonePage extends ControllerBase {

  /**
   * Test implementation of standalone entity browser page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The standalone entity browser page.
   */
  public function page(Request $request) {
    // @TODO Implement.
    return array();
  }

}
