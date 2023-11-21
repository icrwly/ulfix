'use strict'

import $ from 'jquery'
import PubSub from 'pubsub-js'
import throttle from 'raf-throttle'

export default class primaryNav {
  constructor(el) {

    this.$el = $(el)
    this.$links = this.$el.find('.has-mobile-menu')
    this.$backButtons = this.$el.find('.mobile-menu__back')
    // Fix ticket UL-3175
    this.bindEventListeners()

    this.$backButtons.each((i, e) => {
      $(e).on('click', (e) => {
        this.close($(e.currentTarget))
        e.preventDefault()
      })
    })

    this.$links.each((i, e) => {
      const $arrowButton = $('<a href="#"></a>')
        .addClass('mobile-menu__button icon-caret-right-solid')
        .on('click', (e) => {
          this.open($(e.currentTarget))
          e.preventDefault()
        })

      $(e).append($arrowButton)
    })

    const subscriber = (msg, data) => {
      this.closeAll()
    }

    const token = PubSub.subscribe('ui.mobileMenu.close', subscriber);
  }

  open($el) {
    $el.closest('.menu-list').addClass('is-active')
    $el.closest('.menu-item').addClass('is-active')

  }

  close($el) {
    $el.closest('.menu-list').removeClass('is-active')
    $el.closest('.menu-item').removeClass('is-active')
  }

  closeAll() {
    this.$el.find('.is-active').removeClass('is-active')
  }

  /**
   * Purpose: To hide the mobile sub-menu box if window-size >= 992
   * Ticket:  UL-3175
   * @returns {void}
   */
  bindEventListeners() {
    $(window).on('resize', throttle(() => {
      if ($(window).width() >= 992) {
        this.closeAll()
      }
    }))
  }
}
