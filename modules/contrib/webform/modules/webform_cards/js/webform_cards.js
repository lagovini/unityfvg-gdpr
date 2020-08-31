/**
 * @file
 * JavaScript behaviors for webform cards.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize webform cards.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformCards = {
    attach: function (context) {
      // Determine if the form is the context or it is within the context.
      var $forms = $(context).is('form.webform-submission-form')
        ? $(context)
        : $('form.webform-submission-form', context);

      $forms.once('webform-cards').each(function () {
        // Form.
        var $form = $(this);

        // Options from data-* attributes.
        var options = {
          progressStates: $form[0].hasAttribute('data-progress-states'),
          progressLink: $form[0].hasAttribute('data-progress-link'),
          autoForward: $form[0].hasAttribute('data-auto-forward'),
          previewLink: $form[0].hasAttribute('data-preview-link'),
          confirmation: $form[0].hasAttribute('data-confirmation'),
          track: $form.data('track'),
          toggle: $form[0].hasAttribute('data-toggle'),
          toggleHideLabel: $form.data('toggle-hide-label'),
          toggleShowLabel: $form.data('toggle-show-label'),
          ajaxEffect: $form.data('ajax-effect'),
          ajaxSpeed: $form.data('ajax-speed')
        };

        var currentPage = $form.data('current-page');

        // Progress.
        var $progress = $('.webform-progress');

        // Current card.
        var $currentCardInput = $form.find(':input[name="current_card"]');

        // Cards.
        var $allCards = $form.find('.webform-card');

        // Preview.
        if (!$allCards.length) {
          setPreviewLinks();
          return;
        }

        // Display show/hide all cards link.
        if (options.toggle) {
          setToggle();
        }

        // Server-side validation errors.
        // @see \Drupal\Core\Render\Element\RenderElement::setAttributes
        var $invalidCards = $allCards.filter(':has(.form-item--error-message)');
        if ($invalidCards.length) {
          // Hide progress.
          $form.find('.webform-progress').hide();
          // Show invalid cards and shake'em.
          $invalidCards
            .addClass('webform-card--error')
            .effect('shake', {distance: 10});
          return;
        }

        // Actions, preview, and submit buttons.
        var $formActions = $form.find('.form-actions');
        var $previewButton = $formActions.find('.webform-button--preview');
        var $submitButton = $formActions.find('.webform-button--submit');

        // Previous and next buttons.
        var $previousButton = $form.find('.webform-button--previous');
        var $nextButton = $form.find('.webform-button--next');
        $previousButton.data('default-label', $previousButton.val());
        $nextButton.data('default-label', $nextButton.val());
        $previousButton.on('click', previousButtonClickEventHandler).show();
        $nextButton.on('click', nextButtonClickEventHandler).show();

        // Auto-forward.
        if (options.autoForward) {
          // Auto-forward on enter.
          $form.find('input')
            .not(':button, :submit, :reset, :image, :file')
            .on('keypress', function (event) {
              if (event.which === 13) {
                autoForwardEventHandler(event);
                // Disable auto submit.
                // @see Drupal.behaviors.webformDisableAutoSubmit
                event.preventDefault();
                return false;
              }
            });

          // Auto-forward on change.
          $form.find('select[data-images]:not([multiple]), input[type="range"].form-webform-rating')
            .on('change', autoForwardEventHandler);

          // Auto-forward radios with label.
          $form.find('input:radio, label[for]')
            .on('mouseup', function (event) {
              var $radio = (event.target.tagName === 'LABEL')
                ? $('#' + $(event.target).attr('for'))
                : $(this);
              if ($radio.is(':radio') && $radio.val() !== '_other_') {
                setTimeout(function () {
                  autoForwardEventHandler(event);
                });
              }
            });
        }

        // Track when cards are hidden/shown via #states conditional logic.
        if (options.progressStates) {
          $(document).on('state:visible state:visible-slide', function stateVisibleEventHandler(e) {
            if ($(e.target).hasClass('webform-card') && $.contains($form[0], e.target)) {
              trackProgress();
            }
          });
        }

        initialize();

        /* ****************************************************************** */
        // Private functions.
        /* ****************************************************************** */

        /**
         * Initialize the active card.
         */
        function initialize() {
          var currentCard = $currentCardInput.val();
          var $activeCard = currentCard ? $allCards.filter('[data-webform-key="' + currentCard + '"]') : [];
          if (!$activeCard.length) {
            $activeCard = $allCards.first();
          }
          setActiveCard($activeCard, true);
        }

        /**
         * Set the active card.
         *
         * @param {jQuery} $activeCard
         *   An jQuery object containing the active card.
         * @param {boolean} initialize
         *   Are cards being initialize
         */
        function setActiveCard($activeCard, initialize) {
          if (!$activeCard.length) {
            return;
          }

          // Unset the active card
          $allCards.filter('.webform-card--active').removeClass('webform-card--active');

          // Set the previous and next labels.
          $previousButton.val($activeCard.data('prev-button-label') || $previousButton.data('default-label'));
          $nextButton.val($activeCard.data('next-button-label') || $nextButton.data('default-label'));

          // Show/hide the previous button.
          var hasPrevCard = !!$activeCard.prevAll('.webform-card:not([style*="display: none"])').length;
          $previousButton.toggle(hasPrevCard);

          // Hide/show the next button and submit buttons.
          var hasNextCard = !!$activeCard.nextAll('.webform-card:not([style*="display: none"])').length;
          $previewButton.toggle(!hasNextCard);
          $submitButton.toggle(!hasNextCard);
          $nextButton.toggle(hasNextCard);

          // Activate the card.
          $activeCard.addClass('webform-card--active');

          // Show the active card.
          if (!initialize) {
            applyAjaxEffect($activeCard);
          }

          // Focus the active card's first visible input.
          if (!initialize || $form.hasClass('js-webform-autofocus')) {
            $activeCard.find(':input:visible').first().focus();
          }

          // Set current page.
          $currentCardInput.val($activeCard.data('webform-key'));

          // Track the current page in a form data attribute and the URL.
          trackCurrentPage($activeCard);

          // Track progress.
          trackProgress();
        }

        /**
         * Track the current page in a form data attribute and the URL.
         *
         * @param {jQuery} $activeCard
         *   An jQuery object containing the active card.
         *
         * @see \Drupal\webform\WebformSubmissionForm::form
         * @see Drupal.behaviors.webformWizardTrackPage
         */
        function trackCurrentPage($activeCard) {
          if (!options.track) {
            return;
          }

          var page = (options.track === 'index')
            ? ($allCards.index($activeCard) + 1)
            : $activeCard.data('webform-key');

          // Set form data attribute.
          $form.data('webform-wizard-current-page', page);

          // Set URL
          var url = window.location.toString();
          var regex = /([?&])page=[^?&]+/;
          if (url.match(regex)) {
            url = url.replace(regex, '$1page=' + page);
          }
          else {
            url = url + (url.indexOf('?') !== -1 ? '&page=' : '?page=') + page;
          }
          window.history.replaceState(null, null, url);
        }

        /**
         * Track progress.
         *
         * @see webform/templates/webform-progress.html.twig
         * @see webform/templates/webform-progress-tracker.html.twig
         */
        function trackProgress() {
          // Hide/show cards and update steps.
          var cards = getCardsProgressSteps();
          for (var i = 0; i < cards.length; i++) {
            var card = cards[i];
            var cardAttributeName = '[data-webform-' + card.type + '="' + card.key + '"]';

            var $cardStep = $progress.find(cardAttributeName);

            // Set card and page step.
            $cardStep.find('[data-webform-progress-step]').html(card.step);
            if (card.type === 'page') {
              continue;
            }

            // Hide/show card step.
            $cardStep.toggle(!card.hidden);

            // Set .is-active and .is-complete classes.
            $cardStep.toggleClass('is-active', card.active);
            $cardStep.toggleClass('is-complete', !card.active && card.complete);

            // Set 'Current' and 'Complete' state.
            var $cardState = $cardStep.find('[data-webform-progress-state]');
            $cardState.toggle(card.active || card.complete);
            if (card.active) {
              $cardState.html(Drupal.t('Current'));
            }
            if (card.complete) {
              $cardState.html(Drupal.t('Complete'));
            }

            // Link card step.
            if (options.progressLink) {
              var $links = $cardStep.find('[data-webform-progress-link]');
              $links.data('webform-key', card.key);
              if (card.complete) {
                if ($links.attr('role') !== 'link') {
                  $links
                    .attr({'role': 'link', 'title': card.title, 'aria-label': card.title, 'tabindex': '0'})
                    .click(function () {
                      var $card = $allCards.filter('[data-webform-key="' + $(this).data('webform-key') + '"]');
                      setActiveCard($card);
                    })
                    .keydown(function (event) {
                      if (event.which === 13) {
                        var $card = $allCards.filter('[data-webform-key="' + $(this).data('webform-key') + '"]');
                        setActiveCard($card);
                      }
                    });
                }
              }
              else if ($links.attr('role') === 'link') {
                $links.removeAttr('role title aria-label tabindex')
                  .off('click keydown');
              }
            }
          }

          // Set properties.
          var properties = getCardsProgressProperties();
          for (var property in properties) {
            var attribute = '[data-webform-progress-' + property + ']';
            var value = properties[property];
            $progress.find(attribute).html(value);
          }

          // Set <progress> tag [value] and [max] attributes.
          $progress.find('progress').attr({
            value: properties.index,
            max: properties.total
          });
        }

        /**
         * Set show/hide all cards toggle button.
         */
        function setToggle() {
          var $toggle = $('<button type="button" class="webform-cards-toggle"></button>')
            .html(options.toggleShowLabel)
            .on('click', toggleEventHandler)
            .wrap('<div class="webform-cards-toggle-wrapper"></div>')
            .parent();
          $allCards.eq(0).before($toggle);
        }

        /**
         * Set links to previous pages/cards in preview.
         */
        function setPreviewLinks() {
          if (currentPage !== 'webform_preview' || !$form.find('.webform-preview').length) {
            return;
          }

          var $button = $form.find('.js-webform-wizard-pages-link[data-webform-page="webform_start"]');

          // Link to previous pages in progress steps (aka bar).
          if (options.progressLink) {
            $progress.find('[data-webform-card]').each(function () {
              var $step = $(this);
              var card = $step.data('webform-card');
              var title = $step.attr('title');
              $step
                .find('[data-webform-progress-link]')
                .attr({'role': 'link', 'title': title, 'aria-label': title, 'tabindex': '0'})
                .click(function () {
                  // Set current card.
                  $currentCardInput.val(card);
                  // Click button to return to the 'webform_start' page.
                  $button.click();
                })
                .keydown(function (event) {
                  if (event.which === 13) {
                    $(this).click();
                  }
                });
            });
          }

          // Link to previous pages in preview.
          if (options.previewLink) {
            $form
              .find('.webform-card-edit[data-webform-card]')
              .each(function appendEditButton() {
                var $card = $(this);

                var card = $card.data('webform-card');
                var title = $card.attr('title');

                var $cardButton = $button.clone();
                $cardButton
                  .removeAttr('data-webform-page data-msg-required')
                  .attr('id', $cardButton.attr('id') + '-' + card)
                  .attr('name', $cardButton.attr('name') + '-' + card)
                  .attr('data-drupal-selector', $cardButton.attr('data-drupal-selector') + '-' + card)
                  .attr('title', Drupal.t("Edit '@title'", {'@title': title}).toString())
                  .click(function () {
                    // Set current card.
                    $currentCardInput.val(card);
                    // Click button to return to the 'webform_start' page.
                    $button.click();
                    return false;
                  });
                $card.append($cardButton).show();
              });
          }
        }

        /**
         * Get cards progress properties.
         *
         * Properties include index, total, percentage, and summary.
         *
         * @return {{summary: string, total: number, percentage: string,
         *   index: *}} Cards progress properties.
         */
        function getCardsProgressProperties() {
          var $activeCard = $allCards.filter('.webform-card--active');

          var $visibleCards = $allCards.filter(':not([style*="display: none"])');

          var index = (currentPage === 'webform_preview')
            ? $visibleCards.length + 1
            : $visibleCards.index($activeCard);

          var total = $visibleCards.length
            + ($previewButton.length ? 1 : 0)
            + (options.confirmation ? 1 : 0);

          var percentage = Math.round((index / (total - 1)) * 100);

          var summary = Drupal.t(
            '@index of @total',
            {'@index': index + 1, '@total': total}
          );

          return {
            index: index + 1,
            total: total,
            percentage: percentage + '%',
            summary: summary
          };
        }

        /**
         * Get cards as progress steps.
         *
         * @return {[]}
         *   Cards as progress steps.
         */
        function getCardsProgressSteps() {
          var $activeCard = $allCards.filter('.webform-card--active');
          var activeKey = $activeCard.data('webform-key');

          var cards = [];

          // Append cards.
          var step = 0;
          var isComplete = true;
          $allCards.each(function () {
            var $card = $(this);
            var key = $card.data('webform-key');
            var title = $card.data('title');

            // Set active and complete classes.
            var isActive = (activeKey === key);
            if (isActive) {
              isComplete = false;
            }

            // Hide/show progress based on conditional logic.
            var isHidden = false;
            if (options.progressStates) {
              isHidden = $card.is('[style*="display: none"]');
              if (!isHidden) {
                step++;
              }
            }
            else {
              step++;
            }

            cards.push({
              type: 'card',
              key: key,
              title: title,
              step: isHidden ? null : step,
              hidden: isHidden,
              active: isActive,
              complete: isComplete
            });
          });

          // Append preview and confirmation pages.
          $(['webform_preview', 'webform_confirmation']).each(function () {
            var $progressStep = $form.find('[data-webform-progress-steps] [data-webform-page="' + this + '"]');
            if ($progressStep.length) {
              step++;
              cards.push({
                type: 'page',
                key: this,
                step: step
              });
            }
          });
          return cards;
        }

        /**
         * Apply Ajax effect to elements.
         *
         * @param {jQuery} $elements
         *   An jQuery object containing elements to be displayed.
         */
        function applyAjaxEffect($elements) {
          switch (options.ajaxEffect) {
            case 'fade':
              $elements.hide().fadeIn(options.ajaxSpeed);
              break;

            case 'slide':
              $elements.hide().slideDown(options.ajaxSpeed);
              break;
          }
        }

        /**********************************************************************/
        // Event handlers.
        /**********************************************************************/

        /**
         * Toggle event handler.
         *
         * @param {jQuery.Event} event
         *   The event triggered.
         */
        function toggleEventHandler(event) {
          if ($form.hasClass('webform-cards-toggle-show')) {
            $form.removeClass('webform-cards-toggle-show');
            $(this)
              .attr('title', options.toggleShowLabel)
              .html(options.toggleShowLabel);
            var $activeCard = $allCards.filter('.webform-card--active');
            setActiveCard($activeCard);
          }
          else {
            $form.addClass('webform-cards-toggle-show');
            $(this)
              .attr('title', options.toggleHideLabel)
              .html(options.toggleHideLabel);
            var $visibleCards = $allCards.filter(':not([style*="display: none"])');
            applyAjaxEffect($visibleCards);
            $nextButton.hide();
            $previousButton.hide();
            $previewButton.show();
            $submitButton.show();
          }
        }

        /**
         * Previous button event handler.
         *
         * @param {jQuery.Event} event
         *   The event triggered.
         */
        function previousButtonClickEventHandler(event) {
          // Get previous visible card (not "display: none").
          var $previousCard = $allCards.filter('.webform-card--active')
            .prevAll('.webform-card:not([style*="display: none"])')
            .first();
          setActiveCard($previousCard);
          // Prevent the button's default behavior.
          event.preventDefault();
        }

        /**
         * Next button event handler.
         *
         * @param {jQuery.Event} event
         *   The event triggered.
         */
        function nextButtonClickEventHandler(event) {
          var validator = $form.validate();
          if (!$form.valid()) {
            // Focus first invalid input.
            validator.focusInvalid();
            // Shake the invalid card.
            var $activeCard = $allCards.filter('.webform-card--active');
            $activeCard.effect('shake', {distance: 10});
          }
          else {
            // Get next visible card (not "display: none").
            var $nextCard = $allCards.filter('.webform-card--active')
              .nextAll('.webform-card:not([style*="display: none"])')
              .first();
            if ($nextCard.length) {
              setActiveCard($nextCard);
            }
            else if ($previewButton.length) {
              $previewButton.click();
            }
            else {
              $submitButton.click();
            }
          }
          // Prevent the button's default behavior.
          event.preventDefault();
        }

        /**
         * Auto forward event handler.
         *
         * @param {jQuery.Event} event
         *   The event triggered.
         */
        function autoForwardEventHandler(event) {
          if ($form.hasClass('webform-cards-toggle-show')) {
            return;
          }

          var $activeCard = $allCards.filter('.webform-card--active');
          var $allInputs = $activeCard.find('input:visible, select:visible, textarea:visible');
          var $autoForwardInputs = $activeCard.find('input:visible, select:visible');
          if (!$autoForwardInputs.length || $allInputs.length !== $autoForwardInputs.length) {
            return;
          }

          var inputValues = [];
          $autoForwardInputs.each(function () {
            var name = this.name;
            if (!(name in inputValues)) {
              inputValues[name] = false;
            }
            if (this.type === 'radio' && this.checked) {
              inputValues[name] = true;
            }
            else if (this.type === 'select' && this.selectedIndex !== -1) {
              inputValues[name] = true;
            }
            else if (this.type === 'range' && this.value) {
              inputValues[name] = true;
            }
          });

          // Only auto-forward when a single input is visible.
          if (Object.keys(inputValues).length > 1) {
            return;
          }

          var inputHasValue = inputValues.every(function (value) {
            return value;
          });
          if (inputHasValue) {
            $nextButton.click();
          }
        }
      });
    }
  };

})(jQuery, Drupal);
