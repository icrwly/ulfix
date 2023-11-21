/**
 * The LATAM language JS functions.
 * Reads/sets 2 cookies:
 * `browser_lang` = browser language
 * `last_lang` = language of the last page they visited
 * If user is on the EN homepage, and either of these
 * cookies is set to ES or PT-BR, they will be redirected.
 *
 * @Last Updated: 3/26/2021
 */

jQuery(document).ready(function() {

    // Acceptable language codes:
    var langs = ["en", "es", "pt-br"];
    // The browser language cookie.
    var browserLang = Cookies.get('browser_lang');
    // If there is a last language cookie?
    var lastLang = Cookies.get('last_lang');
    // Parts of the URL path:
    var currPath = window.location.pathname;
    // Function to get the query string params.
    var getParameterByName = function(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    };
    // Function to update language.
    var updateLang = function() {
        // Parts of the URL path:
        var pathArray = window.location.pathname.split('/');
        // Language code in the URL:
        var langCode = pathArray[1];
        // If language code is OK.
        if (langs.includes(langCode)) {
            // Set the language cookie.
            Cookies.set('last_lang', langCode, { sameSite: 'strict', expires: 7 });
        }
    };
    // Reset the language?
    var langReset = getParameterByName('langReset');

    // If no browser language cookie:
    if (browserLang == undefined) {
        // Browser language setting.
        var ln = window.navigator.language||navigator.browserLanguage;
        // Browser language code.
        var ln_code = ln.substr(0,2);
        // If the language code is 'pt', change to 'pt-br':
        if(ln_code == 'pt'){
            ln_code = 'pt-br';
        }
        // If browser language code is not OK.
        if (!langs.includes(ln_code)) {
            ln_code = false;
        }
        // Set the browser language cookie.
        Cookies.set('browser_lang', ln_code, { sameSite: 'strict', expires: 7 });
        // Reset this value.
        browserLang = ln_code;
    }

    // If User is resetting their language:
    if (langReset){
        updateLang();
    }
    // Else user is not on EN homepage (not resetting):
    else if(currPath != '/en'){
        updateLang();
    }
    // Else, they're on EN homepage (not resetting):
    else {
        // If there is a last lang cookie:
        if (lastLang != undefined) {
            // If the user wants to be on English:
            if (lastLang == 'en') {
                return false;
            }
            // Else, send them to the last language:
            else {
                window.location.href = '/' + lastLang;
            }
        }
        // Else if there is a browser lang cookie:
        else if (browserLang && browserLang != 'en') {
            window.location.href = '/' + browserLang;
        }
        return false;
    }
});
