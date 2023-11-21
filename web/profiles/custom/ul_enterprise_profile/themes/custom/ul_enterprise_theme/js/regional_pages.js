/**
 * @file
 * Funcatinality for Regional Pages.
 **/

// JS for the Regional Pages:
var suppressStickyCta = true;

// Regional Pages Menu Helper:
(function ($, Drupal){
  // Add container for mobile icon.
  $(document).ready(function(){
    if($('.dropbutton').length){
      $('.dropbutton li').each(function(i){
        if(i == 0){
          $(this).addClass('curr-lang').before('<li class="dropbutton-action mobi-icon"><span>&nbsp;</span></li>');
        }
      });
    }
    // Open/close menu:
    $('.dropbutton').on('click', '.mobi-icon', function(){
      $('.dropbutton-wrapper').toggleClass('open');
      return false;
    });
  });
})(jQuery, Drupal);
