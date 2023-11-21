'use strict';
// It is required to import sub modules in the new Swiper 9 esm.
import Swiper, {Autoplay, Navigation, Pagination, Thumbs} from 'swiper/swiper.esm.js';
import $ from 'jquery';
// import Swiper from 'swiper/swiper.esm.js';

export default class CarouselSlider {
  constructor($el){
    this.$el = $el;

    $(window).on('load', () => {
      this.destroy();
      this.setup();
    });

  }

  destroy(){
    if (typeof this.swiperInstance !== 'undefined' && this.swiperInstance !== null) {
      this.swiperInstance.destroy(true, true);
      this.swiperInstance = null;
    }
  }
  setup(){
    const sliderTop = document.querySelectorAll('.carousel-top');
    const sliderTopThumbs = document.querySelectorAll('.carousel-bottom');
    var i;
    const sliderNextbtn = document.querySelectorAll('.paragraph--carousel .swiper-button-next');
    const sliderPrevbtn = document.querySelectorAll('.paragraph--carousel .swiper-button-prev');
    const slider_t = [];
    const slider_b = [];
    var j;
    for (j = 0;j < sliderNextbtn.length;j++) {
      sliderNextbtn[j].classList.add('btn-next-' + j);
      sliderPrevbtn[j].classList.add('btn-prev-' + j);
    }
    for (i = 0;i < sliderTop.length;i++) {
      sliderTop[i].classList.add('carousel-top-' + i);
      sliderTopThumbs[i].classList.add('carousel-bottom-' + i);
      var countSlides = $('.carousel-bottom-' + i +' .swiper-slide:not(.swiper-slide-duplicate)').length;
      var right_rail = $('.col-aside').length;
      var slides_view;
      var free_mode;
      var space_between;

      if (right_rail > 1 && countSlides > 3) {
        slides_view = 3;
        free_mode = true;
        space_between = 5;
      } else {
        slides_view = countSlides;
        free_mode = false;
        space_between = 10;
      }
      var autoPlay = '10000';
      if (countSlides < 2) {
        autoPlay = '9999999';
        $('.btn-next-' + i).css({'display':'none'});
        $('.btn-prev-' + i).css({'display':'none'});
      }

    slider_b[i] = new Swiper('.carousel-bottom-' + i, {
      rewind: true,
      allowTouchMove: false,
      spaceBetween: space_between,
      slidesPerView: countSlides,
      disableOnInteraction: false,
      freeMode: false,
      watchSlidesProgress: false,
    });

    slider_t[i] = new Swiper(".carousel-top-" + i, {
        modules: [Autoplay, Navigation, Pagination, Thumbs],

        direction: 'horizontal',
        rewind: true,
        // Navigation arrow
        navigation: {
          nextEl: '.btn-next-' + i,
          prevEl: '.btn-prev-' + i,
        },
        autoplay: {
          delay: autoPlay,
        },
        pagination: {
          el: ".swiper-pagination-" + i,
          type: 'bullets',
          clickable: true,
        },

        disableOnInteraction: false,
        watchSlidesProgress: false,
        watchOverflow: false,
        autoHeight: false,
        centeredSlides: false,
        threshold: 10,
        effect: 'slide',
        freeMode: false,
        slidesPerGroup: 1,
        slidesPerView: 1,
        spaceBetween: 400,
        speed: 400,

        thumbs: {
          swiper: slider_b[i],
          thumbsContainerClass: "slides_" + countSlides,
          slideThumbActiveClass: "thumb_active_slide",
          multipleActiveThumbs: false
        },
    });
  }

  $(".carousel-top").each(function(elem, target){
    var swp = target.swiper;
    $(this).hover(function() {
      swp.autoplay.stop();
      $(this).next('.carousel-bottom').addClass('active_carousel');
      $(".active_carousel .swiper-bullet-active").addClass("hover");
      }, function() {
        swp.autoplay.start();
        $(".active_carousel .swiper-bullet-active").removeClass("hover");
        $(this).next('.carousel-bottom').removeClass('active_carousel');
      });
    });

  }
}
