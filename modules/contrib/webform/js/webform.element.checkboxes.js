/**
 * @file
 * JavaScript behaviors for checkboxes.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Adds check all or none checkboxes support.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/project/webform/issues/3068998
   */
  Drupal.behaviors.webformCheckboxesAllorNone = {
    attach: function (context) {
      $('[data-options-all], [data-options-none]', context)
        .once('webform-checkboxes-all-or-none')
        .each(function () {
          var $element = $(this);

          var options_all_value = $element.data('options-all');
          var options_none_value = $element.data('options-none');

          // Get all checkboxes.
          var $checkboxes = $element.find('input[type="checkbox"]');
          // Get all options/checkboxes.
          var $options = $checkboxes
            .not('[value="' + options_all_value + '"]')
            .not('[value="' + options_none_value + '"]');

          // All of the above.
          if (options_all_value) {
            var $options_all = $element
              .find(':checkbox[value="' + options_all_value + '"]');
            $options_all.on('click', function () {
              var checked = this.checked;
              $checkboxes.each(function () {
                if (this.value === options_none_value) {
                  this.checked = false;
                }
                else {
                  this.checked = checked;
                }
              });
            });

            $options.on('click', toggleCheckAllEventHandler);
            toggleCheckAllEventHandler();

            /**
             * Toggle check all checkbox checked state.
             */
            function toggleCheckAllEventHandler() {
              var checked = true;
              $options.each(function () {
                if (!this.checked) {
                  checked = false;
                }
              });
              $options_all[0].checked = checked;
            }
          }

          // None of the above.
          if (options_none_value) {
            var $options_none = $element.find(':checkbox[value="' + options_none_value + '"]');
            $options_none.on('click', toggleCheckNoneEventHandler);
            toggleCheckNoneEventHandler();

            /**
             * Toggle check none checkbox checked state.
             */
            function toggleCheckNoneEventHandler() {
              var checked = $options_none[0].checked;
              $checkboxes.each(function () {
                if (this.value !== options_none_value) {
                  if (checked && this.checked) {
                    this.checked = false;
                    $(this).change();
                  }
                  this.disabled = checked;
                }
              });
            }

          }
        });
    }
  };

  /**
   * Adds HTML5 validation to required checkboxes.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/project/webform/issues/3068998
   */
  Drupal.behaviors.webformCheckboxesRadiosRequired = {
    attach: function (context) {
      $('.js-webform-type-checkboxes.required, .js-webform-type-webform-radios-other.checkboxes', context)
        .once('webform-checkboxes-required')
        .each(function () {
          var $element = $(this);
          var $firstCheckbox = $element
            .find('input[type="checkbox"]')
            .first();

          // Copy clientside_validation.module's message to the checkboxes.
          if ($element.attr('data-msg-required')) {
            $firstCheckbox.attr('data-msg-required', $element.attr('data-msg-required'));
          }

          $element.find('input[type="checkbox"]').on('click', required);
          required();

          function required() {
            var isChecked = $element.find('input[type="checkbox"]:checked');
            if (isChecked.length) {
              $firstCheckbox
                .removeAttr('required')
                .removeAttr('aria-required');
            }
            else {
              $firstCheckbox.attr({
                'required': 'required',
                'aria-required': 'true'
              });
            }
          }
        });
    }
  };

})(jQuery, Drupal);
