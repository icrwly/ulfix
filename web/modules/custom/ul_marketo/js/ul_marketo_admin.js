/**
 * @file
 * Admin JS Functions.
 */

(function ($, window, Drupal, drupalSettings) {

  // Toggle Language.
  Drupal.behaviors.defaultMarketoLang = {
    attach: function attach(context, settings) {
      $title = $('h3.marketo-select-lang');
      $marketoLang = $('.item-list ul.marketo-lang-ul');
      $title.click(function() {
        $marketoLang.toggle();
        $(this).toggleClass('opened');
      });
    }
  }

  // L2O: Conditional validation for routing values.
  Drupal.behaviors.conditionalValidate = {
    attach: function attach(context, settings) {
      var mkto_validate = {};

      // Clear Mkto Campaign of errors:
      mkto_validate.clear_campaign = function(){
        $('.layout-region--node-secondary label').each(function(){
          let parent_class = $(this).parent().attr('class');
          if(parent_class.includes('mkto-campaign')) {
            $(this).removeClass('has-error');
            $(this).siblings().removeClass('error');
          }
        });
      };
      // Is there a routing value (Last Interest)?
      mkto_validate.routing_vals_exist = function(){
        var last_int = $('[data-drupal-selector*="edit-field-shared-marketo-forms-0-last-interest"]').val();
        if (last_int){
          return true;
        }
        return false;
      };
      // Are there CTAs?
      mkto_validate.mkto_ctas_exist = function(){
        var hdr_cta = $('#edit-field-shared-marketo-link-0').val();
        var rr_enabled = $('#edit-field-shared-rr-checkbox-value').prop('checked');
        var rr_cta = $('#edit-field-shared-rr-marketo-cta-0').val();
        if (hdr_cta && '_none' != hdr_cta){
          return true;
        }
        else if (rr_enabled && '_none' != rr_cta){
          return true;
        }
        return false;
      };
      // Are there any Mkto paragraphs?
      mkto_validate.mkto_pgraphs_exists = function(){
        var mkto_paragraphs = [
          'Marketo Modal',
          'Marketo Interrupter',
          'Campaign Form',
          'Fifty/Fifty Text and Form',
        ];
        var counter = 0;
        $('.paragraph-type-title').each(function(){
          if (mkto_paragraphs.includes($(this).text())){
            counter++;
          }
        });
        if (counter){
          return true;
        } else {
          return false;
        }
      };
      // Is there a Gated Form?
      mkto_validate.gated_form_exists = function(){
        var counter = 0;
        $('.ief-entity-table tbody tr').each(function() {
          $('td').each(function(){
            if ($(this).attr('class') == 'inline-entity-form-marketo_form-type'){
              if($(this).find('div').text() == 'Gated Content Form'){
                counter++;
              }
            }
          });
        });
        $('.details-wrapper fieldset').each(function() {
          $('.form-wrapper').each(function(){
            if ($(this).hasClass('field--name-field-gated-asset-title')){
              counter++;
            }
          });
        });
        if(counter){
          return true;
        }
        return false;
      }
      // Show error message:
      mkto_validate.show_error = function(){
        var error_mssg = '<div role="contentinfo" aria-label="Error message" class="messages messages--error"> \
            <div role="alert"> \
            <h2 class="visually-hidden">Error message</h2> \
            Please specify the Last Interest. \
            </div> \
            </div>';
        $('main.page-content').prepend(error_mssg);
        $('#edit-field-shared-marketo-forms-0').attr('open', 'open');
        $('#edit-field-shared-marketo-forms-0 .form-select').css('border', '3px solid red');
        $([document.documentElement, document.body]).animate({ scrollTop: 0 }, 1000);
        return false;
      };

      // Do this as page loads:
      mkto_validate.clear_campaign();

      // Do this as client-side validation:
      if($("#edit-field-shared-marketo-forms-0").length > 0){
        // On Submit:
        $(once('node_submit', $('.node-form'))).submit(function(event){
          // Reset:
          mkto_validate.routing_vals_required = false;
          // Clear error mssg:
          $('.messages--error').remove();
          // If the node has routing values:
          if(mkto_validate.routing_vals_exist()){
            return;
          }
          // If there are CTAs (hdr or RR):
          else if(mkto_validate.mkto_ctas_exist()){
            mkto_validate.routing_vals_required = true;
          }
          // If there are any Mkto Paragraphs:
          else if(mkto_validate.mkto_pgraphs_exists()){
            mkto_validate.routing_vals_required = true;
          }
          // If there is a Gated Content Form:
          else if(mkto_validate.gated_form_exists()){
            mkto_validate.routing_vals_required = true;
          }
          // ================================== //
          // ERROR: Routing Values are required:
          // ================================== //
          if(mkto_validate.routing_vals_required){
            mkto_validate.show_error();
            mkto_validate.clear_campaign();
            event.preventDefault();
          }
        });
      }
    }
  }

  // Regional Pages validation.
  Drupal.behaviors.regionalPagesValidate = {
    attach: function attach(context, settings) {
      var rgn = {};
      rgn.modal_field = '#edit-field-regional-page-content-wrapper .dropbutton-widget';
      rgn.cust_field = '#edit-field-shared-marketo-custom-wrapper fieldset';

      // Is this a regional landing page?
      rgn.is_rgn_page = function(){
        if($('form').attr('id') && $('form').attr('id').indexOf('regional-pages') > 0){
          return true;
        }
        return false;
      };

      // Is there a Mkto Hdr CTA?
      rgn.mkto_cta_exists = function(){
        var mtkoCta = $('#edit-field-shared-marketo-link-0').find(":selected").val();
        if(mtkoCta == 'generic_form'){
          return true;
        }
        return false;
      };

      // Is there a Marketo Modal paragraph?
      rgn.mkto_modal_exists = function(){
        var exists = false;
        $('.paragraph-type-title').each(function(){
          if($(this).text() == 'Marketo Modal'){
            exists = true;
          }
        });
        return exists;
      };

      // Is there Marketo Customizations?
      rgn.mkto_custom_exists = function(){
        if($('#edit-field-shared-marketo-custom-wrapper fieldset table').length){
          return true;
        }
        return false;
      };

      // Show error mssg.
      rgn.show_error = function(errorMssg, errorField){
        var error_mssg = ' \
          <div role="contentinfo" aria-label="Error message" class="messages messages--error"> \
            <div role="alert"> \
              <h2 class="visually-hidden">Error message</h2> \
              ' + errorMssg + ' \
            </div> \
          </div>';
        $('main.page-content').prepend(error_mssg);
        $(errorField).css('border', '3px solid red');
        $([document.documentElement, document.body]).animate({ scrollTop: 0 }, 1000);
        return false;
      };

      // Remove errors.
      rgn.remove_error = function(){
        $('.messages--error').remove();
        $(rgn.modal_field).css('border', 'none');
        $(rgn.cust_field).css('border', 'none');
      };

      // If regional landing page, then watch form submit event:
      if(rgn.is_rgn_page()){
        // Form submit:
        $(once('rgn_form_submit', $('.layout-region-node-footer'))).on('click','.form-submit',function(event){
          // Remove any old errors:
          rgn.remove_error();

          var error = false;
          var errorField;

          // If CTA without a Modal paragraph:
          if(rgn.mkto_cta_exists() && !rgn.mkto_modal_exists()){
            error = 'Please add a Marketo Modal paragraph type.';
            errorField = rgn.modal_field;
          }

          // If Modal without Customizations:
          if(rgn.mkto_modal_exists() && !rgn.mkto_custom_exists()){
            error = 'Please add Marketo Customizations.';
            errorField = rgn.cust_field;
          }

          // If error:
          if(error){
            rgn.show_error(error, errorField);
            event.preventDefault();
          }

        });
      }
    }
  }

  // Campaign Landing Pages validation.
  Drupal.behaviors.campaignPagesValidate = {
    attach: function attach(context, settings) {

      var cmpgnPg = {};

      // Is this a campaign landing page?
      cmpgnPg.is_cmpgn_page = function(){
        if($('form').attr('id') && $('form').attr('id').indexOf('campaign-page') > 0){
          return true;
        }
        return false;
      }
      // Is the Right Rail enabled?
      cmpgnPg.rr_enabled = function(){
        var rr_enabled = $('#edit-field-shared-rr-checkbox-value').prop('checked');
        if(rr_enabled){
          return true;
        }
        return false;
      }
      // Check select menus:
      cmpgnPg.check_sel_menus = function(){
        var hasForm = false, formName;
        $('form select').each(function(){
          formName = $(this).attr('name');
          if (formName.includes('field_campaign_page_marketo_form')) {
            hasForm = true;
          }
        });
        if(hasForm){
          return true;
        }
        return false;
      }
      // Does the RR have a form?
      cmpgnPg.rr_has_form = function(){
        // Creae Node form:
        if ($('form').attr('id') == 'node-campaign-page-form') {
          if(cmpgnPg.check_sel_menus()){
            return true;
          }
          return false;
        }
        // Edit Node form:
        else {
          if ($('#edit-field-campaign-page-marketo-form-0-top-paragraph-type-title ').length) {
            return true;
          }
          else if (cmpgnPg.check_sel_menus()){
            return true;
          }
        }
        return false;
      }

      // Remove errors.
      cmpgnPg.remove_error = function(){
        $('.messages--error').remove();
        $('#edit-field-campaign-page-marketo-form-wrapper').css('border', 'none');
      }

      // Show error mssg.
      cmpgnPg.show_error = function(){
        var error_mssg = ' \
          <div role="contentinfo" aria-label="Error message" class="messages messages--error"> \
            <div role="alert"> \
              <h2 class="visually-hidden">Error message</h2> \
              You must select a form for the Right Rail. \
            </div> \
          </div>';
        $('main.page-content').prepend(error_mssg);
        $('#edit-field-campaign-page-marketo-form-wrapper').css('border', '3px solid red');
        $([document.documentElement, document.body]).animate({ scrollTop: 0 }, 1000);
        return false;
      }

      // If campaign landing page, then watch form submit event:
      if(cmpgnPg.is_cmpgn_page()){
        $(once('rgn_form_submit', $('.layout-region-node-footer'))).on('click','.form-submit',function(event){
          cmpgnPg.remove_error();
          // If Right Rail is enabled but there's no campaign form:
          if(cmpgnPg.rr_enabled() && !cmpgnPg.rr_has_form()){
            cmpgnPg.show_error();
            event.preventDefault();
          }
        });
      }
    }
  }

  // Custom Block - hide field for translations.
  Drupal.behaviors.blockTranslations = {
    attach: function attach(context, settings) {
      // Current path:
      var currPath = window.location.pathname;
      // Site name:
      var site_name = drupalSettings.ul_marketo.site_name;

      // If LATAM site:
      if (site_name.includes('LATAM')){
        // First 3 characters in path:
        var first3 = currPath.substring(0, 3);
        // If the first 3 chars is "/en":
        if (first3 == '/en'){
          return;
        }
      }

      // Add trans example: /fr/block/286/translations/add/en/fr
      var addTrans = new RegExp("\/[a-z\-]*\/block\/[0-9]*\/translations\/add\/[a-z\-]*\/[a-z\-]*");
      // Edit trans example: /fr/block/318
      var editTrans = new RegExp("\/[a-z\-]*\/block\/[0-9]*");
      if (addTrans.test(currPath) || editTrans.test(currPath)) {
        $('#edit-field-mkto-campaign-0-value').prop('readonly', true).css('background', '#ececec');
        $("label[for='edit-field-mkto-campaign-0-value']").text('Marketo Campaign (only editable in original language)');
        $('#edit-field-mkto-campaign-0-value--description').remove();
      }
    }
  }

  // Mkto Form Block - deprecate / hide.
  // Prevent new blocks from being added.
  Drupal.behaviors.deprecateBlock = {
    attach: function attach(context, settings) {
      if('/block/add' == window.location.pathname.slice(-10)){
        $('.block .admin-list li a').each(function(){
          if($(this).prop('href').includes('add/mkto_form')){
            $(this).parent().remove();
          }
        });
      }
    }
  }

  // Emergo node form alterations.
  Drupal.behaviors.emergoNodeForm = {
    attach: function attach(context, settings) {
      if( window.location.host.includes('emergo') ) {
        $('.dropbutton__item input').each(function(){
          if($(this).attr('id') && $(this).attr('id').includes('crc-files')){
            $(this).parent().remove();
          }
        });
      }
    }
  }

  // Copy the submit button text to the CTA button text if it's empty
  Drupal.behaviors.ctatitleNodeForm = {
    attach: function attach(context, settings) {
        $('.ief-form input').each(function(index){
          var ctaText = '';
          if($(this).attr('id') && $(this).attr('id').includes('shared-cta-button')) {
            ctaText = $(this).attr('data-drupal-selector');
            var submitText = $("input[data-drupal-selector*='"+ctaText+"']").closest('.field--type-string').next().find('.js-text-full').val();

            if($(this).val()==''){
              $(this).val(submitText);
            }
          }
        });
    }
  }

  // Revision log character counter:
  Drupal.behaviors.revLogCounter = {
    attach: function attach(context, settings) {
      let inputId = false;
      const winPath = window.location.pathname;
      if (winPath.includes('/node/add/')
        || winPath.includes('/node/edit/')
        || winPath.includes('/translations/add/')
        || (winPath.includes('/node/') && winPath.includes('/edit'))
      ){
        $('.form-textarea').each(function(index) {
          let elemId = $(this).attr('id');
          if (elemId.includes('edit-revision-log')) {
            inputId = elemId;
            $(this).parent().siblings('.form-item__label').append('<div class="rev-log-counter">Current length is <span>0</span> of 16</div>');
            return false;
          }
        });
        if (inputId) {
          $(`#${inputId}`).on('keyup', function(){
            $('.rev-log-counter span').html($(this).val().length);
          });
        }
      }
    }
  }

})(jQuery, window, Drupal, drupalSettings);
