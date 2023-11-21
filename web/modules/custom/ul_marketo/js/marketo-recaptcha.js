/**
 * Marketo reCaptcha JS.
 */

// This var is Global in scope.
var CaptchaCallback;

(function ($, drupalSettings) {
  (function(drplMkto) {

    // Not for gated form.
    if (drplMkto.mkto_bundle == 'gated_content_form') {
      return;
    }

    // The reCaptcha callback function:
    CaptchaCallback = function() {
      $('.g-recaptcha').each(function(index, el) {
        var widgetId = grecaptcha.render(el, {
          'sitekey' : $(el).attr('data-sitekey'),
          'theme' : 'light',
        });
        $(this).attr('data-widget-id', widgetId);
      });
    };

    // Get the reCaptcha JS.
    var url = 'https://www.recaptcha.net/recaptcha/api.js?hl=' + drplMkto.form_language + '&onload=CaptchaCallback&render=explicit';
    $.getScript(url, function() {
      if($('.mktoButtonRow').length){
        $('.mktoButtonRow').prepend( $('.recapWrap') );
      }
      else {
        waitForElm('.mktoButtonRow').then(function(elm){
          $('.mktoButtonRow').prepend( $('.recapWrap') );
        });
      }
    });

  })(drupalSettings.marketo);
})(jQuery, drupalSettings);
