'use strict'
import $ from 'jquery';

export default class TabScroll {
  constructor($el) {
    this.$el = $el
    this.$tabLink = $(this.$el).find('a')
    this.activeClass = 'active'

    this.$tabLink.on('click', (e) => {
      this.scrollControl(e)
      this.classControl(this.$tabLink, e)
    })
  }

  classControl(links, e) {
    links.removeClass(this.activeClass);
    $(e.currentTarget).addClass(this.activeClass)
  }

  scrollControl(e) {
    let selector = $(e.currentTarget).attr('href'),
    $target = $(selector)
    if($target.length) {
      const globalHeight = $('.global-header').height()
      $('html, body').animate({
        scrollTop: $target.first().offset().top - globalHeight
      }, 500)
    }
  }

}
