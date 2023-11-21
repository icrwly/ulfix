'use strict';
import $ from 'jquery'

export default class regionClick {
  constructor(el) {
    this.$el = $(el);
    let regionSelect = $(this.$el).find('select')
    let regionSelectId = regionSelect.attr('id');
    regionSelect.on('change', (e) => {
      let url = regionSelect.find('option:selected').val()
      window.location = url
    })
    document.getElementById(regionSelectId).selectedIndex = 0;
  }
}
