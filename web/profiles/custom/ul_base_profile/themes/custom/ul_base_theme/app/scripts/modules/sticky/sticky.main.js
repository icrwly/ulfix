import $ from 'jquery'
import Stickyfill from 'stickyfilljs'
import enquire from 'enquire.js'

export default class Sticky {
  constructor(el){
    this.$el = $(el)
    this.breakpoint = this.$el.data('breakpoint')
    if(typeof this.breakpoint != 'undefined'){
      enquire.register(`screen and (${this.breakpoint})`, {
        match: () => {
          this.setup()
        },
        unmatch: () => {
          this.destroy()
        }
      })
    }
  }
  setup(){
    Stickyfill.add(this.$el)
  }
  destroy(){
    Stickyfill.remove(this.$el)
  }
}
