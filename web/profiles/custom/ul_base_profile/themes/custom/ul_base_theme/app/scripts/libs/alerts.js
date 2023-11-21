import './jquery.cookie.js'
import $ from 'jquery'
import once from 'jquery-once'

$('.alert').once('setupAlerts').each((i, e) => {

  class Alert {
    constructor(el) {
      this.$el = $(el)
      this.$closeButton = this.$el.find('.alert__close')
      this.$primaryButton = this.$el.find('.button--primary')
      this.hideOnScroll = this.$el.data('hide-on-scroll')
      this.hideOnTimer = this.$el.data('hide-on-timer')
      this.cookieName = this.$el.data('id')
      this.speed = 500
      this.scrollThreshold = 100

      this.bindEventListeners()

      if (!$.cookie(this.cookieName) || $.cookie(this.cookieName) == 'shown') {
        this.open()
      }
    }

    bindEventListeners() {
      this.$closeButton.on('click', (e) => {
        e.preventDefault();
        this.close()
      })

      this.$primaryButton.on('click', (e) => {
        e.preventDefault();
        this.close()
      })

      if (this.hideOnScroll) {
        const originalScrollPosition = $(window).scrollTop()
        $(window).on('scroll.closeAlert', (e) => {
          let currentScrollPosition = $(window).scrollTop()
          if (this.getDifference(originalScrollPosition, currentScrollPosition) >= this.scrollThreshold) {
            this.close()
            $(window).off('scroll.closeAlert')
          }
        })
      }

      if (this.hideOnTimer) {
        setTimeout(() => {
          this.close()
        }, this.hideOnTimer)
      }
    }

    open() {
      this.$el.slideDown(this.speed, (e) => {
        $.cookie(this.cookieName, 'shown')
      })
    }

    close() {
      this.$el.slideUp(this.speed, (e) => {
        $.cookie(this.cookieName, 'dismissed')
      })
    }

    getDifference(a, b) {
      return Math.abs(a - b);
    }

  }

  let a = new Alert(e);

});
