/**
 * JS stuffs for Entity browser.
 */
(function (Drupal, $) {

  "use strict";

  /**
   * Make browser button functional.
   */
  Drupal.behaviors.entity_browser = {
    attach: function (context) {
      $('.entity_browser-open', context).click(function(e) {
        e.preventDefault();
        $(this).parent().find('input').val('1');
      });
    }
  };

  /**
   * Command to save the contents of an editor-provided modal.
   *
   * This command does not close the open modal. It should be followed by a call
   * to Drupal.AjaxCommands.prototype.closeDialog.
   */
  Drupal.AjaxCommands.prototype.entityBrowserDialogUpdate = function (ajax, response, status) {
    var result = response.values;
    $('input[name="' + result.field_name + '[' + result.field_column + ']"]').val(result.items.join(','));
  };


}(Drupal, jQuery));
