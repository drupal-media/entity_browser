<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Form\WidgetSelectorConfig.
 */

namespace Drupal\entity_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\EntityBrowserInterface;

class WidgetSelectorConfig extends PluginConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_browser_widget_selector_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin(EntityBrowserInterface $entity_browser) {
    return $entity_browser->getWidgetSelector();
  }

}
