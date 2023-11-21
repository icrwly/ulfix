/**
 * @file
 * Functionality to add back link for specific pages.
 */

 (function ($, window, Drupal, drupalSettings) {

    // Helper function: Create a back button.
    function _addBackLink() {
      var referrers = [
        'contact-us'
      ];
  
      if (document.referrer != '') {

        // The referrer path will be the last element in the split array.
        var referrer = document.referrer.split('/');
        referrer = referrer.pop();
     
        // Remove any query parameters from referrer path.
        referrer = referrer.split('?')[0];

        if (referrers.indexOf(referrer) != -1) {
          if(drupalSettings.drupal_lang){
            var link = '<a class="back-link" href="javascript:void(0)" onclick="history.back()" >' + drupalSettings['back_link'][drupalSettings.drupal_lang] + '</a>';
            $('body.path-sales-inquiries .section--marketo .paragraph-gutter, form.web-to-case-form').prepend(link);
          }
        }
      }
      
    }

    Drupal.behaviors.backLink = {
      attach: function attach(context, settings) {
        $(once('back-link', $('body'))).each(function() {
            _addBackLink();
        });
      }
    }
  })(jQuery, window, Drupal, drupalSettings);
  