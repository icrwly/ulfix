/**
 * LATAM GnaaS
 */

(function ($, drupalSettings) {
  var gnaas_lang, contactPage, careersPage;

  // The language:
  gnaas_lang = drupalSettings.path.currentLanguage;
  // The contact us page:
  if(drupalSettings.path.currentLanguage == 'pt-br'){
    contactPage = '/pt-br/contact-us';
  }else if(drupalSettings.path.currentLanguage == 'es'){
    contactPage = '/es/contact-us';
  }else{
    contactPage = '/en/contact-us';
  }
  // The careers page:
  careersPage = 'https://www.ul.com/about/careers';

  // Add the values to the settings array:
  var gnaasSettings = {
    lang: gnaas_lang,
    contact: contactPage,
    careers: careersPage,
    translations: false
  }

  // If Drupal is passing translation URLs:
  if(typeof(drupalSettings.gnaas) !== "undefined" && drupalSettings.gnaas !== false){
    // Update the translations property with
    // array of translations:
    gnaasSettings.translations = {
      'en': drupalSettings.gnaas['en'],
      'pt-br': drupalSettings.gnaas['pt-br'],
      'es': drupalSettings.gnaas['es']
    }
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
          var options = ['/en', '/es', '/pt-br', '/en/home', '/es/home', '/pt-br/home'];
          if (options.includes(href)){
            var newHref = href.replace('/home','') + qstring;
            $(this).attr('href', newHref);
          }
        }
      });
    }, 1000);
  });
})(jQuery, drupalSettings);
