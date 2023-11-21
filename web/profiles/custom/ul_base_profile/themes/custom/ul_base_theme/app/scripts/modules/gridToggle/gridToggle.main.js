'use strict'

import $ from 'jquery';

export default class gridToggle {
  constructor(el) {
    this.$el = $(el)
    this.viewType = 'grid'
    this.$toggleButton = $('<button></button>').addClass('button button--red').text('Switch View')
    this.$items = this.$el.find('[data-item-grid-class]')

    this.$toggleButton.on('click', () => {
      this.$items.each((key, value) => {
        let itemGridClass = $(value).data('item-grid-class')
        let itemListClass = $(value).data('item-list-class')
        if (this.viewType == 'grid') {
          $(value).removeClass(itemGridClass).addClass(itemListClass)
        } else {
          $(value).removeClass(itemListClass).addClass(itemGridClass)
        }
      })

      this.viewType = this.viewType == 'grid' ? 'list' : 'grid'
    })

    this.$el.before(this.$toggleButton)
  }
}
