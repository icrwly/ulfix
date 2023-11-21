'use strict'
import $ from 'jquery'
export default class BackButton{
  constructor($el) {
    this.$el = $el
    $(this.$el).on('click', (e) => {
      e.preventDefault()
      window.history.back()
    })
  }
}
