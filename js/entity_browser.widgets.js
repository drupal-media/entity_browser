/**
 * @file entity_browser.widgets.js
 *
 * Defines the behavior of the entity browser's config widgets page.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Registers behaviours related to config widgets page.
   */
  Drupal.behaviors.entityBrowserWidgets = {
    attach: function () {
      $('#edit-widget').on('change', function(e) {
        e.preventDefault();
        location.reload(true);
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
