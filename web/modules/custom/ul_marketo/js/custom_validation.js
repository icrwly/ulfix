/**
 * Custom Validation:
 * LAST UPDATED: Jan 11, 2023.
 * VERSION: 1.0.1
 * REQUIREMENTS: jQuery and jQuery Validate Plugin
 */

let jQvalidate;

(function ($, drplMkto) {

  const mktoform_id = '#mktoForm_' + drplMkto.form_id;

  jQvalidate = $(mktoform_id).validate({
    ignore: ".ignore",
    invalidHandler: function(event, validator) {
      var errors = validator.numberOfInvalids();
      if (errors) {
        // Show only first error:
        validator.errorList = [validator.errorList[0]];
        return false;
      }
      return true;
    },
    rules: {
      hiddenRecaptcha: {
        required: function() {
          if (grecaptcha.getResponse( $('#mkto_recaptcha').attr('data-widget-id') ) == '') {
            return true;
          } else {
            return false;
          }
        }
      }
    },
  });

})(jQuery, drupalSettings.marketo);
