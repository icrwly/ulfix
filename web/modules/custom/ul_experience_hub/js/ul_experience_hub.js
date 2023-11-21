/**
 * Experience Hub / Multi-select filters JS functions.
 */

(function ($, window, Drupal) {

    Drupal.behaviors.customFilter = {
      attach: function (context, settings) {

        function disableScroll() {
          document.body.classList.add("stop-scrolling");
        }

        function enableScroll() {
          document.body.classList.remove("stop-scrolling");
        }

        function mobile_filters() {
          var screen_width = window.innerWidth;
          var screen_height = window.innerHeight;
          //Check the width
          if(screen_width<992 ) {
            if(screen_width<768){
              var buttons_block_height = 170;
          }else{
              var buttons_block_height=120;
          }
          $(".mobile_filter.show").css({'top': '0','z-index':'999','overflow-y': 'scroll','position': 'fixed'});
          $(".mobile_filter.show .checkbox_filter").css({'width':'80%','right':'0','height': screen_height-buttons_block_height,'max-height':screen_height-buttons_block_height,'overflow-y': 'scroll','position':'fixed'});
          $(".mobile_filter.show .mobile_actn").css({'width':'80%','top': screen_height-buttons_block_height,'position':'fixed', 'right':'0'});
          $("#ul-global-nav").attr('style', 'z-index: 90 !important;');
          if($(".mobile_filter.show").length) {
            disableScroll();
          }
          } else {
            $(".mobile_filter.show").attr('style', '');
            $(".mobile_filter.show .checkbox_filter").attr('style', '');
            $(".mobile_filter.show .mobile_actn").attr('style', '');
            $(".mobile_filter.hide").attr('style', '');
            $(".mobile_filter.hide .checkbox_filter").attr('style', '');
            $(".mobile_filter.hide .mobile_actn").attr('style', '');
            $("#ul-global-nav").attr('style', '');
            enableScroll();
          }
        }

        function addClass(instance, newclass, parent) {
          for (var i = 0; i < instance.length; i++) {
            instance[i].className = newclass;
            if (parent) {
              instance[i].parentElement.classList.add('has_children');
            }
          }
        }

        function fixTitleFormat(){
          setTimeout(function(){
            $(".card h3").each(function (index, value) {
              if (value) {
                var text = value.innerText;
                var find = ["&lt;br/&gt;", "&lt;br /&gt;"];
                var replacement = ["<br />", "<br />"];
                text = text.replaceArray(find, replacement);
                $(this).html(text);
              }
            });
          }, 1000);
        }

        // Get direct descendant list-item children:
        var parent_taxonomies = document.querySelectorAll('.form-checkboxes > ul');
        var children_taxonomies = document.querySelectorAll('.form-checkboxes > ul > li > ul');

        // Add needed classes for parents/children:
        addClass(parent_taxonomies,'panel toplvl');
        addClass(children_taxonomies,'panel btmlvl','y');

        // Open/close filters:
        $('.multiselect-filter-results .pager__active-page.pager__button').click(function(e){
          if(!$(this).parent().hasClass('is-open')) {
            $(this).parent().toggleClass('is-open');
          }else{
            $(this).parent().removeClass('is-open');
          }
        });

        // Add classes for checked items:
        $(".multiselect-children input:checked").each(function(index, value){
            $(this).parent().closest('ul').addClass('checked');
            $(this).parent().closest('.multiselect-children').addClass('checked');
        });

        if($('.panel').find('.btmlvl.checked').length || $('.panel').find('.toplvl.checked').length) {
          $('.panel').find('.panel').slideUp(0);
          $('.checked').find('.toplvl').slideToggle(0);
          $('.panel').find('.btmlvl.checked').slideToggle(0);
          $('.panel').find('.btmlvl.checked').parent().addClass('checked-li');
          $('.multiselect-children.checked').closest('.title_categ').toggleClass('checked');
          if (window.location.href.indexOf("page") < 0) {
            $('.mobile_filter.show').animate({
                right: "-70px"
            }, 0, function() {
              // Animation complete.
            });
            $( ".filter-slider-arrow, .multiselect-filter .mobile_filter" ).removeClass('hide').addClass('show');
            $('.filter-slider-arrow').html('').removeClass('show').addClass('hide');
            mobile_filters();
          }
        } else {
          $('.panel').find('.panel').slideUp();
          $('.panel').find('.panel').first().slideToggle(100);
          $('.panel').first().toggleClass('selected');
          if($('.rst_all').length) {
            $('.mobile_filter.show').animate({
                right: "-70px"
            }, 0, function() {
                });
                $( ".filter-slider-arrow, .multiselect-filter .mobile_filter" ).removeClass('hide').addClass('show');
                $('.filter-slider-arrow').html('').removeClass('show').addClass('hide');
                mobile_filters();
          }
          $('body').removeClass('rst_all');
        }

        // Show bottom level panel:
        $('.toplvl .js-form-type-checkbox').click(function(e){
          var $target = $(e.target);
          if (!$target.is('input')) {
            $(this).next('.panel.btmlvl').slideToggle(100);
            $(this).toggleClass('selected');
          }
        });

        // Uncheck/disable filter:
        $('.filter-slider-uncheck').click(function(){
          $('.multiselect-children input:checkbox').removeAttr('checked');
          $('.multiselect-children .js-form-type-checkbox').addClass('disabled_fltr');
          $('.btmlvl .js-form-type-checkbox .option').addClass('disabled_fltr');
          $('.filter-slider-uncheck').addClass('disabled_fltr');
          $('.title_categ').addClass('disabled_fltr');
          $('body').addClass('rst_all');
          $('.filter-bar__search .js-form-type-textfield input').val('');
          $('.filter-bar__search .js-form-type-textfield').addClass('disabled_fltr');;
          $('.multiselect-children').closest('form').find('[data-bef-auto-submit-click]').trigger('click');
          var uri = window.location.toString();
          if (uri.indexOf("?") > 0) {
              var clean_uri = uri.substring(0, uri.indexOf("?"));
              window.history.replaceState({}, document.title, clean_uri);
          }
          fixTitleFormat();
        });

        // Specify active filter:
        $('.multiselect-children .js-form-type-checkbox input').click(function(){
          $('.multiselect-children .js-form-type-checkbox').addClass('disabled_fltr');
          $('.btmlvl .js-form-type-checkbox .option').addClass('disabled_fltr');
          $('.title_categ').addClass('disabled_fltr');
          $('.filter-slider-uncheck').addClass('disabled_fltr');
          $('.filter-bar__search .js-form-type-textfield').addClass('disabled_fltr');
          $(".multiselect-children .js-form-type-checkbox.highlight").addClass('active_fltr');
          $('body').addClass('rst_all');
          $(this).parent().addClass('active_fltr');
          var uri = window.location.toString();
          if (uri.indexOf("?") > 0) {
              var clean_uri = uri.substring(0, uri.indexOf("?"));
              window.history.replaceState({}, document.title, clean_uri);
          }
          fixTitleFormat();
        });

        // Select category on title click:
        $('.title_categ').click(function(){
          $(this).next('.form-checkboxes').toggleClass('selected_categ');
          $(this).toggleClass('selected');
          $('.selected_categ.form-checkboxes .form-checkboxes .toplvl').slideToggle(100);
          $(this).next('.form-checkboxes').removeClass('selected_categ');
        });

        $(window).on('resize', function(){
          mobile_filters();
        });

        //Filters Accordion Menu
        $('.filter-slider-arrow').click(function(){
          if($(this).hasClass('show')){
            $( ".mobile_filter" ).animate({
              right: "0"
            }, 700, function() {
              // Animation complete.
            });

            $( ".filter-slider-arrow, .multiselect-filter .mobile_filter" ).removeClass('hide').addClass('show');
            $(this).html('').removeClass('show').addClass('hide');
            mobile_filters();

          } else {
            $( ".multiselect-filter .mobile_filter" ).animate({
                right: "-=50%"
            }, 700, function() {
              // Animation complete.
            });
            $( ".filter-slider-arrow, .multiselect-filter .mobile_filter" ).removeClass('show').addClass('hide');
            $(this).html('Filter').removeClass('hide').addClass('show');
          }
        });

        $('.filter-slider-done').click(function(){
            $( ".multiselect-filter .mobile_filter" ).animate({
                right: "-=50%"
            }, 700, function() {
              // Animation complete.
            });
            $( ".filter-slider-arrow, .multiselect-filter .mobile_filter" ).removeClass('show').addClass('hide');
            $( ".filter-slider-arrow" ).html('Filter').removeClass('hide').addClass('show');
            enableScroll();
        });

        $('html, body').stop();
        fixTitleFormat();

      }
    };

})(jQuery, window, Drupal);
