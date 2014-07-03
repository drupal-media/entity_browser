<?php

/**
 * @file
 * Hooks related to entity browser and it's plugins.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the information provided in \Drupal\entity_browser\Annotation\EntityBrowserDisplay.
 *
 * @param $displays
 *   The array of display plugins, keyed on the machine-readable name.
 */
function hook_entity_browser_display_info_alter(&$displays) {
  $displays['modal_display']['label'] = t('Superb fancy stuff!');
}

/**
 * Alter the information provided in \Drupal\entity_browser\Annotation\EntityBrowserTab.
 *
 * @param $tabs
 *   The array of tab plugins, keyed on the machine-readable name.
 */
function hook_entity_browser_tab_info_alter(&$displays) {
  $displays['view_tab']['label'] = t('Views FTW!');
}

/**
 * @} End of "addtogroup hooks".
 */
