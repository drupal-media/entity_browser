/**
 * @file entity_browser.common.js
 *
 * Common helper functions used by various parts of entity browser.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.entityBrowser = {};

  /**
   * Reacts on "entities selected" event.
   *
   * @param event
   *   Event object.
   * @param uuid
   *   Entity browser UUID.
   * @param entities
   *   Array of selected entities.
   */
  Drupal.entityBrowser.selectionCompleted = function(event, uuid, entities) {
    // Update value form element with new entity IDs.
    var entity_ids = $(this).parent().parent().find('input[type*=hidden]').val();
    if (entity_ids.length != 0) {
      entity_ids = entity_ids + ' ';
    }

    entity_ids = entity_ids + $.map(entities, function(item) {return item[0]}).join(' ');
    $(this).parent().parent().find('input[type*=hidden]').val(entity_ids);
    $(this).parent().parent().find('input[type*=hidden]').trigger('entity_browser_value_updated');
  };

  /**
   * Reacts on "entities selected" event.
   *
   * @param element
   *   Element to bind on.
   * @param callbacks
   *   List of callbacks.
   * @param event_name
   *   Name of event to bind to.
   */
  Drupal.entityBrowser.registerJsCallbacks = function(element, callbacks, event_name) {
    // JS callbacks are registred as strings. We need to split their names and
    // find actual functions.
    for (var i = 0; i < callbacks.length; i++) {
      var callback = callbacks[i].split('.');
      var fn = window;

      for (var j = 0; j < callback.length; j++) {
        fn = fn[callback[j]];
      }

      if (typeof fn === 'function') {
        $(element).bind(event_name, fn);
      }
    }
  }

}(jQuery, Drupal, drupalSettings));


