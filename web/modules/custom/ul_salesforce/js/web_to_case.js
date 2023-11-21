/**
 * @file
 * Functionality for the Web to Case Forms.
 * @Last Updated: May 17, 2023
 * @Version: v1.1.1
 */

(function ($, window, Drupal) {

  // Helper function: Get the scroll offset to ensure JS
  // scrolls user to the location of the error:
  function getScrollOffset() {
    var offset = 0;

    // Nav Toolbar.
    if ($('nav#toolbar-bar').length == 1) {
      offset += parseInt($('nav#toolbar-bar').css('height'));
    }

    // Admin Toolbar.
    if ($('div#toolbar-item-administration-tray').length == 1) {
      offset += parseInt($('div#toolbar-item-administration-tray').css('height'));
    }

    // Covid Banner.
    if ($('div.covid-banner').length == 1) {
      offset += parseInt($('div.covid-banner').css('height'));
    }

    // Global header.
    if ($('header.global-header').length == 1) {
      offset += parseInt($('header.global-header').css('height'));
    }

    // Global nav.
    if ($('#ul-global-nav').length == 1) {
      offset += parseInt($('#ul-global-nav').css('height'));
    }

    return offset;
  }

  // Remove the custom-select class from drop-down menu.
  Drupal.behaviors.webToCase = {
    attach: function attach(context, settings) {
      $(once('web-to-case-name', $('.web-to-case')))
        .each(function() {
          $('.web-to-case .custom-select').removeClass('custom-select');
        });
    }
  }

  // Customize HTML5 validation behavior on web-to-case forms
  // using the jQuery validate plugin.
  Drupal.behaviors.webToCaseValidation = {
    attach: function attach(context, settings) {

      const validator = $("#ul-salesforce-contactus-form").validate({
        focusInvalid: false,
        ignore: ".ignore",
        highlight: function(element) {
          // Intentionally left blank.
        },
        messages: {
          email: "( * ) example@yourdomain.com",
        },
        showErrors: function(errorMap, errorList) {
          if (errorList.length > 0) {
            // Save only the first error.
            this.errorList = [errorList[0]];
            if (this.errorList[0].element.name == 'hiddenRecaptcha') {
              $('.g-recaptcha iframe').css("border", "solid 2px #ca0123");
            }
            else {
              this.defaultShowErrors();
            }
          }
        },
        invalidHandler: function(form, validator) {
          if (!validator.numberOfInvalids())
            return;

          var e = $(validator.errorList[0].element);
          var eOffset = e.offset().top;
          var eHeight = e.height();
          var windowHeight = $(window).height();
          var offset;
          var scroll_target;

          if ($(e).attr('name') == 'hiddenRecaptcha') {
            eOffset = $('.g-recaptcha iframe').offset().top;
            eHeight = $('.g-recaptcha iframe').height;
          }

          if (eHeight < windowHeight) {
            offset = eOffset - ((windowHeight / 2) - (eHeight / 2));
          }
          else {
            offset = eOffset;
          }

          scroll_target = offset - getScrollOffset();

          $('html, body').animate({
            scrollTop: scroll_target
          }, 0);
        },
        rules: {
          hiddenRecaptcha: {
            required: function () {
              if (grecaptcha.getResponse( $('#sf_recaptcha').attr('data-widget-id') ) == '') {
                return true;
              } else {
                return false;
              }
            }
          }
        },
      });
    }
  }

  // myUL integration: Set two field values when there is the
  // myUL referrer in the query string.
  Drupal.behaviors.myulIntegrate = {
    attach: function attach(context, settings) {
      const urlParams = new URLSearchParams(window.location.search);
      const referrer = urlParams.get('ref');
      if(referrer && referrer.toLowerCase() == 'myul'){
        $('#edit-subject').val('myUL Customer Portal');
        $('#edit-web-inquiry').val('myUL Portal Support');
        $('#edit-web-inquiry option').each(function(){
          if(!$(this).val()){
            $(this).remove();
          }
        });
      }
    }
  }

  // Update the NAME hidden field:
  $('.sfrce_field').change('input', function() {
    var f_name = $('#edit-first-name').val();
    var l_name = $('#edit-last-name').val();
    $('.web-to-case input[name=name]').val(f_name + ' ' + l_name);
  });

  // On form submit:
  $(document).on('submit','#ul-salesforce-contactus-form',function(){
    // Disable submit button.
    $("#form-submit").attr('disabled', true);
    // GA push:
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event': 'Contact Us Form Submit',
    });
  });

})(jQuery, window, Drupal);
