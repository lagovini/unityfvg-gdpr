/**
 * @file
 * Attaches behaviors for the Clientside Validation jQuery module.
 */

(function ($) {

  'use strict';

  $(document).once('webform_cvjquery').on('cv-jquery-validate-options-update', function (event, options) {
    options.errorElement = 'strong';
    options.showErrors = function (errorMap, errorList) {
      // Show errors using defaultShowErrors().
      this.defaultShowErrors();

      // Add '.form-item--error-message' class to all errors.
      $(this.currentForm).find('strong.error').addClass('form-item--error-message');

      // Move all radios, checkbox, and datelist errors to parent container.
      $(this.currentForm).find('.form-checkboxes, .form-radios, .form-type-datelist .container-inline, .form-type-tel').each(function () {
        var $container = $(this);
        var $errorMessages = $container.find('strong.error.form-item--error-message');
        $container.append($errorMessages);
      });
    };
  });

})(jQuery);
