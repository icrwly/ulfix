'use strict';

import Swiper, {Navigation, Pagination} from 'swiper/swiper.esm.js';
import $ from 'jquery';
// import enquire from 'enquire.js';

export default class HeroCarousel{
  constructor($el){
    this.$el = $el;
    this.persist = $(this.$el).data('persist');
    this.navigation = $(this.$el).data('navigation');

    $(window).on('load', () => {
      this.destroy();
      this.setup();
    });
  }
  combinedOpts(...opts){
    return $.extend(...opts);
  }
  createOpts(){
    this.defaultOpts = {
      // Use the modules.
      modules: [Navigation, Pagination],
      pagination: {
        el: $(this.$el).find('.swiper-pagination'),
        type: 'bullets',
        clickable: true
      },
      navigation: {
        nextEl: $(this.$el).find('.swiper-button-next'),
        prevEl: $(this.$el).find('.swiper-button-prev'),
      },
      autoHeight: true,
      centeredSlides: false,
      threshold: 10,
      effect: 'slide',
      loop: true,
      slidesPerGroup: 1,
      slidesPerView: 1,
      spaceBetween: 0,
      speed: 400,
      on: {
        init: function () {
          let slideLen = $(this.$el).find('.swiper-slide:not(.swiper-slide-duplicate)').length;
          if(slideLen <= 1) {
            $(this.$el).addClass('hidden-pagination');
            $(this.$el).find('.swiper-pagination').hide();
          }
        }
      }
    };
    this.userOpts = $(this.$el).data('opts') || {};
  }
  destroy(){
    if (typeof this.swiperInstance !== 'undefined' && this.swiperInstance !== null) {
      this.swiperInstance.destroy(true, true);
      this.swiperInstance = null;
    }
  }
  setup(){

    this.createOpts();
    let opts = this.combinedOpts(this.defaultOpts, this.userOpts);
    this.currentOpts = opts;

    var strSelector = 'section.hero .swiper-container'
    // this.swiperInstance = new Swiper($(this.$el).find('.swiper-container'), opts);
    this.swiperInstance = new Swiper(strSelector, opts);

    if(this.navigation){
      $(this.$el).find('.swiper-button-next').on('click', (e) => {
        e.preventDefault();
        this.swiperInstance.slideNext();
      })
      $(this.$el).find('.swiper-button-prev').on('click', (e) => {
        e.preventDefault();
        this.swiperInstance.slidePrev();
      })
    }
  }
}
