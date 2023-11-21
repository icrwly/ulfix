/**
 * NOTE: See lines #146 - 155 in the `ul_onetrust.module` file.
 */

// Let One Trust access our jQuery object.
if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') {
  var $ = jQuery;
}

// Instantiate cookie banner.
function OptanonWrapper() {}
