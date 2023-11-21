'use strict'
import $ from 'jquery'

export default class Pagination{
  constructor(el) {
    this.$el = $(el)
    this.isOpen = false
    let $select = this.$el.find('.pager__select')
    let $activePage = this.$el.find('.pager__active-page')

    $activePage.click((e) => {
      e.preventDefault()
      this.isOpen ? $select.removeClass('is-open') : $select.addClass('is-open')
      this.isOpen = !this.isOpen
    });

  }
}
