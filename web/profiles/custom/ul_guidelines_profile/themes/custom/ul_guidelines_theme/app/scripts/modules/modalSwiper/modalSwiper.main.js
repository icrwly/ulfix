'use strict'

import $ from 'jquery';
// Require swiper.js. Otherwise defaults to swiper.esm.js which uses ES6 syntax.
// import Swiper from 'swiper/dist/js/swiper.js';
import Swiper from 'swiper/swiper.esm.js';
import modaal from 'modaal/dist/js/modaal';

export default class ModalSwiper{
  constructor($el) {
    this.$el = $el
    $(this.$el).modaal({
      custom_class: $(this.$el).attr('data-modal-class'),
      after_open: function() {
        var swiper = new Swiper($('.modaal-wrapper.slider').find('.swiper-container'), {
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
        });
      },
    })
  }
}
