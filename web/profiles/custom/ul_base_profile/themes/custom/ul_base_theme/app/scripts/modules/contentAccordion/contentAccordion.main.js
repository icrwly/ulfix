/**
 * Paragraph type "content accordion" scripts.
 */

import $ from 'jquery'

var contentAccordionLoaded = false;

export default class contentAccordion {
  constructor() {
    if(!contentAccordionLoaded) {
      this.setup()
    }
  }
  setup() {
    $('.p-accordion > dd').hide()
    $('.p-accordion > dt > span').removeClass('icon-minus').addClass('icon-plus')
    $('.p-accordion > dt').click(function() {
      if( $(this).hasClass('open') ){
        $(this).removeClass('open').next().slideUp()
        $(this).find('span').removeClass('icon-minus').addClass('icon-plus')
      } else {
        $(this).addClass('open').next().slideDown()
        $(this).find('span').removeClass('icon-plus').addClass('icon-minus')
      }
      return false
    })
    contentAccordionLoaded = true;
  }
}
