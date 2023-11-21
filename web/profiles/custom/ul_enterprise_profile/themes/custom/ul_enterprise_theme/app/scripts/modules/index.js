import SampleModule from './sampleModule/sampleModule.main';

export default {
  init: function() {
    const dataModules = document.querySelectorAll('[data-module]')
    for (const dataModule of dataModules) {
      const name = dataModule.dataset.module
      try {
        const module = this.modules[name](dataModule)
      } catch (e) {
        console.warn(`Data module ${name} not defined`)
      }
    }
  }
}
