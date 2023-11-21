/**
 * NAME: MARKETO MODAL JS
 * VERSION: 1.0.5
 * LAST UPDATED: Sep 13, 2023
 * REQUIRED: jQuery
 */

(function ($, window, Drupal) {

  Drupal.behaviors.modalMarketo = {

    attach: function attach(context, settings) {

      // Only run if a Marketo Modal <div> exists!
      if($('.mktoModal').length){

        var hsh = window.location.hash.substring(1);
        var mktoHsh = 'show-modal';

        // Open modal on page load:
        if(hsh == mktoHsh){
          window.addEventListener('load', function() {
            _remove_survey();
            $('.mktoModal').addClass('is-visible');
          });
        }

        // Open modal when #hash value changes (button click):
        $(window).bind('hashchange', function() {
          hsh = window.location.hash.substring(1);
          if(hsh == mktoHsh){
            _remove_survey();
            $('.mktoModal').addClass('is-visible');
            $('html, body').animate({ scrollTop: 0 }, 'slow');
          }
        });

        // Close modal.
        $('.mktoModalClose, .mkto-ok-btn, .mktoModalMask').on('click', function() {
          $('.mktoModal').removeClass('is-visible');
          window.location.hash = '#';
        });

        // Change links to use #show-modal instead of links
        // to the routed marketo pages:
        $('.hero a, .siderail--cta a, .paragraph--action-banner a').each(function(){
          var href = $(this).prop('href');
          var inq_s = '/sales-inquiries/';
          var inq_y = '/sales-inquiry/';
          var modal_a = '#show-modal';
          if (href.includes(inq_s) || href.includes(inq_y)){
            $(this).attr('href', modal_a);
          }
        });

        // Helper function: Remove Qualtrics Survey:
        function _remove_survey() {
          delete QSI;
          $('script').each(function() {
            if ($(this).attr('src') && $(this).attr('src').includes('qualtrics')) {
              $(this).remove();
            }
          });
          $('div').each(function() {
            if ($(this).hasClass('QSIWebResponsive')) {
              $(this).fadeOut().remove();
            }
          });
        }

      }
    }
  }
})(jQuery, window, Drupal);
