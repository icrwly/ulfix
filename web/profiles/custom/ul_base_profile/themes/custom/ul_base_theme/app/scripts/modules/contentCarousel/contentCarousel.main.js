'use strict';
// It is required to import sub modules in the new Swiper 9 esm.
import Swiper, {Navigation, Pagination} from 'swiper/swiper.esm.js';
import $ from 'jquery';
// import Swiper from 'swiper/swiper.esm.js';

export default class ContentCarousel{
  constructor($el){
    this.$el = $el;
    this.$slides = $(this.$el).find('.swiper-slide');
    this.persist = $(this.$el).data('persist');
    this.navigation = $(this.$el).data('navigation');
    this.isInMegaMenu = $(this.$el).closest('.mega-menu').length;

    $(window).on('load', () => {
      // Skip this JS for the Pargraphp Carousel.
      var isParagraphCarousel = $('section.section--paragraph .swiper-container');
      if (isParagraphCarousel.length > 0) {
        this.destroy();
        this.setup();
      }

    });
  }
  destroy(){
    if (typeof this.swiperInstance !== 'undefined' && this.swiperInstance !== null) {
      this.swiperInstance.destroy(true, true);
      this.swiperInstance = null;
    }
  }
  setup(){
    var slider_content, strSelector;
    // Fix the error: Failed to execute getComputedStyle when using Swiper.
    // Check if the element exists.
    // The getElementsByClassName doesn't work; change to jQuery selector.
    var sectionLists = $('section.section--paragraph');
    var isSwiper = $('section:not(.hero) .swiper-container');

    if (isSwiper.length > 0) {
      // init Swiper: useing the const intead of var.Ref. https://swiperjs.com/get-started.
      // Handle multiple carousels on the same page.
      sectionLists.each(function( index ) {

        if ($(this).hasClass('section--gallery')) {
          strSelector = 'section.section--gallery .swiper-container';
        } else if ($(this).find('.paragraph--spotligh')) {
          strSelector = 'section.section--spotlight .swiper-container';
        } else {
          strSelector = "";
        }


        if (strSelector == "") {
          return false;
        }
        slider_content = new Swiper(strSelector, {
          // configure Swiper to use modules.
          modules: [Navigation, Pagination],

          pagination: {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true,
            dynamicBullets: true,
            dynamicMainBullets: 1
          },

          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },

          autoHeight: false,
          centeredSlides: false,
          threshold: 10,
          effect: 'fade',
          fadeEffect: {
            crossFade: true
          },

          rewind: true,
          spaceBetween: 36,
          speed: 1000,
          roundLengths: true,
          watchOverflow: true,
          slidesPerView: 1,
          slidesPerGroup: 1,
        });

      });

    }

  }
}
