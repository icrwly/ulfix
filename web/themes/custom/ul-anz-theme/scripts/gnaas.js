/**
 * GnaaS functions for Australia, New Zealand site.
 */

(function ($, drupalSettings) {
  // The settings array:
  var gnaasSettings = {
    lang: null,
    contact: '/contact-us',
    careers: 'https://www.ul.com/about/careers',
    translations: false
  }

  // Instantiate GnaaS - except on Campaign Pages:
  if (!$('body').attr('class').includes('campaign-page')) {
    UL_GnaaS({
      env: drupalSettings.gnaas_env,
      //Links:
      careersPageUrl: gnaasSettings.careers,
      contactPageUrl: gnaasSettings.contact,
      //Language:
      lang: gnaasSettings.lang,
      //Translations:
      multisite: gnaasSettings.translations,
    });
  }

  // Modify links in the GnaaS:
  $(window).bind('load', function() {
    setTimeout(function(){
      // Translations:
      $('#ul-global-language-selector li a').each(function(index){
        if($(this).attr('href') == '#'){
          // Disable empty links (translations not available):
          $(this).addClass('disabled');
        }
        else {
          // Add a query string parameter:
          // This will allow users to change languages
          // without being redirected!
          var qstring = '?langReset=1';
          var href = $(this).attr('href');
          var options = ['/en', '/en/home'];
          if (options.includes(href)){
            var newHref = href.replace('/home','') + qstring;
            $(this).attr('href', newHref);
          }
        }
      });
      // Contact Us:
      $('.md-screen-hide a').each(function(){
        if($(this).attr('href') == '/contact-us'){
          $(this).attr('target','_self');
        }
      });
    }, 1000);
  });
})(jQuery, drupalSettings);
