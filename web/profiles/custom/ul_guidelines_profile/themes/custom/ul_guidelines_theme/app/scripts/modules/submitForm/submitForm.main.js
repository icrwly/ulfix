'use strict'

import $ from 'jquery'

import {getUrlParameter} from '../../utils/urlHelpers';

export default class SubmitForm{
  constructor($el) {
    this.$el = $el
    this.nextButton = $(this.$el).find('.next-form')
    this.showFormClass = 'active'
    this.steps = $($el).find('li')
    this.progress = $($el).find('[data-progress]')
    this.activePage = getUrlParameter('step') || 1

    this.goToPage(this.activePage)
  }
  goToPage(page) {
    $(this.$el).find(`[data-form]`).removeClass(this.showFormClass);
    $(this.$el).find(`[data-form="${page}"]`).addClass(this.showFormClass);

    this.setupForm(page);
    this.goToStep(page - 1);
  }
  goToStep(step) {
    const totalSteps = this.steps.length - 1
    const width = step / totalSteps * 100;

    // Make sure not to go beyond the end
    if (width > 100) {
      return;
    }

    $(this.progress).animate({
      width: `${width}%`
    }, {
      duration: 500,
      complete: () => {
        $(this.steps)
          .filter((i) => i <= step)
          .addClass('active-step');
      }
    })
  }
  setupForm(page) {
    const forms = $(this.$el).find('form');
    forms.off('submit');

    const activePage = $(this.$el).find(`[data-form="${page}"]`);
    const activeForm = $(activePage).find('form').first();

    activeForm.on('submit', (e) => {
      // e.preventDefault();
      console.log('form submit');
    });

  }
}
