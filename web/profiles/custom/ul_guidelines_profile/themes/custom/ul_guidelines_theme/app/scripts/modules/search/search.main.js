import $ from 'jquery'

// import CardSection from '../../../../templates/4_regions/section--cards.twig'
// import GlobalData from '../../../json/global.json'
// import ListingSection from '../../../../templates/4_regions/section--listing-items.twig'
import {getUrlParameter} from '../../utils/urlHelpers'

const Views = {
	results: 'results',
	search: 'search'
}

export default class Search {
	constructor(el) {
		this.el = el
		this.form = $(el).find('#views-exposed-form-search-results-search-block')
		this.input = $(this.form).find('input[name="keyword"]')
		this.placeholder = $(this.input).data('placeholder') || 'What are you searching for?'
		this.recent = $(el).find('.search-recent')
		this.clear = $(el).find('[data-clear]')
		this.views = $(el).find('[data-view]')
		this.activeView = ''

		this.results = {
			featured: $(el).find('.search-results__featured'),
			pages: $(el).find('.search-results__page'),
			assets: $(el).find('.search-results__assets')
		}

		$(this.clear).click(this.handleClearClick.bind(this))
		$(this.input).keyup(this.handleInputKeyUp.bind(this))
		$(this.input).focus(this.handleInputFocus.bind(this))
		$(this.input).blur(this.handleInputBlur.bind(this))
		// uncommen for local testing of search results rendering
		// $(this.form).submit(this.handleFormSubmit.bind(this))

		this.setPlaceholder()

		const keyword = getUrlParameter('keyword') || null

		if (keyword) {
			this.setView(Views.results)
		} else {
			this.setView(Views.search)
		}
	}
	setPlaceholder() {
		$(this.input).attr('placeholder', this.placeholder)
	}
	handleInputFocus() {
		$(this.input).removeAttr('placeholder')
		this.showRecent()
	}
	handleInputBlur() {
		const value = $(this.input).val()

		if (!value || value === '') {
			this.setPlaceholder()
			this.hideClear()
		}

		this.hideRecent()
	}
	handleInputKeyUp() {
		const value = $(this.input).val()

		if (!value || value === '') {
			this.showRecent()
			this.hideClear()
			return
		}

		this.showClear()
		this.hideRecent()
	}
	handleClearClick() {
		$(this.input).val('')
		$(this.input).focus()
		this.hideClear()
	}
	handleFormSubmit(e) {
		e.preventDefault()
		const search = $(this.input).val()

		if (!search || search === '') {
			return
		}

		this.clearSearchResults()

		this.getSearchResults(search)
			.then(this.renderResults.bind(this))
			.then(() => this.setView(Views.results))
	}
	clearSearchResults() {
		$(this.results.featured).html('')
		$(this.results.pages).html('')
		$(this.results.assets).html('')
	}
	// getSearchResults(search) {
	// 	return new Promise((resolve, reject) => {
	// 		resolve(GlobalData.searchData.results)
	// 	})
	// }
	// renderResults(results) {
	// 	$(this.results.featured).html(CardSection({section: results.featured}))
	// 	$(this.results.pages).html(ListingSection({section: results.pages}))
	// 	$(this.results.assets).html(ListingSection({section: results.assets}))
	// }
	showRecent() {
		const count = $(this.recent).data('count')
		if (count <= 0) {
			return
		}

		$(this.recent).slideDown()
	}
	hideRecent() {
		$(this.recent).slideUp()
	}
	showClear() {
		$(this.clear).fadeIn()
	}
	hideClear() {
		$(this.clear).fadeOut()
	}
	setView(view) {
		if (this.activeView === view) {
			return
		}

		const v = $(this.el).find(`[data-view="${view}"]`)

		$(this.views).fadeOut(150, () => $(v).fadeIn(150, () => this.activeView = view))
	}
}
