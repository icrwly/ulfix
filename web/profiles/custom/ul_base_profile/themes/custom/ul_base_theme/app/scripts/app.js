// imposrt animation on scroll library
import AOS from './libs/aos.js';
import imagepopup from './libs/image_popup.js'
// https://webpack.js.org/configuration/output/#output-publicpath,
// https://github.com/webpack/webpack/issues/2776#issuecomment-233208623
/* eslint-disable no-undef */

if (typeof asset_path !== "undefined") {
    __webpack_public_path__ = asset_path
}
import moduleRegistry from './modules/'

moduleRegistry.init()

//IE11 fix - Click to Enlarge Image overlap GNaaS
if ($('#ul-global-nav').length) {
  if($('.mfp-wrap').length) {
    $('#ul-global-nav').css("display", "none");
  }else {
    $('#ul-global-nav').css("display", "block");
  }
}

//Initialize animation
$(window).on('load', function() {

  $('.card--spotlight .swiper-slide .popup_spotlight').removeAttr("href");
  $('.swiper-container .popup_spotlight').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    closeBtnInside: false,
    fixedContentPos: true,
    mainClass: 'mfp-no-margins mfp-with-zoom',
    image: {
      verticalFit: true
    },
    zoom: {
      enabled: true,
      duration: 300
    }
  });
  $('.popup-gallery').magnificPopup({
    delegate: 'a',
    type: 'image',
    closeBtnInside: false,
    tLoading: 'Loading image #%curr%...',
    mainClass: 'mfp-img-mobile',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0,1]
    },
    image: {
      tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
      titleSrc: ''
    }
  });
  $('.popup-gallery-img').magnificPopup({
    delegate: 'a',
    closeBtnInside: false,
    type: 'image',
    tLoading: 'Loading image #%curr%...',
    mainClass: 'mfp-img-mobile',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0,1]
    },
    image: {
      tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
      titleSrc: ''
    }
  });

  $('.chat-offline-modal').magnificPopup({
      type: 'inline',
      fixedContentPos: false,
      fixedBgPos: true,
      overflowY: 'auto',
      closeBtnInside: true,
      preloader: false,
      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in'
  });
  $(document).on('click', '.popup-modal-dismiss', function (e) {
      e.preventDefault();
      $.magnificPopup.close();
  });

  //select all carousel images on the page
  var all_swiper = document.querySelectorAll(".swiper-slide img");
  var sw;
  var d_src;
  for (sw = 0;sw < all_swiper.length;sw++) {
    d_src = all_swiper[sw].getAttribute("data-src");
    //check if src attribute exist
    if (!all_swiper[sw].src || !all_swiper[sw].src.length || all_swiper[sw].src.length === 0) {
        all_swiper[sw].src = d_src;
    }
  }
  if ($(".dtable-full").length < 1) {
    if ($(".dtable-simple").length < 1) {
      //select all spotlights on the page
      var all_spl = document.querySelectorAll(".section--spotlight");
      var s;
        for (s = 0;s < all_spl.length;s++) {
          //adding animation effects
            all_spl[s].setAttribute("data-aos", "fade-zoom-in");
            all_spl[s].setAttribute("data-aos-easing", "ease-in-back");
            all_spl[s].setAttribute("data-aos-duration", "800");
        }
    }
  }
  //select all related content paragraphs
  var all_rc = document.querySelectorAll(".paragraph--related-content .row--gutter-s");
  var two_cols = $('main').hasClass('two-col');
  var i;
  for (i = 0;i < all_rc.length;i++) {
    //adding class rel_content_#
    //this class help provides access to each related content paragraph
    //and we can have different animation rules
    all_rc[i].classList.add("rel_content_"+i);
  }
  for (i = 0;i < all_rc.length;i++) {
    var rc_card_span;
    var animation_delay;
    var j;
    var c = 0;
    var counter;

    //check if "three items per row" display swlected
    if (document.querySelectorAll(".rel_content_"+ i +" .span-6-t.span-4-d") !== null){
      //select each card
      rc_card_span = document.querySelectorAll(".rel_content_"+ i +" .span-6-t.span-4-d");
      //maximum animation delay
      animation_delay = 400;
      for (j = 0;j < rc_card_span.length;j++) {
        //add animation_delay to each card and update to zero if new line
        counter = c*200;
        if(counter>=animation_delay){c=0}else{c++}
        //adding animation effects
        rc_card_span[j].setAttribute("data-aos", "fade-zoom-in");
        rc_card_span[j].setAttribute("data-aos-easing", "ease-in-back");
        rc_card_span[j].setAttribute("data-aos-duration", "800");
        rc_card_span[j].setAttribute("data-aos-delay", counter);
      }
    }
    //check if "four items per row" display swlected
    if(document.querySelectorAll(".rel_content_"+ i +" .span-6-t.span-3-d") !== null){
      rc_card_span = document.querySelectorAll(".rel_content_"+ i +" .span-6-t.span-3-d");

      if (two_cols == true){
        animation_delay = 400;
      }else{
        animation_delay = 600;
      }
      for (j = 0;j < rc_card_span.length;j++) {
        counter = c*200;
        if(counter>=animation_delay){c=0}else{c++}
        rc_card_span[j].setAttribute("data-aos", "fade-zoom-in");
        rc_card_span[j].setAttribute("data-aos-easing", "ease-in-back");
        rc_card_span[j].setAttribute("data-aos-duration", "800");
        rc_card_span[j].setAttribute("data-aos-delay", counter);
      }
    }
    //other
    if(document.querySelectorAll(".section__inner.rel_content_"+ i +" .span-6-t") !== null) {
      rc_card_span = document.querySelectorAll(".section__inner.rel_content_"+ i +" .span-6-t");
      animation_delay = 400;
      for (j = 0;j < rc_card_span.length;j++) {
        counter = c*200;
        if(counter>=animation_delay){c=0}else{c++}
        rc_card_span[j].setAttribute("data-aos", "fade-zoom-in");
        rc_card_span[j].setAttribute("data-aos-easing", "ease-in-back");
        rc_card_span[j].setAttribute("data-aos-duration", "800");
        rc_card_span[j].setAttribute("data-aos-delay", counter);
      }
    }
  }
  AOS.init();
});

// Custom Select
/* Custom select styles require the SELECT element to be wrapped in a DIV to
   position the SELECT icon. Drupal gives us a wrapper DIV but also puts the
   field LABEL in it, which throws off icon positioning. Until we can customize
   the SELECT HTML coming out of Drupal, use JS to add the wrapper. */
var applyCustomSelect = function ($el) {
    $el.each(function (i, e) {
      if (!$(e).closest('.custom-select').length) {
       $(e).wrap('<div class="custom-select"/>');
      }
    });
};

applyCustomSelect($('.js-form-type-select .form-select'))


// Custom Drupal Behaviors

import once from 'jquery-once'

if (typeof Drupal !== 'undefined') {
  Drupal.behaviors.customBehaviors = {
    attach: function (context, settings) {
      // Custom Select
      $('.js-form-type-select .form-select', context).once('applyCustomSelect').each(function (i, e) {
        applyCustomSelect($(e));
      });

      // Reset Filters
      $('.filter-bar__active-filters .active-filter', context).once('handleFilters').each(function (i, e) {
        $(e).on('click', function (e) {
          e.preventDefault();
          const target = $(e.currentTarget).data('target')
          $('.filter-bar__filter-options .form-select[name="' + target + '"] option:eq(0)').prop('selected', true)
          $(e.currentTarget).closest('form').find('[data-bef-auto-submit-click]').trigger('click');
        })
      });

      // Handle Form Submit
      $('.filter-bar__active-filters .form-submit', context).once('handleFilterSubmit').each(function (i, e) {
        $(e).on('click', function (e) {
          e.preventDefault();
          $('.filter-bar__filter-options .form-select').each(function (i, e) {
            $(e).find('option:eq(0)').prop('selected', true);
          });
          $(e.currentTarget).closest('form').find('[data-bef-auto-submit-click]').trigger('click');
        });
      });
    }
  };
}
// NOTE: alerts JS moved to this separate file. Ultimately this will go into a Drupal module.
// import './libs/alerts'
import wistia from './libs/wistia_background.js'
