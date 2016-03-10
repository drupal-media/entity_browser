/**
 * @file entity_browser.modal.js
 *
 * Defines the behavior of the entity browser's modal display.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.AjaxCommands.prototype.select_entities = function (ajax, response, status) {
    var uuid = drupalSettings.entity_browser.modal.uuid;

    $('input[data-uuid="' + uuid + '"]').trigger('entities-selected', [uuid, response.entities])
    .removeClass('entity-browser-processed').unbind('entities-selected');
  };

  /**
   * Registers behaviours related to modal display.
   */
  Drupal.behaviors.entityBrowserModal = {
    attach: function (context) {
      _.each(drupalSettings.entity_browser.modal, function (instance) {
        _.each(instance.js_callbacks, function (callback) {
          // Get the callback.
          callback = callback.split('.');
          var fn = window;

          for (var j = 0; j < callback.length; j++) {
            fn = fn[callback[j]];
          }

          if (typeof fn === 'function') {
            $('input[data-uuid="' + instance.uuid + '"]').not('.entity-browser-processed')
              .bind('entities-selected', fn).addClass('entity-browser-processed');
          }
        });
      });
    }
  };

  Drupal.behaviors.fluidModal = {
    attach: function (context) {

      // on window resize run function
      $(window).resize(function (context) {
        fluidDialog();
      });

      // catch dialog if opened within a viewport smaller than the dialog width
      // run function on all dialog opens
      $(document).on("dialogopen", ".ui-dialog", function (event, ui) {
        fluidDialog();
      });
    }
  };

  var fluidDialog = function fluidDialog() {
    var $visible = $(".ui-dialog:visible");
    // each open dialog
    $visible.each(function () {
      var $this = $(this);
      var dialog = $this.find(".ui-dialog-content").data("ui-dialog");
      // if fluid option == true
      if (dialog.options.fluid) {
        var wWidth = $(window).width();
        // check window width against dialog width
        if (wWidth < (dialog.options.maxWidth + 50)) {
          //if there is a maxWidth, don't allow a bigger size
          dialog.option("width", dialog.options.maxWidth);
        } else {
          // if no maxWidth is defined, make it responsive
          dialog.option("width", '92%');
        }

        var vHeight = $(window).height();
        // check window width against dialog width
        if (vHeight < (dialog.options.maxHeight + 50)) {
          //if there is a maxHeight, don't allow a bigger size
          dialog.option("height", dialog.options.maxHeight);
        } else {
          // if no maxHeight is defined, make it responsive
          dialog.option("height", vHeight-100);

          // Because there is no iframe height 100% in HTML 5, we have to set the height of the iframe as well
          var contentHeight = $this.find('.ui-dialog-content').height() - 20;
          $this.find("iframe").css("height", contentHeight);
        }

        //reposition dialog
        dialog.option("position", dialog.options.position);
      }
    });
  };


}(jQuery, Drupal, drupalSettings));
