/**
 * @file entity_browser.iframe.js
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

    // Use same string to display entity IDs to the user.
    // TODO - temporary solution. See: https://www.drupal.org/node/2366241 and
    // \Drupal\entity_browser\Plugin\Field\Widget\EntityReference.
    $(this).parent().parent().find('div.current-markup').html(entity_ids);

    // Display "Select entities" link and destroy iFrame.
//    $(this).parent().find('a[data-uuid*=' + uuid + ']').show();
//    $(this).remove();
  }

}(jQuery, Drupal, drupalSettings));
