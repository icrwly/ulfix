/**
 * @file
 * Functionality for the Marketo Report views.
 **/

 (function ($, window, Drupal) {

    Drupal.behaviors.marketoReport = {
      attach: function attach(context, settings) {
          var anyOption = '<option value="All" selected="selected">- Any -</option>';
          var content_sub_type_options = $('select#edit-content-sub-type').html();
          var last_interest_options = $('select#edit-last-interest-filter').html();
          var options;
          var optgroup;
          var sub_cou;
          var last_interest;

          // Content types that have sub type fields.
          var contentSubTypes = {
            'event': 'Event Types',
            'help': 'Help Types',
            'knowledge': 'Insight Types',
            'news': 'News Types',
            'offering': 'Offering Types',
            'resource': 'Resource Types',
            'tool': 'Tool Types'
          }

          // If the form is loaded with exposed filters we need to update the content sub type and last interest filters.
          function init() {
            var type = $('form[data-exposed-form=marketo-report] select[data-drupal-selector=edit-type]').val();
            changeContentType(type);

            var sub_cou = $('form[data-exposed-form=marketo-report] select[data-drupal-selector=edit-sub-cou-filter]').val();
            changeSubCou(sub_cou);
          }

          function changeContentType(type) {
            if (type != 'All') {
              optgroup = contentSubTypes[type];
              if (optgroup != undefined) {
                // Reset to original options
                $('select[data-drupal-selector=edit-content-sub-type]').html(content_sub_type_options);

                // Get selected optgroup
                options = $('select[data-drupal-selector=edit-content-sub-type] optgroup[label="' + optgroup + '"]').html();
                $('select[data-drupal-selector=edit-content-sub-type]').html(anyOption + options);
                $('select[data-drupal-selector=edit-content-sub-type]').trigger("chosen:updated");
              }
              else {
                $('select[data-drupal-selector=edit-content-sub-type]').html(anyOption);
                $('select[data-drupal-selector=edit-content-sub-type]').trigger("chosen:updated");
              }
            }

          }

          function changeSubCou(sub_cou) {
            if (sub_cou != 'All') {

              let paramLast = getSearchParams("last_interest_filter");
              let selected = "";

              $('select[data-drupal-selector=edit-last-interest-filter]').html(anyOption);
              for (var i = 0; i < settings.ulReport.subCou[sub_cou]['last_interests'].length; i++) {
                last_interest = settings.ulReport.subCou[sub_cou]['last_interests'][i];

                if (paramLast == last_interest) {
                  selected = '  selected="selected"';
                }
                else {
                  selected = "";
                }

                // $('select[data-drupal-selector=edit-last-interest-filter]').append('<option value="' + last_interest + '">' + last_interest + '</option');
                $('select[data-drupal-selector=edit-last-interest-filter]').append('<option value="' + last_interest + '"' + selected + '>' + last_interest + '</option');
              }

            }
            else {
              $('select[data-drupal-selector=edit-last-interest-filter]').html(last_interest_options);
            }

            $('select[data-drupal-selector=edit-last-interest-filter]').trigger("chosen:updated");
          }

          /**
           * Get the parameter from the URL for Last Interster filter in marketo_report View.
           *
           * @param {*} k
           * @returns
           */
          function getSearchParams(k){
            var p={};
            location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){p[k]=v})
            var param = k?p[k]:p;

            param = decodeURIComponent(param);
            param = param.replace(/\+/g, " ");
            return param;
           }


          init();

          $('form[data-exposed-form=marketo-report] select[data-drupal-selector=edit-type]').change(function(){
            var type = $(this).val();
            changeContentType(type);
          });

          $('form[data-exposed-form=marketo-report] select[data-drupal-selector=edit-sub-cou-filter]').change(function(){
            sub_cou = $(this).val();
            changeSubCou(sub_cou);
          });

      }
    }
  })(jQuery, window, Drupal);
