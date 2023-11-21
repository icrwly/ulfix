/**
 * @file: "back-to-page/site" banner functions.
 *
 * Creates a banner (link) at the top of the page to
 * return the user to the page/site where they recently
 * visited in the session.
 *
 * There may be more than one banner enabled. But, we
 * only will display one banner at one time, the most
 * recently visited page/site.
 */

(function ($, window) {
  // The Back Button Object.
  var bbtnObj = {};
  // The referring page.
  bbtnObj.referrer = window.document.referrer;
  // The current path.
  bbtnObj.currentPath = window.location.pathname;
  // Abort flag, to help prevent us showing
  // banner on the same page.
  bbtnObj.abort = false;
  // If a match to a banner is found.
  bbtnObj.matchFound = false;

  // First loop through each banner and look for a match.
  $('.wrot-link').once('bbtn_firstloop').each(function(){
    // this div.wrot-link.
    var $e = $(this);
    // The banner/button ID (the parent div's ID).
    var bannerId = $e.parent().attr('id');
    // Return page aliases ("back-to-page").
    var returnPageAliases = $e.data('aliases');
    // Return to site URL ("back-to-site").
    var returnSiteUrl = $e.data('return-site');

    // If "back-to-site":
    if (typeof returnSiteUrl !== "undefined") {
      // If referrer matches.
      if (bbtnObj.referrer.includes(returnSiteUrl)){
        // Save this banner as "most recent".
        $.cookie('bbtn_recent', bannerId, { path: '/;SameSite=Strict' });
      }
    }

    // Else if "back-to-page":
    else if (typeof returnPageAliases !== "undefined"){
      // Loop through the aliases.
      returnPageAliases.forEach(function(alias){
        if(!bbtnObj.matchFound){
          // If it matches the current path.
          if (bbtnObj.currentPath.localeCompare(alias) === 0){
            // Save this banner as "most recent".
            $.cookie('bbtn_recent', bannerId, { path: '/;SameSite=Strict' });
            // A match is found.
            bbtnObj.matchFound = true;
          }
        }
      });
    }
  }); // End: first loop.

  // If a cookie is set:
  if($.cookie('bbtn_recent')){
    // if the abort flag has not been set to True.
    if(!bbtnObj.abort){
      // Loop through a second time to get the one banner to display.
      $('.wrot-link').once('bbtn_secondloop').each(function(){
        // this div.wrot-link.
        var $e = $(this);
        // The banner/button ID (the parent div's ID).
        var bannerId = $e.parent().attr('id');

        // If the cookie matches this banner ID.
        if($.cookie('bbtn_recent') == bannerId){
          // The button text.
          var returnText = $e.data('return-text');
          // Return page aliases ("back-to-page").
          var returnPageAliases = $e.data('aliases');
          // Return to site URL ("back-to-site").
          var returnSiteUrl = $e.data('return-site');

          // Display the "back-to" link:
          if(typeof returnSiteUrl !== "undefined"){
            $e.append('<a href="' + bbtnObj.referrer + '" class="icon icon-caret-left-solid">' + returnText + '</a>');
          }
          else if($.inArray(bbtnObj.currentPath, returnPageAliases) == -1){
            $e.append('<a href="' + returnPageAliases[0] + '" class="icon icon-caret-left-solid">' + returnText + '</a>');
          }

          // Set the abort flag (so we won't display another banner).
          bbtnObj.abort = true;
        }
        // Else, it is not a match.
        else {
          // If not displaying, remove it.
          $('#' + bannerId).remove();
        }
      }); // End: Second loop.
    }
  }
})(jQuery, window);
