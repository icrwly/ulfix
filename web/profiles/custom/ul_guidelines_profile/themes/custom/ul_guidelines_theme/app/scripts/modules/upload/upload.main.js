'use strict'
import $ from 'jquery'

export default class Upload {
  constructor($el) {
		this.$el = $el;
		this.input = $(this.$el).find('input');
		this.trigger = $(this.$el).find('[data-trigger]');

		this.trigger.click(() => $(this.input).click());
  }
}
