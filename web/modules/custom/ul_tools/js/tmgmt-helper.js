/**
 * @file
 * This JS is to help the `tmgmt` module in the Admin
 * review translation pages. It will keep the Mkto
 * form names and display view modes intact!
 **/

(function($){
  'use strict';
  Drupal.behaviors.translation_helper = {
    attach: function(context){
      // If translation table:
      if($('table.tmgmt-ui-review').length){
        // Loop through textareas:
        $(".form-textarea").each(function(){
          // Get the ID:
          var src = $(this).attr('id');
          // We only want to look at the Source items:
          if (src.includes('-source')){
            // The marketo forms (and view modes)
            // that we do not want to translate:
            var forms = [
              'full',
              'card',
              'contact_form_configurable',
              'default',
              'email_form',
              'event_form',
              'gated_content_form',
              'generic_form',
              'mkto_pref_ctr',
              ];
            // If the value of the current item
            // is a marketo form:
            if(forms.indexOf(this.value) > -1){
              // Get the ID for the matching translation:
              var trg = '#' + src.replace('-source', '-translation');
              // And pass the un-translated Marketo form
              // to the translation field - unchanged.
              $(trg).val(this.value);
            }
          }
        });
      }
    }
  };
})(jQuery);
