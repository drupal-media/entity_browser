/**
 * @file entity_browser.entity_reference.js
 *
 * Defines the behavior of the entity reference widget that utilizes entity
 * browser.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.entityBrowserEntityReference = {};

  /**
   * Reacts on "entities selected" event.
   *
   * @param event
   *   Event object.
   * @param entities
   *   Array of selected entities.
   */
  Drupal.entityBrowserEntityReference.selectionCompleted = function(event, uuid, entities) {
    // Update value form element with new entity IDs.
    var entity_ids = $(this).parent().parent().find('input[type*=hidden]').val();
    if (entity_ids.length != 0) {
      entity_ids = entity_ids + ' ';
    }

    entity_ids = entity_ids + $.map(entities, function(item) {return item[0]}).join(' ');
    $(this).parent().parent().find('input[type*=hidden]').val(entity_ids);
    $(this).parent().parent().find('input[type*=hidden]').trigger('entity_browser_value_updated');
  }

}(jQuery, Drupal, drupalSettings));
