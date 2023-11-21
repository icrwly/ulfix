'use strict'

import $ from 'jquery'
// Require swiper.js. Otherwise defaults to swiper.esm.js which uses ES6 syntax.
import Swiper from 'swiper/swiper.esm.js';
import modaal from 'modaal/dist/js/modaal'

export default class Modal{
  constructor($el) {
    this.$el = $el
    $(this.$el).modaal({
      custom_class: $(this.$el).attr('data-modal-class'),
      hide_close: true,
      after_open: (thisModal) => {
        var swiper = new Swiper('.swiper-container', {
          centeredSlides: false,
          loop: false,
          mode:'horizontal',
          observer: true,
          observeParents: true,
          pagination: {
            clickable: true,
            el: $(thisModal).find('.swiper-pagination'),
            type: 'bullets',
          },
          slidesPerGroup: 1,
          slidesPerView: 1,
          slideVisibleClass: 'swiper-slide-visible',
          spaceBetween: 20,
          speed: 400,
          watchSlidesVisibility : true
        })
      }
    })
    $('.cancel').on('click', (e) => {
      e.preventDefault();
      $(this.$el).modaal('close')
    })
  }
}
