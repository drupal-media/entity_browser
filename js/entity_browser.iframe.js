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
    var iframe = $(
      '<iframe />',
      {
        'src' : drupalSettings['entity_browser']['iframe'][uuid]['src'],
        'width' : drupalSettings['entity_browser']['iframe'][uuid]['width'],
        'height' : drupalSettings['entity_browser']['iframe'][uuid]['height'],
        'data-uuid' : uuid
      }
    );

    $(iframe).bind('entities-selected', Drupal.entityBrowserIFrame.selectionCompleted);
    $(this).parent().append(iframe);
    $(this).hide();
  };

  /**
   * Reacts on "entities selected" event.
   *
   * @param event
   *   Event object.
   * @param entities
   *   Array of selected entities.
   */
  Drupal.entityBrowserIFrame.selectionCompleted = function(event, entities) {
    //TODO - move this to widget-specific JS code.
    var uuid = $(this).attr('data-uuid');
    $(this).parent().find('a[data-uuid*=' + uuid + ']').show();

    // Value
    var current = $(this).parent().parent().find('input[type*=hidden]').val();
    if (current.length != 0) {
      current = current + ' ';
    }

    current = current + $.map(entities, function(item) {return item[0]}).join(' ');
    $(this).parent().parent().find('input[type*=hidden]').val(current);


    // Markup
    var current_a = $(this).parent().parent().find('div.current-markup').html();
    if (current_a.length != 0) {
      current_a = current_a + ', ';
    }

    current_a = current_a + $.map(entities, function(item) {return item[0]}).join(', ');
    $(this).parent().parent().find('div.current-markup').html(current_a);


    $(this).remove();
  }

}(jQuery, Drupal, drupalSettings));
