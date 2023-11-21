import $ from 'jquery'

const TabletSize = 768

export default class MobileHeader{
  constructor(el) {
    this.el = el
    this.hamburger = $(el).find('[data-toggle]')
    this.nav = $(el).attr('data-target')
    this.activeClass = $(el).attr('data-active-class')

		$(this.hamburger).on('click', this.handleHamburgerClick.bind(this))
		$(window).on('resize', this.handleWindowResize.bind(this))
  }

  // Click function method for hamburger
  handleHamburgerClick(e) {
    e.preventDefault()
    if($(this.hamburger).attr('aria-expanded') === 'true') {
      $(this.hamburger).attr('aria-expanded', 'false')
      this.closeNav()
    } else {
      $(this.hamburger).attr('aria-expanded', 'true')
      this.openNav()
    }
  }

	handleWindowResize() {
		if($(window).width() >= TabletSize){
			this.resetNav()
		}
	}

  // Open navigation method
  openNav(){
    $(this.el).addClass(this.activeClass)
    const availHeight = $(window).height() - $(this.el).height()
    $(this.nav).height(availHeight)
    $(this.nav).slideDown(300)
  }

  // Close navigation method
  closeNav() {
    $(this.el).removeClass(this.activeClass)
    $(this.nav).slideUp(300)
  }

  // Resize function resets active class on hamburger
  // and remove any styles from mobile nav
  resetNav(){
    $(this.nav).attr('style', '')
    $(this.hamburger).attr('aria-expanded', 'false')
  }

}
