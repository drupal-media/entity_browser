<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Form\DisplayConfig.
 */

namespace Drupal\entity_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\EntityBrowserInterface;

class DisplayConfig extends PluginConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_display_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin(EntityBrowserInterface $entity_browser) {
    return $entity_browser->getDisplay();
  }

}
