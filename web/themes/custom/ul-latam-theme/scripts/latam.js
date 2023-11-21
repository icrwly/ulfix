/**
 * LATAM.ul.com JS functions.
 */

jQuery(document).ready(function($){

  // Move Social Media (addtoany) icons below the
  // registration form (Mkto Form block).
  $(".section--addtoany").each(function() {
      var item = $(this);
      item.insertBefore( ".region-bottom" );
  });

});
