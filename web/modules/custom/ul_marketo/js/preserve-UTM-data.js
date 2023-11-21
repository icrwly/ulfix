/**
 * NAME: Preserve UTM Data
 * VERSION: 1.0.3
 * LAST UPDATED: Feb 8, 2022
 * REQUIRED: jQuery & jQuery.cookie
 */

// Create object.
let _utm = {};

(function($){
  // UTM params:
  _utm.params_obj = {};

  // Cookie name:
  _utm.cname = 'UTM_params';

  // Cookie path:
  _utm.cpath = '/;SameSite=Strict';

  // Array of UTM keys.
  _utm.keys = [
    'utm_campaign',
    'utm_content',
    'utm_medium',
    'utm_source',
    'utm_term',
    'utm_mktocampaign',
    'utm_mktoadid',
    'keyword',
  ];

  // Get UTM values from the query string:
  function utmGetVals(){
    let item, obj = {};
    const params = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < params.length; i++){
      item = params[i].split('=');
      if(_utm.keys.includes(item[0])){
        obj[item[0]] = item[1];
      }
    }
    return obj;
  }

  // If UTM cookie exists:
  function utmCookieExists(){
    if (typeof $.cookie(_utm.cname) === 'undefined'){
      return false;
    }
    return true;
  }

  // Set a Cookie:
  function utmSetCookie(){
    const str = JSON.stringify(_utm.params_obj);
    $.cookie(_utm.cname, str, { path: _utm.cpath });
  }

  // Pass the UTM values to the form:
  function utmPassCookieValToForm(theForm){
    // If there is no cookie, nothing to pass.
    if (typeof $.cookie(_utm.cname) === 'undefined'){
      return false;
    }

    // Cookie Value (JSON.stringified object).
    // Important: Needs to be parsed.
    const obj = JSON.parse($.cookie(_utm.cname));

    // Pass the values to the form:
    theForm.vals({
      "utmcampaign": obj.utm_campaign,
      "utmcontent": obj.utm_content,
      "utmmedium": obj.utm_medium,
      "utmsource": obj.utm_source,
      "utmterm": obj.utm_term,
      "utmmktocampaign": obj.utm_mktocampaign,
      'utmmktoadid': obj.utm_mktoadid,
      'gAKeyword': obj.keyword,
    });
  }

  /**
   * -------------------------------- *
   * BEGIN:
   * -------------------------------- *
   */

  // If cookie does not exist:
  if(!utmCookieExists()){
    // Get UTM params in the query string:
    _utm.params_obj = utmGetVals();

    // If UTM params object is not empty, set cookie:
    if(!$.isEmptyObject(_utm.params_obj)){
      utmSetCookie();
    }
  }

  // If there is a cookie:
  if(utmCookieExists()){
    // If this function is callable:
    if (typeof waitForElm === "function") {
      // Call function to wait for the form:
      waitForElm('.mktoForm').then(function(elm){
        // If we can access the form object:
        if(typeof mktoForm === 'object'){
          // Pass UTM values to the form:
          utmPassCookieValToForm(mktoForm);
        }
      });
    }
  }

})(jQuery);

// Helper function: Delete the UTM cookie:
// This is called in the Marketo onSuccess callback.
function _deleteUtmCookie(){
  jQuery.removeCookie(_utm.cname, { path: _utm.cpath });
}
