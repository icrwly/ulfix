'use strict';

import Swiper, {Autoplay} from 'swiper/swiper.esm.js';
import $ from 'jquery';

export default class IntroCarousel {
  constructor(el) {
    this.$el = $(el);
    this.setup();
    $(window).on('load', () => {
      this.destroy();
      this.setup();
    });
  }

  reset() {
    this.destroy()
    this.setup()
  }

  destroy() {
    if (typeof this.swiperInstance !== 'undefined' &&
      this.swiperInstance !== null) {
        $(window).off('optimizedResize')
        this.swiperInstance.destroy(true, true);
        this.swiperInstance = null;
    }
  }

  setup() {
    var slider_intro;
    slider_intro = new Swiper('.card-container .swiper-container', {
      // Use the module.
      modules: [Autoplay],
      autoplay: {
        delay: '4000',
      },
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      loop: true,
      slidesPerView: 1,
      initialSlide: 0,
      spaceBetween: 100,
      speed: 400,
      freeMode: false,
      slidesPerGroup: 1
    });
  }

}
