/**
 * @file entity_browser.multi_step_display.js
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Registers behaviours related to selected entities.
   */
  Drupal.behaviors.entityBrowserMultiStepDisplay = {
    attach: function (context) {
      var $entities = $(context).find('.entities-list');
      $entities.sortable({
        stop: Drupal.entityBrowserMultiStepDisplay.entitiesReordered
      });

      // Add a toggle button for the display of selected entities.
      var $toggle = $('.entity-browser-show-selection');

      function setToggleText() {
        if($entities.css('display') == 'none') {
          $toggle.val(Drupal.t('Show selected'));
        } else {
          $toggle.val(Drupal.t('Hide selected'));
        }
      }

      $toggle.on('click', function (e) {
        e.preventDefault();
        $entities.slideToggle('fast', setToggleText);
      });

      setToggleText();
    }
  };

  Drupal.entityBrowserMultiStepDisplay = {};

  /**
   * Reacts on sorting of the entities.
   *
   * @param {object} event
   *   Event object.
   * @param {object} ui
   *   Object with detailed information about the sort event.
   */
  Drupal.entityBrowserMultiStepDisplay.entitiesReordered = function (event, ui) {
    var items = $(this).find('.item-container');
    for (var i = 0; i < items.length; i++) {
      $(items[i]).find('.weight').val(i);
    }
  };

}(jQuery, Drupal));
