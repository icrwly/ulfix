'use strict'
import $ from 'jquery'

export default class Dropdown {
  constructor(el){
    this.el = el;
		this.label = $(el).find('.dropdown__label');
		this.menu = $(el).find('.dropdown__menu');
		// $(this.el).hover(this.setActive.bind(this), this.setInactive.bind(this))
    $(this.el).on("mouseenter", this.setActive.bind(this)).on("mouseleave", this.setInactive.bind(this));
  }
	setActive() {
    $(this.menu).fadeIn();
	}
	setInactive() {
    $(this.menu).fadeOut();
	}
}
