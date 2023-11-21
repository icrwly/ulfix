/**
 * Canada GnaaS
 */

(function ($, drupalSettings) {
  // Defaults:
  var gnaasSettings = {
    lang: (drupalSettings.path.currentLanguage == 'fr-ca' ? 'fr' : 'en'),
    contact: (drupalSettings.path.currentLanguage == 'fr-ca' ? 'https://canada.ul.com/fr/locations/' : 'https://canada.ul.com/locations/'),
    careers: 'https://www.ul.com/about/careers',
    translations: false
  }

  // If Drupal is passing translation URLs:
  if(typeof(drupalSettings.gnaas) !== "undefined" && drupalSettings.gnaas !== false){
    gnaasSettings.translations = {
      en: drupalSettings.gnaas['en'],
      fr: drupalSettings.gnaas['fr-ca']
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

  // Disable empty links (translations not available):
  $(window).on('load', function() {
    $('#ul-global-language-selector li a').each(function(index){
      if($(this).attr('href') == '#'){
        $(this).addClass('disabled');
      }
    });
  });
  
})(jQuery, drupalSettings);
