/**
 * @file
 * Binds jQuery accordions to provided FAQ Fields.
 */

(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.faqfieldAccordion = {
    attach: function (context) {
      if (drupalSettings.faqfield != undefined) {

        // Bind the accordion to any defined faqfield accordion formatter with
        // provided settings.
        for (var selector in drupalSettings.faqfield) {
          $(selector, context).accordion(drupalSettings.faqfield[selector]);
        }

      }
    }
  };

})(jQuery, Drupal, drupalSettings);
