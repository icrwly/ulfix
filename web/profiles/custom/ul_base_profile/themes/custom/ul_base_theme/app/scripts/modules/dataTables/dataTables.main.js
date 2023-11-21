'use strict';

import 'datatables.net';
import 'datatables.net-responsive-dt';
import $ from 'jquery';

var table_simple;
var table_full;
var table_resp;

export default class dataTable {
  constructor(el) {
    this.$el = $(el);
    this.setup();
  }
  setup() {
    $(document).ready(function() {
      // $.fn.DataTable.isDataTable provides the ability to
      // check if a table node is already a DataTable or not.
      // Helps to prevent re-initialise a table that is already
      // a DataTable.
      $.fn.dataTable.ext.errMode = 'none';
      if ( !$.fn.DataTable.isDataTable( '.section--wysiwyg .dtable-simple' ) ) {
        table_simple = $('.section--wysiwyg .dtable-simple').DataTable(
          {
            responsive: true,
            info: false,
            bLengthChange: false,
            order: [],
            paging: false
          }
        );
      }
      if ( !$.fn.DataTable.isDataTable( '.section--wysiwyg .dtable-full' ) ) {
        table_full = $('.section--wysiwyg .dtable-full').DataTable(
          {
            responsive: true,
            paging: true,
            info: false,
            order: [],
            pageLength: 50
          }
        );
      }
      if ( !$.fn.DataTable.isDataTable( '.section--wysiwyg table:not(.dtable-full,.dtable-simple,.dtable-none)' ) ) {
        table_resp = $('.section--wysiwyg table:not(.dtable-full,.dtable-simple,.dtable-none)').DataTable(
          {
            responsive: true,
            searching: false,
            paging: false,
            bLengthChange: false,
            order: [],
            info: false
          }
        );
      }

      // Add responsive clases:
      $('.section--wysiwyg table').each(function( index ) {
        if ($(this).hasClass('dtable-simple') || $(this).hasClass('dtable-full')){
          $(this).addClass('display responsive');
        }
      });
    });
  }
}
