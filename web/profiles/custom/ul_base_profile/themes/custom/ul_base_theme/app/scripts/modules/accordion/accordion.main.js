// Rewrite code to fix the Uncaught Error:
// ES Modules may not assign module.exports or exports.*, Use ESM export syntax.
'use strict'

import $ from 'jquery';
import PubSub from 'pubsub-js'
import throttle from 'raf-throttle'

export default class Accordion {
  constructor(el) {
    this.$el = $(el)
    this.$target = this.getTarget()
    this.activeClass = 'active-trail'
    this.animatingClass = 'is-animating'
    this.speed = 500
    this.noTransitionAfterOpen = this.$el.data('no-transition-after-open') || false
    this.maxWidth = this.$el.data('max-width')
    this.minWidth = this.$el.data('min-width')
    this.group = this.$el.data('group')
    this.closeOnLoad = this.$el.data('close-on-load') || false
    this.showMultiple = this.$el.data('show-multiple') || false
    this.isSetup = false

    if (this.maxWidth || this.minWidth) {
      this.checkWidth()

      $(window).on('resize', throttle(() => {
        this.checkWidth()
      }))
    } else {
      this.setup()
    }
  }

  setup() {
    if (this.$target) {
      if (this.closeOnLoad){
        this.$target.hide()
        this.$el.removeClass(this.activeClass)
      } else if (!this.$el.hasClass(this.activeClass)) {
        this.$target.hide()
      }

      this.setupSubscriptions()
      this.bindEventListeners()
      this.isSetup = true
    }
  }

  destroy() {
    this.unbindEventListeners()
    // Remove inline 'display' property to avoid style conflicts
    this.$target.css({'display': ''});
    this.isSetup = false
  }

  unbindEventListeners() {
    this.$el.off('click.accordion')
  }

  bindEventListeners() {
    this.$el.on('click.accordion', (e) => {
      e.preventDefault()
      if (this.$el.hasClass('active-trail')) {
        this.hide(true)
      } else {
        // animate by default; if no-transition is set to true only animate if this is the first in its group to be shown
        let animate = this.noTransitionAfterOpen ? !$(`.active-trail[data-group="${this.group}"]`).length : true
        this.show(animate)
      }
    })
  }

  show(animate) {
    // Publish "show" event, passing this $el and group name. All accordions are listening.
    PubSub.publish('ui.accordion.show', {
      $el: this.$el,
      group: this.group
    });

    this.$el.addClass(this.activeClass)
    if (animate) {
      this.$el.addClass(this.animatingClass)
      this.$target.slideDown(this.speed, () => {
        this.$el.removeClass(this.animatingClass)
      })
    } else {
      this.$target.show()
    }
  }

  hide(animate){
    this.$el.removeClass(this.activeClass)
    if (animate) {
      this.$el.addClass(this.animatingClass)
      this.$target.slideUp(this.speed, () => {
        this.$el.removeClass(this.animatingClass)
      })
    } else {
      this.$target.hide()
    }
  }

  checkWidth() {
    if ($(window).outerWidth() <= this.maxWidth ||
        $(window).outerWidth() >= this.minWidth) {
      !this.isSetup && this.setup()
    } else {
      this.isSetup && this.destroy()
    }
  }

  getTarget() {
    let target = null
    // Try target seclector. Otherwise try next element.
    // If neither are present, leave as null and never setup the accordion
    if ($(this.$el.data('target')).length) {
      target = $(this.$el.data('target'))
    } else if (this.$el.next().length) {
      target = this.$el.next()
    }
    return target
  }

  setupSubscriptions() {
    const subscriber = (msg, data) => {
      switch (msg) {
        case 'ui.accordion.show': {
          const isSameGroup = data.group && data.group.length && this.group == data.group
          // Hide this accordion if element shown is in the same group and showMultiple is false.
          if (isSameGroup && data.$el !== this.$el && !this.showMultiple) {
            // set animate to true if no-transition is false
            this.hide(!this.noTransitionAfterOpen)
          }
          break
        }
        default:
      }
    }

    const token = PubSub.subscribe('ui.accordion', subscriber);
  }
}
