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
 * Alter the information provided in \Drupal\entity_browser\Annotation\EntityBrowserWidget.
 *
 * @param $widgets
 *   The array of widget plugins, keyed on the machine-readable name.
 */
function hook_entity_browser_widget_info_alter(&$widgets) {
  $widgets['view_widget']['label'] = t('Views FTW!');
}

/**
 * Alter the information provided in \Drupal\entity_browser\Annotation\EntityBrowserSelectionDisplay.
 *
 * @param $widgets
 *   The array of selection display plugins, keyed on the machine-readable name.
 */
function hook_entity_browser_selection_display_info_alter(&$selection_displays) {
  $selection_displays['no_selection']['label'] = t('Nothing!');
}

/**
 * Alter the information provided in \Drupal\entity_browser\Annotation\EntityBrowserWidgetSelector.
 *
 * @param $widget_selectors
 *   The array of widget selector plugins, keyed on the machine-readable name.
 */
function hook_entity_browser_widget_selector_info_alter(&$widgets) {
  $widgets['tab_selector']['label'] = t('Tabs are for winners');
}



/**
 * @} End of "addtogroup hooks".
 */
