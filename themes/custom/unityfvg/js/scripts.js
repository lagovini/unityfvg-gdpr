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
  Drupal.behaviors.userLogin = {
    attach: function (context, settings) {
      if (context.URL && context.URL.includes("user/")){
        if (!context.URL.includes("?internal")) {
          $("body.path-user nav.tabs ul.tabs--primary.nav.nav-tabs a")
          .each(function()
          {
            this.style.display = 'none';
          });
        }
        else {
          $("body.path-user nav.tabs ul.tabs--primary.nav.nav-tabs a")
          .each(function()
          {
            this.href += '?internal';
          });
        }
      }
    }
  };
})(jQuery, Drupal);

