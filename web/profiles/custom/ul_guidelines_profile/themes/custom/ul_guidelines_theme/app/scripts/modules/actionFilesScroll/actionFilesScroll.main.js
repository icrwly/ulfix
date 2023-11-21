'use strict'
import $ from 'jquery'

export default class ActionFilesScroll{
  constructor($el) {
    this.$el = $el
    this.scrollElement = $(this.$el).attr('data-scroll')
    $(this.$el).on('click', (e) => {
      e.preventDefault()
      document.querySelector(this.scrollElement).scrollIntoView({
        block: 'end',
        behavior: 'smooth'
      })
    })
  }
}
