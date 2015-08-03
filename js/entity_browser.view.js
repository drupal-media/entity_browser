/**
 * @file entity_browser.view.js
 *
 * Defines the behavior of the entity browser's view widget.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Registers behaviours related to view widget.
   */
  Drupal.behaviors.entityBrowserView = {
    attach: function (context) {
      // Make sure the pager "buttons" trigger a rebuild of the form properly
      $('.entity-browser-form .pager a').on('click', function(e) {
        e.preventDefault();
        var form = $('.entity-browser-form');
        // Redirect form using the query arguments from the pager
        form.attr('action', $(this).attr('href'));
        form.children('[name=filter]').click();
      });
    }
  }

}(jQuery, Drupal, drupalSettings));
