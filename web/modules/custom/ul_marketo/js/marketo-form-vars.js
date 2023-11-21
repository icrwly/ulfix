/**
 * Marketo Form Vars JS.
 * Pass values to the JS vars needed for the form:
 */

let optinlang, form_language, optinfile, mkto_form_id;

(function ($, drupalSettings) {

  Drupal.behaviors.mktoFormVars = {

    attach: function (context, settings) {

      (function(drplMkto) {

        // Opt-in Lang.
        optinlang = drplMkto.form_language;
        // Form Lang.
        form_language = drplMkto.form_language;
        // Optin File.
        optinfile = drplMkto.optin_file;
        // Form ID.
        mkto_form_id = drplMkto.form_id;

      })(drupalSettings.marketo);
    }
  }
})(jQuery, drupalSettings);

/**
 * Helper function: Wait for an element to exist
 * using the MutationObserver api.
 */
 function waitForElm(selector) {
  return new Promise(resolve => {
      if (document.querySelector(selector)) {
          return resolve(document.querySelector(selector));
      }

      const observer = new MutationObserver(mutations => {
          if (document.querySelector(selector)) {
              resolve(document.querySelector(selector));
              observer.disconnect();
          }
      });

      observer.observe(document.body, {
          childList: true,
          subtree: true
      });
  });
}

/**
 * Helper function: Remove personal info from form
 * submitted values.
 */
function cleanFormVals(obj) {

  if(typeof obj !== 'object') return {};

  delete obj.customerComments;
  delete obj.oPSPreferredLanguage;
  delete obj.FirstName;
  delete obj.LastName;
  delete obj.Company;
  delete obj.Email;
  delete obj.Phone;
  delete obj.Department;
  delete obj.Title;

  return obj;
}
