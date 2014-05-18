<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Ajax\EntityBrowserDialogUpdate.
 */

namespace Drupal\entity_browser\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Provides an AJAX command for update field via entity browser dialog.
 *
 * This command is implemented in editor.dialog.js in
 * Drupal.AjaxCommands.prototype.entityBrowserDialogUpdate.
 */
class EntityBrowserDialogUpdate implements CommandInterface {

  /**
   * An array of values that will be passed back to the editor by the dialog.
   *
   * @var string
   */
  protected $values;

  /**
   * Constructs a EntityBrowserDialogUpdate object.
   *
   * @param string $values
   *   The values that should be passed to the form constructor in Drupal.
   */
  public function __construct($values) {
    $this->values = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return array(
      'command' => 'entityBrowserDialogUpdate',
      'values' => $this->values,
    );
  }

}
