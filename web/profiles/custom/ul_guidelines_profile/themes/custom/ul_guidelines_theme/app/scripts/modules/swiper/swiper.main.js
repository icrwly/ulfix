'use strict';

import $ from 'jquery';
// Require swiper.js. Otherwise defaults to swiper.esm.js which uses ES6 syntax.
// import Swiper from 'swiper/dist/js/swiper.js';
import Swiper from 'swiper/swiper.esm.js';
import enquire from 'enquire.js';

export default class SwiperCarousel{
  constructor($el){
    this.$el = $el;
    this.persist = $(this.$el).data('persist');
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
      centeredSlides: false,
      loop: false,
      mode:'horizontal',
      nextButton: $(this.$el).find('.swiper-button-next'),
      observer: true,
      observeParents: true,
      pagination: $(this.$el).find('.swiper-pagination'),
      prevButton: $(this.$el).find('.swiper-button-prev'),
      slidesPerGroup: 1,
      slidesPerView: 1,
      slideVisibleClass: 'swiper-slide-visible',
      spaceBetween: 0,
      speed: 400,
      watchSlidesVisibility : true
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
    this.swiperInstance = new Swiper($(this.$el).find('.swiper-container'), opts);
  }
}
