/**
 * NAME: CONTACT US JS
 * VERSION: 1.1.2
 * LAST UPDATED: Jul (Hot Fix) 2023
 * REQUIRED: jQuery & Drupal
 */

(function ($, window, Drupal) {

  Drupal.behaviors.contactusPage = {

    attach: function attach(context, settings) {

      // Inquiry Types:
      const inquiryTypes = {
        ulfile: 'Changes to existing UL File',
        feedback: 'General Feedback (Customer Advocacy)',
        customerservice: 'Customer Service',
        invoicing: 'Invoicing',
        myul: 'myUL Portal Support',
        productiQ: 'Product iQ (PiQ) Question',
        sales: 'Sales',
      };

      // Has the empty value been removed
      // from  the Web Inquiries menu:
      let emptied = false;

      /**
       * Helper functions.
       */

      // Move the Salesforce & Mkto forms:
      // Move web-to-case form out of block at the bottom of the page and
      // up into the main page content region.
      // Move the Marketo form out of the sidebar and under the web-to-case
      // form.
      function _moveForms(){
        // Web-to-case.
        $('#ul-salesforce-contactus-form').addClass('web-to-case contactusForm');
        $('#sf_target').replaceWith($('#ul-salesforce-contactus-form'));
        // Mkto.
        $('.section .activeForm').addClass('hidden');
        $('#mkto_target').replaceWith($('.section .activeForm'));
        $('#mkto_intrptr').insertAfter($('.section .activeForm'));
        $('.aside-section .section--marketo').remove();
      }

      // Prep Salesforce form:
      // Inquiry Type select menu gets class: 'fbtf_wrap'.
      // All other inputs and the submit button gets class: 'sfrce_field_wrap'.
      // This is so we can hide them if user selects "Sales" as Inquiry Type
      // and we hide them and show the Marketo form.
      function _prepForm(){
        $('#ul-salesforce-contactus-form .fbtf_select').closest('.js-form-item').addClass('fbtf_wrap');
        $('#ul-salesforce-contactus-form .button-container').addClass('sfrce_field_wrap');
        $('#ul-salesforce-contactus-form .js-form-item').each(function(index) {
          if (!$(this).hasClass('fbtf_wrap')) {
            $(this).addClass('sfrce_field_wrap');
          }
        });
      }

      // Prep page (on load): Get URL hash & toggle forms
      // based on the hash value.
      function _prepPage(){

        if(window.location.hash) {
          _setInqType(window.location.hash.substring(1));
        }

        setTimeout(function(){
          _toggleForms($('#edit-web-inquiry').val());
        }, 1000);
      }

      // Set/change the inquiry type value:
      function _setInqType(hashVal){
        let inqType = '';
        if(inquiryTypes.hasOwnProperty(hashVal)){
          inqType = inquiryTypes[hashVal];
          _removeEmpty();
        }
        $('#edit-web-inquiry').val(inqType);
      }

      // Remove empty option from menu:
      function _removeEmpty(){
        if(emptied){ return; }

        $('#edit-web-inquiry option').each(function(){
          if(!$(this).val()){
            $(this).remove();
            emptied = true;
          }
        });
      }

      // Toggle forms (Salesforce vs Mkto):
      function _toggleForms(inqType){
        var web2case = [
          'ulfile',
          'feedback',
          'customerservice',
          'invoicing',
          'myul',
          'productiQ',
        ];
        var curr = _setHash(inqType);
        var formType = false;

        if(curr){
          if(web2case.includes(curr)){
            formType = 'sf';
          }
          else {
            formType = 'mkto';
          }

          waitForElm('.col-main .activeForm').then(function(elm){
            $('.col-wrap').removeClass('invoicing customerservice feedback sales').addClass(curr);
            $('.sfrce_field').removeAttr('disabled');

            if(formType == 'sf'){
              if($('.sfrce_field_wrap').hasClass('hidden')){
                $('.activeForm').addClass('hidden');
                $('.sfrce_field_wrap').removeClass('hidden');
              }
            }
            else {
              if($('.activeForm').hasClass('hidden')){
                $('.activeForm').removeClass('hidden');
                $('.sfrce_field_wrap').addClass('hidden');
              }
            }

            _uncheckCheckboxes();

            setTimeout(function() {
              ulmkto.remove_styles();
            }, 500);

          });
        }
      }

      // Uncheck GDPR checkboxes:
      function _uncheckCheckboxes(){
        $('#emailOptin, .faux_optin, .pipl_accept').prop('checked', false);
      }

      // Set/change the URL hash value:
      function _setHash(inqType){
        let hashVal = '';
        for (const key in inquiryTypes) {
          if (inquiryTypes.hasOwnProperty(key)) {
            if(inquiryTypes[key] == inqType){
              hashVal = key;
            }
          }
        }
        window.location.hash = hashVal;
        return hashVal;
      }

      // Watch form: Look for changes to inquiry type.
      function _watchForm(){
        // Web Inquiry select menu:
        $('#edit-web-inquiry').on('change', function(e) {
          var selectedVal = this.value;
          _toggleForms(selectedVal);
          if(selectedVal){
            _removeEmpty();
          }
          if ($('.pipl_accept').length) {
            $('.pipl_accept').prop('checked', false);
            $('.faux_optin').prop('checked', false);
            $('#form-submit, .mktoButton').attr('disabled', true);
          }
        });

        // Country select menus:
        $('#edit-web-country').change(function() {
          var selected_cntry = $(this).val();
          $('#edit-web-country option:selected', this).remove();
          $('#edit-web-country option, #Country option').each(function(){
            if(selected_cntry == $(this).val()){
              $(this).attr('selected', 'selected');
            } else {
              $(this).removeAttr('selected');
            }
          });
          if(selected_cntry == 'China' || selected_cntry == 'Viet Nam'){
            $('#form-submit').attr('disabled', true);
          } else {
            $('#form-submit').attr('disabled', false);
          }
          if (typeof ulmkto === 'object' && ulmkto.hasOwnProperty('update_gdpr')){
            ulmkto.update_gdpr(selected_cntry);
          }
        });

        // PIPL Accept:
        $('.web-to-case').on('change', 'input[type="checkbox"]', function() {
          if ($(this).hasClass('pipl_accept') || $(this).hasClass('faux_optin')) {
            if ($('.pipl_accept').length) {
              if ($('.pipl_accept').is(':checked') && $('.faux_optin').is(':checked')) {
                $('#form-submit').attr('disabled', false);
              } else {
                $('#form-submit').attr('disabled', true);
              }
            }
          }
        });
      }

      /**
       * --------------------- *
       * Begin:
       * --------------------- *
       */

      _moveForms();
      _prepForm();
      _prepPage();
      _watchForm();

    }

  }

})(jQuery, window, Drupal);
