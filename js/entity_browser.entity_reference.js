/**
 * @file entity_browser.entity_reference.js
 *
 * Defines the behavior of the entity reference widget that utilizes entity
 * browser.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Registers behaviours related to entity reference field widget.
   */
  Drupal.behaviors.entityBrowserEntityReference = {
    attach: function (context) {

      // Entity ID display is not retained on form validation fail due to the
      // nature of form api. Lets set is here (read from hidden form element).
      // This is a temporary solution until we have https://www.drupal.org/node/2366241.
      $(context).find('.field-widget-entity-browser-entity-reference').each(function () {
        $(this).find('div.current-markup').html($(this).find('input[type*=hidden]').val());
      });
    }

  };

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
  }

}(jQuery, Drupal, drupalSettings));
