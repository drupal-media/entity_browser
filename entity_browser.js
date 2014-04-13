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

}(Drupal, jQuery));
