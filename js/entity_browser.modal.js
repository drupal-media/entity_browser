/**
 * @file entity_browser.modal.js
 *
 * Defines the behavior of the entity browser's modal display.
 */
(function ($, Drupal, drupalSettings) {
	
  "use strict";
  
  Drupal.AjaxCommands.prototype.select_entities = function (ajax, response, status) {
    var uuid = drupalSettings.entity_browser.modal.uuid;
    $('a[data-uuid="' + uuid + '"]').trigger('entities-selected', [uuid, response.entities]).unbind('entities-selected');
  };


  /**
   * Registers behaviours related to modal display.
   */
  Drupal.behaviors.entityBrowserModal = {
    attach: function (context) {
      for (var i = 0; i < drupalSettings.entity_browser.modal.js_callbacks.length; i++) {
        // get the callback
        var callback = drupalSettings.entity_browser.modal.js_callbacks[i].split('.');
        var fn = window;

        for (var j = 0; j < callback.length; j++) {
          fn = fn[callback[j]];
        }

        if (typeof fn === 'function') {
          $('a[data-uuid="' + drupalSettings.entity_browser.modal.uuid + '"]').bind('entities-selected', fn);
        }
      }
    }
  }
}(jQuery, Drupal, drupalSettings));