(function ($, Drupal) {
  Drupal.behaviors.webform_gdpr = {
    attach: function (context, settings) {
      // console.log(context);
      // console.log(settings);
      var sedelegale1 = $('#edit-sede-legale-1');
      var sedeLegale = $('#edit-titolare-termsel-riq01-00');
      // console.log('FIRE')
      $('#edit-sede-legale-1', context).once('edit-sede-legale-1').each(function () {
        // Apply the myCustomBehaviour effect to the elements only once.
       sedelegale1.change(function() {
         var tid = sedelegale1.val();
         $('#edit-sede-legale-10').val(tid);
        })
      });
      $('#edit-titolare-termsel-riq01-00', context).once('edit-titolare-termsel-riq01-00').each(function () {
         // Apply the myCustomBehaviour effect to the elements only once.
         sedeLegale.change(function() {
         var tid = sedeLegale.val();
         $('#edit-sedelegale-entsel-riq01-01').val(tid);
         $('#edit-emailrdp-entsel-riq01-02').val(tid);
        })
      });
    }
  };

  Drupal.behaviors.webform_actions = {
    attach: function (context, settings) {
      $('.webform-submission-form', context).once('webform-submission-form').each(function () {
        var myForm = $(this);
        var buttons = myForm.find('#edit-actions');
        var marginActions = $('.main-container').css('margin-left')

        var stepSelected = myForm.find('.webform-progress-bar__page--current');
        if (stepSelected.data('webform-page') === 'page_premessa') {
          buttons.find('.webform-button--draft').hide()
          myForm.addClass('step-0')
        } else {
          myForm.removeClass('step-0')
        }
        // toggleclass fixed on scroll from

        var start = myForm.height() - myForm.offset().top;
        var end   = myForm.height() + 120;
        $(window).scroll(function(){
          console.log('SCROLL', $(window).scrollTop() )
          console.log('offset', myForm.offset().top )
          console.log('height', myForm.height() )
          if($(window).scrollTop() > start && $(window).scrollTop() < end) {
            buttons.addClass('fixed');
            buttons.removeClass('bottom');
            buttons.css('left', $('.main-container').css('margin-left'));
          }
          else if ($(window).scrollTop() > start && $(window).scrollTop() > end) {
            buttons.removeClass('fixed');
            buttons.addClass('bottom');
            buttons.css('left', '-34%');
          }
          else {
            buttons.removeClass('fixed');
            buttons.removeClass('bottom');
            buttons.css('left', '-34%');
          }
        });

        $(window).resize(function(){
          console.log('RESIZE');
          if (buttons.hasClass('fixed')) {
            buttons.css('left', $('.main-container').css('margin-left'));
          }
        });
      });
    }
  };
})(jQuery, Drupal);

