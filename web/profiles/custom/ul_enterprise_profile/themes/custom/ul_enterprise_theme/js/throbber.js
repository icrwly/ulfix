/**
 * @file
 * Replaced Drupal cores ajax throbber(s)
 * See: https://www.drupal.org/node/2974681
 *
 */
(function ($, Drupal) {
  Drupal.theme.ajaxProgressThrobber = function () {
    return '<div class="loading-container">' +
           '<div class="spinner-wrap">' +
           '<div class="ajax-spinner spinner"></div>' + 
           '</div>' +
           '<div class="ajax-spinner__label spinner-text">' + Drupal.t('Please&nbsp;wait&hellip;', {}, {
             context: "Loading text for Drupal cores Ajax throbber (inline)"
            }) + "</div></div>";
  };
  
  Drupal.theme.ajaxProgressIndicatorFullscreen = function () {
    return '<div class="loading-container">' +
           '<div class="spinner-wrap">' +
           '<div class="ajax-spinner spinner"></div>' + 
           '</div>' +
           '<div class="ajax-spinner__label spinner-text">' + Drupal.t('Please&nbsp;wait&hellip;', {}, {
             context: "Loading text for Drupal cores Ajax throbber (inline)"
            }) + "</div></div>";
  };
    // You can also customize only throbber message:
    // Drupal.theme.ajaxProgressMessage = message => '<div class="my-message">' + message + '</div>';
})(jQuery, Drupal);