/**
 * @file
 * Functionality to redirect after login.
 **/

 (function ($, window, Drupal) {

    // Login Redirect.
    Drupal.behaviors.loginRedirect = {
      attach: function attach(context, settings) {

        var title = jQuery('title').html();
        if (title == 'Access denied | Guidelines') {
          // Get the login button
          var login_button = $('div.landing__content__wrap a.button:eq(0)');
          var href = login_button.attr('href').trim();
          var parts = href.split('?destination=');
          var destination =  parts[0] + '?destination=' + location.pathname;
          login_button.attr('href',destination);
        }
      }
    }
  })(jQuery, window, Drupal);
  