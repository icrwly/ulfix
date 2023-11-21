(function ($, window) {
  $('#edit-field-private-event-dropdown').on('change', eventDropdownChange);
  function eventDropdownChange(e) {
    if($(this).val() == 'Private') {
      $('#edit-field-shared-metatags-0-advanced-robots-noindex').prop('checked', true);
      $('#edit-field-shared-metatags-0-advanced-robots-nofollow').prop('checked', true);
      $('#edit-index-default-node-settings-0').prop('checked', true);
    } else {
      $('#edit-field-shared-metatags-0-advanced-robots-noindex').prop('checked', false);
      $('#edit-field-shared-metatags-0-advanced-robots-nofollow').prop('checked', false);
      $('#edit-index-default-node-settings-1').prop('checked', true);
    }
  }
})(jQuery, window);
