'use strict';
// Require swiper.js. Otherwise defaults to swiper.esm.js which uses ES6 syntax.
// It is required to import sub modules in the new Swiper 9 esm.
import Swiper, {Navigation, Pagination} from 'swiper/swiper.esm.js';
import $ from 'jquery';
import throttle from 'raf-throttle'

export default class CardCarousel {
  constructor(el) {
    this.$el = $(el);
    this.$container = this.$el.find('.swiper-container');
    this.$outerContainer = this.$el.find('.swiper-outer-container');
    this.isDesktop = false;
    this.loadedClass = 'loaded';
    this.resizingClass = 'resizing';

    this.setup();
  }

  getOpts() {
    const self = this;

    this.defaultOpts = {
      // configure Swiper to use modules.
      modules: [Navigation, Pagination],

      autoHeight: true,
      pagination: {
        el: this.$el.find('.swiper-pagination'),
        type: 'bullets',
        clickable: true
      },
      navigation: {
        nextEl: this.$el.find('.swiper-button-next'),
        prevEl: this.$el.find('.swiper-button-prev')
      },
      centeredSlides: false,
      effect: 'slide',
      init: false,
      loop: true,
      slidesPerGroup: 3,
      slidesPerView: 3,
      spaceBetween: 25,
      speed: 400,
      watchSlidesVisibility: true,
      on: {
        init: function() {
          self.isDesktop = $(window).outerWidth() > 990;

          self.$outerContainer.addClass(self.loadedClass);

          this.updateAutoHeight();

          $(window).on('resize', throttle(() => {
            if($(window).outerWidth() > 990){
              if (!self.isDesktop) {
                self.reset()
                self.isDesktop = true;
              }
            } else {
              self.isDesktop = false;
            }
          }))
        },
        paginationRender: function() {
          let $controls = $(this.pagination.$el.closest('.swiper-controls'))

          this.pagination.bullets.length <= 1
            ? $controls.hide()
            : $controls.show()
        }
      },
      breakpoints: {
        // when window width is <= 600px
        600: {
          slidesPerGroup: 1,
          slidesPerView: 1
        },
        990: {
          slidesPerGroup: 2,
          slidesPerView: 2
        }
      }
    };

    // Get Opts passed in via data attr, will override defaults
    this.userOpts = this.$el.data('opts') || {};

    return Object.assign({}, this.defaultOpts, this.userOpts);
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
    let opts = this.getOpts();

    this.swiperInstance = new Swiper(this.$container, opts);
    this.swiperInstance.init();
    this.addCustomResize();
  }

  addCustomResize() {
    // Replaces Swiper's updateAutoHeight function with a modified version
    // which sets slide height to 'auto' before calculating and setting new height.

    /* eslint-disable */
    this.swiperInstance.updateAutoHeight = () => {
      var swiper = this.swiperInstance;
      var activeSlides = [];
      var newHeight = 0;
      var i;

      // remove loaded class to get natural height
      this.$outerContainer.addClass(this.resizingClass);

      // Find slides currently in view
      if (swiper.params.slidesPerView !== 'auto' && swiper.params.slidesPerView > 1) {
        for (i = 0; i < swiper.params.slidesPerView; i += 1) {
          var index = swiper.activeIndex + i;
          if (index > swiper.slides.length) { break; }
          activeSlides.push(swiper.slides.eq(index)[0]);
        }
      } else {
        activeSlides.push(swiper.slides.eq(swiper.activeIndex)[0]);
      }

      // Find new height from highest slide in view
      for (i = 0; i < activeSlides.length; i += 1) {
        if (typeof activeSlides[i] !== 'undefined') {
          var height = activeSlides[i].offsetHeight;
          newHeight = height > newHeight ? height : newHeight;
        }
      }

      // re-add loaded class
      this.$outerContainer.removeClass(this.resizingClass);

      // Update Height
      if (newHeight) { swiper.$wrapperEl.css('height', (newHeight + "px")); }
    }
    /* eslint-enable */
  }

}
