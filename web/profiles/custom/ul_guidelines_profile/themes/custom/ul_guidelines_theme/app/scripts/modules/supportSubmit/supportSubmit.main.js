'use strict'

import $ from 'jquery'

export default class SupportSubmit{
  constructor($el) {
    this.$el = $el
    this.activeClass = 'active'
    this.activeStepClass = 'active-step'
    this.continueClick = $(this.$el).find('.form-page__footer input')
    this.stepCounter = $('.steps')
    this.continueClick.on('click', (e) => {
      e.preventDefault()
      this.nextForm($(e.currentTarget).closest('.form-page'))
      this.nextStep($(e.currentTarget).closest('.form-page').data('form'))
    })
  }
  nextForm(form) {
    form.removeClass(this.activeClass)
    form.next().addClass(this.activeClass)
  }
  nextStep(formID){
    this.stepCounter.find('li').eq(formID).addClass(this.activeStepClass)
  }
}
