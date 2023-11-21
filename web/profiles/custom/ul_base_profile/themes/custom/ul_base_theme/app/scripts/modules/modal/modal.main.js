import 'modaal/dist/js/modaal';
import $ from 'jquery';

export default class Modal {
  constructor(el){
    this.$el = $(el)

    let options = { }

    this.$el.modaal(options)
    $(el.hash).find('.cancel').on('click', (e) => {
      e.preventDefault()
      this.$el.modaal('close')
    })

  }
}
