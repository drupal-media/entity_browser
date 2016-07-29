/**
 * @file entity_browser.common.js
 *
 * Common helper functions used by various parts of entity browser.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.entityBrowser = {};

  /**
   * Command to refresh an entity_browser_entity_reference field widget.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {object} response
   *   Object holding the server response.
   * @param {string} response.details_id
   *   The ID for the details element.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.entity_browser_value_updated = function (ajax, response, status) {
    $('#' + response.details_id)
      .find('input[type="hidden"][name$="[target_id]"]')
      .trigger('entity_browser_value_updated');
  };

  /**
   * Reacts on "entities selected" event.
   *
   * @param {object} event
   *   Event object.
   * @param {string} uuid
   *   Entity browser UUID.
   * @param {array} entities
   *   Array of selected entities.
   */
  Drupal.entityBrowser.selectionCompleted = function (event, uuid, entities) {
    var added_entities_array = $.map(entities, function (item) {
      return item[2] + ':' + item[0];
    });
    // @todo Use uuid here. But for this to work we need to move eb uuid
    // generation from display to eb directly. When we do this, we can change
    // \Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReferenceBrowserWidget::formElement
    // also.
    // Checking if cardinality is set - assume unlimited.
    var cardinality = isNaN(parseInt(drupalSettings['entity_browser'][uuid]['cardinality'])) ? -1 : parseInt(drupalSettings['entity_browser'][uuid]['cardinality']);

    // Having more elements than cardinality should never happen, because
    // server side authentication should prevent it, but we handle it here
    // anyway.
    if (cardinality !== -1 && added_entities_array.length > cardinality) {
      added_entities_array.splice(0, added_entities_array.length - cardinality);
    }

    // Update value form element with new entity IDs.
    var selector = drupalSettings['entity_browser'][uuid]['selector'] ? $(drupalSettings['entity_browser'][uuid]['selector']) : $(this).parent().parent().find('input[type*=hidden]');
    var entity_ids = selector.val();
    if (entity_ids.length !== 0) {
      entity_ids = Drupal.entityBrowser.updateEntityIds(
        entity_ids,
        cardinality,
        added_entities_array,
        drupalSettings['entity_browser'][uuid]['selectionMode'] === 'prepend'
      );
    }
    else {
      entity_ids = added_entities_array.join(' ');
    }

    selector.val(entity_ids);
    selector.trigger('entity_browser_value_updated');
  };

  /**
   * Updates the list of entities based on existing and added entities.
   *
   * Also considers cardinality.
   *
   * @param {string} entity_ids
   *   List of existing entities as a string, separated by space.
   * @param {int} cardinality
   *   The maximal amount of items the field can store.
   * @param {Array} added_entities_array
   *   The entities that are about to be added to the field.
   * @param {bool} reverse
   *   If true, fresh added elements will appear at the beginning of the list.
   *
   * @returns string
   *   List of entities as a string, separated by space.
   */
  Drupal.entityBrowser.updateEntityIds = function (entity_ids, cardinality, added_entities_array, reverse) {
    var existing_entities_array = entity_ids.split(' ');
    var new_entities = _.difference(added_entities_array, existing_entities_array);

    // We always trim the oldest elements and add the new ones.
    if (cardinality === -1 || existing_entities_array.length + added_entities_array.length <= cardinality) {
      if (reverse) {
        existing_entities_array = new_entities.concat(existing_entities_array);
      }
      else {
        existing_entities_array = _.union(existing_entities_array, added_entities_array);
      }
    }
    else {
      $.each(new_entities, function (index, entity_id) {
        // If maximum amount of references is yet reached, stop here.
        if (cardinality >= existing_entities_array.length) {
          return;
        }

        if (reverse) {
          existing_entities_array.unshift(entity_id);
        }
        else {
          existing_entities_array.push(entity_id);
        }
      });
    }

    return existing_entities_array.join(' ');
  };

  /**
   * Reacts on "entities selected" event.
   *
   * @param {object} element
   *   Element to bind on.
   * @param {array} callbacks
   *   List of callbacks.
   * @param {string} event_name
   *   Name of event to bind to.
   */
  Drupal.entityBrowser.registerJsCallbacks = function (element, callbacks, event_name) {
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
  };

}(jQuery, Drupal, drupalSettings));
