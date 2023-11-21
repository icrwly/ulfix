import $ from 'jquery'

export default class NavMobile {
  constructor(el) {
    this.el = el
    this.pages = $(el).find('.nav--mobile__page')
    this.active = 1

    this.setupPages()
  }
  setupPages() {
    this.pages.each((i, page) => {
      const left = this.getSlideLeft(page)

      $(page).css('left', left)
      $(page).find('a[data-target]').each((i, l) => {
        if($(l).hasClass('nav--mobile__link--has-children') || $(l).hasClass('nav--mobile__page__breadcrumb')){
          this.setupLink(l)
        }
      })
      $(page).attr('data-active', $(page).data('level') === this.active)
    })
  }
  getSlideLeft(page) {
    const windowWidth = $(window).width()
    const level = $(page).data('level')
    const offset = level - this.active

    return offset * windowWidth
  }
  setupLink(link) {
    $(link).click((e) => {
      e.preventDefault()
      if($(e.currentTarget).hasClass('nav--mobile__page__breadcrumb')){
        this.goToPage($(link).data('target'), true)
      } else {
        this.goToPage($(link).data('target'))
      }
    })
  }
  goToPage(id, breadcrumb) {
    let page
    this.pages.each((index, p) => {
      if(breadcrumb){
        page = $('[data-target="' + id + '"]').closest('.nav--mobile__page')
      } else {
        if ($(p).attr('id') === id) {
          page = p;
        }
      }
    });

    this.transitionToPage(page)
  }
  transitionToPage(page) {
    this.active = $(page).data('level')
    $(page).attr('data-active', true)

    this.pages.each((index, p) => {
      const level = $(p).data('level')
      const parent = $(p).attr('id')

      if (level === this.active && parent !== $(page).attr('id')) {
        $(p).attr('data-active', false)
      }

      $(p).animate({
        left: this.getSlideLeft(p)
      }, {
        duration: 300
      })
    })
  }
}
