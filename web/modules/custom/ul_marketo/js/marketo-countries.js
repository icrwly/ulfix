/**
 * NAME: MARKETO COUNTRIES JS
 * VERSION: 1.3.4
 * LAST UPDATED: Dec 4, 2023
 * REQUIRED: jQuery & MktoForms2
 */

var ulmkto = {};

(function ($) {

  // Helper function; Country select menu changes:
  function _handleCountryChanges() {
    var selected_cntry = $('#Country').val();

    if (!$('#Country').hasClass('gdpr_select')) {
      $('#Country').addClass('gdpr_select');
    }

    $('#edit-web-country option').each(function(){
      if(selected_cntry == $('#Country').val()){
        $('#Country').attr('selected', 'selected');
      } else {
        $('#Country').removeAttr('selected');
      }
    });

    ulmkto.update_gdpr(selected_cntry);

    // Uncheck input checkboxes on change:
    $('#emailOptin, .faux_optin, .pipl_accept').prop('checked', false);

    // But, if it is a SOI country add check to hidden emailOptin box:
    if (ulmkto.optin_type == 'single') {
      $('#emailOptin').prop('checked', true);
    }
  }

  // Check environment:
  ulmkto.check_env = function() {
    if ($('form').length == 0) {
      return false;
    }

    if (typeof window.MktoForms2 === 'undefined') {
      return false;
    }

    return true;
  }

  // Clear interval, stop env check:
  ulmkto.pbar_show_error = function() {
    if ($('.pbar-wrap').length) {
      pbar.error = true;
      $('.pbar-wrap').html('<div class="pbar-error">' + pbar.errorMssg + '</div>');
    }
  }

  // Prepare the form and set defaults:
  ulmkto.prep_form = function() {
    if (typeof get_optin_mssg !== 'function') {
      ulmkto.pbar_show_error();
      return;
    }
    ulmkto.optin_mssg = get_optin_mssg(ulmkto.lang);
    ulmkto.remove_styles();
    ulmkto.add_classes();
    ulmkto.reorder_ja_fields();
    ulmkto.customize_fields();
    ulmkto.finalize_form();
    ulmkto.watch_form();
  }

  // Get language of the page:
  ulmkto.get_lang = function() {
    var lang = false;
    if (typeof window.optinlang !== 'undefined') {
      lang = window.optinlang;
    }
    else if (typeof window.$language !== 'undefined') {
      lang = window.$language;
    }
    else if (typeof drplMkto.form_language !== 'undefined') {
      lang = drplMkto.form_language;
    }

    if (lang) {
      switch(lang) {
        case 'pt':
          lang = 'pt-br';
        break;

        case 'jp':
          lang = 'ja';
        break;

        case 'zh-cn':
        case 'zh_cn':
          lang = 'zh-hans';
        break;

        case 'zh-hk':
        case 'zh_hk':
          lang = 'zh-hant';
        break;
      }
    }
    else {
      lang = 'en';
    }

    return lang;
  }

  // Is this the Preference Center node:
  ulmkto.get_prefctr = function() {
    if ($('body').hasClass('path-preference-center')) {
      return true;
    }
    return false;
  }

  // Remove Mkto styles and unneeded HTML:
  ulmkto.remove_styles = function() {
    // Wait for the form to be ready and then prepare it.
    waitForElm('#mktoForms2BaseStyle').then(function(elm){
      var formBase = document.getElementById('mktoForms2BaseStyle');
      formBase.disabled = true;
      var formTheme = document.getElementById('mktoForms2ThemeStyle');
      formTheme.disabled = true;
      $('.mktoClear, .mktoGutter, .mktoOffset, .mktoForm style').remove();
      $('.mktoHasWidth, .mktoFormCol, .mktoButtonWrap').removeAttr('style').removeClass('mktoHasWidth');
      $(ulmkto.asterisk).replaceAll('.mktoRequiredField .mktoAsterix');
      $('.mktoAsterix').remove();
      $('.mktoRadioList input').each(function() {
        if ($(this).parent().hasClass('mktoRadioList')) {
          $('.mktoRadioList input').wrap('<div class="mktoRadioList-item"></div>');
          $('.mktoRadioList-item').append(function() {
            return $(this).next('label');
          });
          $(this).closest('.mktoRadioList').removeClass('mktoRadioList');
        }
      });
      $('.mktoForm .mktoCheckboxList input').each(function() {
        if (!$(this).parent().hasClass('mktoCheckboxWrap')) {
          $(this).next('label').addBack().wrapAll('<div class="mktoCheckboxWrap"/>');
        }
      });
    });
  }

  // Add needed classes:
  ulmkto.add_classes = function() {
    $('#optin-mssg').closest('.mktoFormRow').addClass('gdpr_target');
    $('.mktoButtonRow .mktoButton').addClass('button button--red mkto_submit');
    $('#emailOptin').closest('.mktoFormRow').addClass('hidden field--emailOptin').removeClass('mktoFormRow');
    $('#Country').addClass('gdpr_select');

    // Hidden fields:
    $('.mktoForm input[type=hidden]').each(function() {
      var field_nm = $(this).attr("name");
      $(this).closest('.mktoFormRow').addClass(`hidden field-${field_nm}`).removeClass('mktoFormRow');
    });

    // If this is the Pref Ctr:
    if (ulmkto.is_prefctr) {
      $("input:radio").each(function() {
        if ($(this).attr("name") == 'industryFBNLA') {
          $(this).addClass('prefctr_option');
        }
      });
    }

    // Japanese forms:
    if (ulmkto.lang == 'ja') {
      $('#FirstName').closest('.mktoFormRow').addClass('firstname_row');
      $('#LastName').closest('.mktoFormRow').addClass('lastname_row');
      $('#Company').closest('.mktoFormRow').addClass('company_row');
    }
  }

  // Reorder Japanese fields:
  ulmkto.reorder_ja_fields = function() {
    if (ulmkto.lang == 'ja') {
      if ($('.firstname_row').length && $('.lastname_row').length) {
        $('.lastname_row').insertBefore('.firstname_row');
        if ($('.company_row').length) {
          $('.company_row').insertAfter('.firstname_row');
        }
      }
    }
  }

  // Customize fields (for configurable forms):
  ulmkto.customize_fields = function() {
    if (typeof mkto_fields === 'undefined') {
      return;
    }

    ulmkto.config_form = true;

    // Hidden fields:
    if (mkto_fields.hasOwnProperty('hidden')) {
      if (Array.isArray(mkto_fields.hidden)) {
        for (var i = 0; i < mkto_fields.hidden.length; i++) {
          $(mkto_fields.hidden[i]).closest('.mktoFormRow').addClass(`hidden field-${mkto_fields.hidden[i]}`).removeClass('mktoFormRow');
        }
      }
    }

    // Required fields:
    if (mkto_fields.hasOwnProperty('required')) {
      if (Array.isArray(mkto_fields.required)) {
        for (var i = 0; i < mkto_fields.required.length; i++) {
          $(mkto_fields.required[i]).addClass('required').attr('required', 'true');
          $(mkto_fields.required[i]).siblings('.mktoLabel').prepend(ulmkto.asterisk);
        }
      }
    }
  }

  // Keep email opt-in checkbox synced with faux checkbox:
  ulmkto.do_checkbox_sync = function() {
    if ($('.faux_optin').is(':checked')){
      $('#emailOptin').prop('checked', true);
    } else {
      $('#emailOptin').prop('checked', false);
    }
  }

  // Dis/enable the form submit:
  ulmkto.disable_submit = function(disable) {
    if (disable) {
      $('.mkto_submit').prop('disabled', true);
    }
    else {
      $('.mkto_submit').prop('disabled', false);
    }
  }

  // Final style check.
  ulmkto.finalize_form = function() {
    // If the form is configurable:
    if (ulmkto.config_form) {
      $('.activeForm').attr('onsubmit', 'return false;');
    }
    // Add default opt-in mssg:
    ulmkto.show_double_optin(ulmkto.optin_mssg.doi.pre, ulmkto.optin_mssg.doi.txt);
    setTimeout(function(){
      $('.activeForm').removeClass('formLoading').addClass('formLoaded');
      $(document).trigger('formLoaded');
      ulmkto.is_valid = true;
      if ($('.loading-container').length) {
        $('.loading-container').slideUp('fast').remove();
      }
      if($('.activeForm .mktoGutter').length){
        ulmkto.remove_styles();
      }
      if(!$('#Country').hasClass('required')){
        ulmkto.customize_fields();
      }
    }, 1500);
    // Remove duplicate asterisks:
    $('.mktoLabel .asterisk').each(function(){
      $(this).siblings('.asterisk').remove();
    });
  }

  // Set single opt-in mssg:
  ulmkto.show_single_optin = function(soi_txt) {
    $(ulmkto.target).removeClass(['pipl','gdpr_doi']).addClass('gdpr_soi');
    var optin_mssg = `<p>${soi_txt}</p>`;
    $(ulmkto.target).empty().append(optin_mssg);
    ulmkto.disable_submit(false);
  }

  // Set double opt-in mssg:
  ulmkto.show_double_optin = function(doi_pre, doi_txt) {
    $(ulmkto.target).removeClass(['pipl','gdpr_soi']).addClass('gdpr_doi');
    var pre_optin = `<p>${doi_pre}</p>`;
    $(ulmkto.target).empty().append(pre_optin);
    var optin_mssg = `<p><label>${ulmkto.make_checkbox('optin')} ${doi_txt}</label></p>`;
    $(ulmkto.target).append(optin_mssg);
    ulmkto.disable_submit(false);
  }

  // Set PIPL opt-in mssg:
  ulmkto.show_pipl = function(pipl_txt, pipl_btm) {
    $(ulmkto.target).removeClass(['gdpr_soi','gdpr_doi']).addClass('pipl');
    var optin_txt = `<p><label>${ulmkto.make_checkbox('pipl_optin')} ${pipl_txt}</label></p>`;
    $(ulmkto.target).empty().append(optin_txt);
    var optin_btm = `<p><label>${ulmkto.make_checkbox('pipl')} ${pipl_btm}</label></p>`;
    $(ulmkto.target).append(optin_btm);
    ulmkto.disable_submit(true);
  }

  // Create Checkbox: Email Opt-in or PIPL Accept.
  ulmkto.make_checkbox = function(chechbox_type) {
    const reqd = ' <span class="required asterisk nopad">*</span>';
    const optin = '<input name="fauxOptin" type="checkbox" value="yes" class="mktoField faux_optin">';
    const pipl_optin = optin + reqd;
    const pipl_accpt = '<input name="pipl" type="checkbox" value="yes" class="mktoField pipl_accept">' + reqd;
    if (chechbox_type == 'optin')
      return optin;
    else if (chechbox_type == 'pipl_optin')
      return pipl_optin;
    else
      return pipl_accpt;
  }

  // Watch form for changes:
  ulmkto.watch_form = function() {
    $('.mktoForm').on('change', 'input[type="radio"], input[type="checkbox"], select', function() {
      // If this is the Country field, return. That is handled above.
      var thisId = $(this).attr('id');
      if ('Country' === thisId ) {
        _handleCountryChanges();
        return;
      }

      // Configurable form: Service selected = "software".
      if (ulmkto.config_form) {
        if ('uLServiceSegment' == thisId) {
          let serv = $(this).val();
          if (serv.includes('_Software')) {
            if (!$('#uLSoftwareCategory').hasClass('required')) {
              let label = $('#LbluLSoftwareCategory').text().replace('*', '');
              $('#LbluLSoftwareCategory').html(ulmkto.asterisk + ' ' + label);
              $('#uLSoftwareCategory').addClass('required');
            }
          }
        }
      }

      // Toggle opt-in message for Pref Center:
      if (ulmkto.is_prefctr) {
        if ($(this).hasClass('prefctr_option')) {
          if ('Unsubscribe all' === $('input[name="industryFBNLA"]:checked').val()) {
            if (!$(ulmkto.target).hasClass('hidden')) {
              $(ulmkto.target).addClass('hidden')
            }
          }
          else if ($(ulmkto.target).hasClass('hidden')) {
            $(ulmkto.target).removeClass('hidden')
          }
          if (ulmkto.lang == 'ja') {
            ulmkto.add_classes();
            ulmkto.reorder_ja_fields();
          }
        }
      }

      // Keep checkboxes synced:
      if(ulmkto.optin_type == 'single'){
        $("#emailOptin").prop('checked', true)
      } else {
        ulmkto.do_checkbox_sync();
      }

      // PIPL checkboxes:
      if ($(this).hasClass('pipl_accept') || $(this).hasClass('faux_optin')) {
        if ($('.pipl_accept').length) {
          if ($('.pipl_accept').is(':checked') && $('.faux_optin').is(':checked')) {
            ulmkto.disable_submit(false);
          } else {
            ulmkto.disable_submit(true);
          }
        }
      }

      // Reset styles:
      setTimeout(ulmkto.remove_styles, 500);
    });

    // On window resize we fight against Mkto styles again.
    // Credit to Chris Coyier
    // https://css-tricks.com/snippets/jquery/done-resizing-event/
    var resizeTimer;
    $(window).on('resize', function(e) {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        ulmkto.remove_styles();
      }, 300);
    });
  }

  // Update GDPR message based on Country val:
  ulmkto.update_gdpr = function(selected_val) {
    const doi_countries = ['Aland Islands', 'Argentina', 'Australia', 'Austria', 'Belgium', 'Brazil', 'Bulgaria', 'Canada', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Estonia', 'Finland', 'France', 'French Guiana', 'Germany', 'Greece', 'Greenland', 'Guernsey', 'Hungary', 'Iceland', 'Ireland', 'Isle of Man', 'Italy', 'Jersey', 'Latvia', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Malta', 'Netherlands', 'New Zealand', 'Norway', 'Poland', 'Portugal', 'Romania', 'Russian Federation', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Thailand', 'United Kingdom'];
    const pipl_countries = ['China', 'Vietnam', 'Viet Nam'];

    // PIPL:
    if (pipl_countries.includes(selected_val)) {
      ulmkto.is_pipl = true;
      ulmkto.optin_type = 'pipl';
      ulmkto.show_pipl(ulmkto.optin_mssg.pipl.txt, ulmkto.optin_mssg.pipl.btm);
    }
    else {
      ulmkto.is_pipl = false;
      // Double opt-in:
      if (doi_countries.includes(selected_val)) {
        ulmkto.optin_type = 'double';
        if (!$(ulmkto.target).hasClass('gdpr_doi')) {
          ulmkto.show_double_optin(ulmkto.optin_mssg.doi.pre, ulmkto.optin_mssg.doi.txt);
        }
      }
      // Single opt-in:
      else if (!$(ulmkto.target).hasClass('gdpr_soi')) {
        ulmkto.optin_type = 'single';
        ulmkto.show_single_optin(ulmkto.optin_mssg.soi);
      }
    }
  }

  // Get optin JS file:
  ulmkto.get_optin_js = function() {

    let curr_host = window.location.host;
    let optinfile = drplMkto.optin_file;

    if (optinfile == null || !optinfile.includes('.js')) {
      optinfile = 'https://www.ul.com';
      if (curr_host.includes('ulstage.com')){
        optinfile = 'https://www-dev.ul.com';
      }
      optinfile += '/modules/custom/ul_marketo/js/gdpr-mssg/default.js';
    }

    // Add the optin file to the head:
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = optinfile;
    script.onload = ulmkto.prep_form;
    head.appendChild(script);
  }

  // ================================= //
  // BEGIN:
  // ================================= //

  // If bad environment:
  if (!ulmkto.check_env()) {
    ulmkto.pbar_show_error();
    return;
  }

  // Create an object.
  var drplMkto = {};

  // If the drupal settings object exists
  // pass values to our new object.
  if(typeof drupalSettings === 'object'){
    drplMkto = drupalSettings.marketo;
  }

  // If the 'waitForElm' function is not defined:
  if (typeof waitForElm !== 'function') {
    function waitForElm(selector) {
      return new Promise(resolve => {
        if (document.querySelector(selector)) {
          return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(mutations => {
          if (document.querySelector(selector)) {
            resolve(document.querySelector(selector));
            observer.disconnect();
          }
        });

        observer.observe(document.body, {
          childList: true,
          subtree: true
        });
      });
    }
  }

  // If no GDPR opt-in file, load default:
  if (!$('#gdpr_optin').length) {
    ulmkto.get_optin_js();
  }

  // Wait for form to be ready, set defaults, and then prep.
  waitForElm('.mktoForm').then(function(elm){
    ulmkto.asterisk = '<span class="asterisk">*</span>';
    ulmkto.mktoForms2 = window.MktoForms2;
    ulmkto.is_valid = false;
    ulmkto.config_form = false;
    ulmkto.optin_type = 'double';
    ulmkto.lang = ulmkto.get_lang();
    ulmkto.is_prefctr = ulmkto.get_prefctr();
    ulmkto.target = '.gdpr_target';
    ulmkto.prep_form();
  });

})(jQuery);
