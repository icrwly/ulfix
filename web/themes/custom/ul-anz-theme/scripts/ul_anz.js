/**
 * JS functions for the Australia, New Zealand site.
 */

jQuery(document).ready(function($){

  // Move Social Media (addtoany) icons below the
  // registration form (Mkto Form block).
  $(".section--addtoany").each(function() {
      var item = $(this);
      item.insertBefore(".region-bottom");
  });

});
