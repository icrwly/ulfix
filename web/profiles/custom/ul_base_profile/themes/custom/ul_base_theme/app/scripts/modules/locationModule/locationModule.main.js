import $ from 'jquery';

export default class LocationModule {
  constructor() {
    this.$allPanels = $('.loc-accord-body')
    this.setup()
  }

  setup() {
    $('.loc-accord-trigger').click(function(){
      var target = $(this).parent().parent().next()
      if(target.hasClass('open')){
        target.removeClass('open').slideUp()
        target.prev().find('.icon-plusmin').addClass('icon-plus').removeClass('icon-minus')
      } else {
        target.addClass('open').slideDown()
        target.prev().find('.icon-plusmin').addClass('icon-minus').removeClass('icon-plus')
      }
      return false
    })

    this.$allPanels.hide()
  }
}
