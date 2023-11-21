/**
 * @file
 * Disable <a> link on Content type and Lanuage columns.
 **/

 (function ($, window, Drupal) {

  // Export media data to CSV via JS library.
  Drupal.behaviors.csvMediaExport = {
    attach: function attach(context, settings) {
      let $btnCsv = $('#download_this_page a');
      $btnCsv.click(function( event ) {
        let d = new Date();
        let strDate = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
        $('.view-id-ul_media_usage table.views-view-table').csvExport({
          escapeContent:true,
          title: "UL-Media-Usage-Export-" + strDate + ".csv"
        });
      });
    }
  }

})(jQuery, window, Drupal);
