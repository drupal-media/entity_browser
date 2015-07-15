/**
 * @file entity_browser.entity_reference_single.js
 *
 * Defines the behavior of the entity reference widget that utilizes entity
 * browser (single entity version).
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.entityBrowserEntityReferenceSingle = {};

  /**
   * Reacts on "entities selected" event.
   *
   * @param event
   *   Event object.
   * @param entities
   *   Array of selected entities.
   */
  Drupal.entityBrowserEntityReferenceSingle.selectionCompleted = function(event, uuid, entities) {
    // Update value form element with new entity IDs.
    var new_entity_id = entities[0][0];
    var hidden_input = $(this).parent().parent().find('input[type*=hidden]');
    $(hidden_input).val(new_entity_id);
    $(hidden_input).trigger('entity_browser_value_updated');
  };

}(jQuery, Drupal, drupalSettings));
