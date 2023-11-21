/**
 * UL TrustArc Module JS functions.
 * @Version: 1.1.0
 * @Last-Updated: Oct 11, 2023
 */

(function ($, trustarcConfig) {

  // TrustArc cookies.
  let prefs_cookie, location_cookie;

  // Get a cookie value (by the cookie name).
  function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return '';
  }

  // Check cookie values.
  function checkCookieVals() {

    // Get cookie values:
    location_cookie = getCookie('notice_location');
    prefs_cookie = getCookie('notice_gdpr_prefs');

    // If user is in Russia:
    if (location_cookie == 'ru') {
      if ($('#consent_blackbar').length) {
        $('#consent_blackbar').slideUp('fast');
      }
      return;
    }

    // Else if there are cookie preferences.
    else if (prefs_cookie) {
      if (prefs_cookie == '0:') {
        return;
      }
      else {
        rewriteJSfiles();
      }
    }

    // Else, there are no cookie prefs.
    else {
      setTimeout(checkCookieVals, 3500);
    }
  }

  // Rewrite JS files based upon cookie consent.
  function rewriteJSfiles() {
    let clean_prefs_cookie = prefs_cookie.substring(2).replace(/\:$/, '');
    let prefs_arr = clean_prefs_cookie.split(',');

    if (prefs_arr) {
      setTimeout(function () {
        $('.trustecm').each(function (index) {
          let cat = $(this).data('cookiecategory').toString();
          if (prefs_arr.includes(cat)) {
            if (!$(this).hasClass('trustecm_done')) {
              let file_src = $(this).attr('src');
              let file_add = document.createElement('script');
              file_add.setAttribute('type', 'text/javascript');
              file_add.setAttribute('src', file_src);
              file_add.setAttribute('class', 'trustecm_' + cat);
              document.getElementsByTagName('head')[0].appendChild(file_add);
              $(this).addClass('trustecm_done');
            }
          }
        });
      }, 500);
    }
  }

  // Run functions after DOM has loaded:
  window.addEventListener('DOMContentLoaded', function () {
    checkCookieVals();
  });

  // Add empty div in footer menu for "cookie preference" link:
  if ($('.footer__menu').length) {
    $('.footer__menu').append('<div id="teconsent"></div>');
  }

})(jQuery, drupalSettings.trustarc);
