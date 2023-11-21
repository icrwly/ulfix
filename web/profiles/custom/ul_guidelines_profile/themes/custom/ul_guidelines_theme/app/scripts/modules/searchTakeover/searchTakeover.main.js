import $ from 'jquery'

export default class SearchTakeover {
	constructor(el) {
		this.el = el
		this.search = $(el).find('.search')
		this.triggers = $('body').find('a[data-search-toggle]')
		this.close = $(el).find('[data-close]')
		this.bodyClass = $(el).data('body-class')
		this.activeClass = $(el).data('active-class')
		this.ref = null

		$(this.triggers).click(this.handleTriggerClick.bind(this))
		$(this.close).click(this.handleCloseClick.bind(this))
	}
	handleTriggerClick(e) {
		e.preventDefault()
		const ref = $(e.target).data('search-position')
		this.ref = ref;
		this.showSearch(ref)
	}
	handleCloseClick(e) {
		e.preventDefault()
		this.hideSearch()
	}
	searchOpen() {
		return $(this.el).hasClass(this.activeClass);
	}
	showSearch(ref = this.ref) {
		const top = $(ref).offset().top + $(ref).outerHeight()
		const height = $(window).height() - $(ref).outerHeight()

		$('body').addClass(this.bodyClass)

		$(this.search).css('min-height', height)

		$(this.el)
			.css('top', top)
			.fadeIn(300)
			.css('height', height)
			.addClass(this.activeClass)

		setTimeout(() => {
			$(this.el).css('overflow', 'auto')
		}, 300);
	}
	hideSearch() {
		$('body').removeClass(this.bodyClass)
		$(this.el)
			.css('height', 0)
			.css('overflow', 'hidden')
			.removeClass(this.activeClass)
			.fadeOut(300)
	}
}
