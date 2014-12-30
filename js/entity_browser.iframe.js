/**
 * @file entity_browser.iframe.js
 *
 * Defines the behavior of the entity browser's iFrame display.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Registers behaviours related to iFrame display.
   */
  Drupal.behaviors.entityBrowserIFrame = {

    attach: function (context) {
      $(context).find('.entity-browser-handle.entity-browser-iframe').on('click', Drupal.entityBrowserIFrame.linkClick);
    }

  };

  Drupal.entityBrowserIFrame = {};

  /**
   * Handles click on "Select entities" link.
   */
  Drupal.entityBrowserIFrame.linkClick = function() {
    var uuid = $(this).attr('data-uuid');
    var original_path = $(this).attr('data-original-path');
    var iframe = $(
      '<iframe />',
      {
        'src' : drupalSettings['entity_browser']['iframe'][uuid]['src'],
        'width' : drupalSettings['entity_browser']['iframe'][uuid]['width'],
        'height' : drupalSettings['entity_browser']['iframe'][uuid]['height'],
        'data-uuid' : uuid,
        'data-original-path' : original_path
      }
    );

    // JS callbacks are registred as strings. We need to split their names and
    // find actual functions.
    // TODO - move to standalone function as other displays might need the same
    // functionality
    if (drupalSettings.entity_browser.iframe[uuid].js_callbacks || false) {
      for (var i = 0; i < drupalSettings.entity_browser.iframe[uuid].js_callbacks.length; i++) {
        var callback = drupalSettings.entity_browser.iframe[uuid].js_callbacks[i].split('.');
        var fn = window;

        for (var j = 0; j < callback.length; j++) {
          fn = fn[callback[j]];
        }

        if (typeof fn === 'function') {
          $(iframe).bind('entities-selected', fn);
        }
      }
    }

    $(this).parent().append(iframe);
    $(this).hide();
  };


}(jQuery, Drupal, drupalSettings));
