// Rewrite code to fix the Uncaught Error:
// ES Modules may not assign module.exports or exports.*, Use ESM export syntax.
// Change all modales JS under the directory ./modules/**/ => *.load.js & *.main.js.
import accordion from './accordion/accordion.load';
import cardCarousel from './cardCarousel/cardCarousel.load';
import carouselSlider from './carouselSlider/carouselSlider.load';
import componentsFilter from './componentsFilter/componentsFilter.load';
import contentAccordion from './contentAccordion/contentAccordion.load';
import contentCarousel from './contentCarousel/contentCarousel.load';
import dataTables from './dataTables/dataTables.load';
import globalHeader from './globalHeader/globalHeader.load';
import gridToggle from './gridToggle/gridToggle.load';
import heroCarousel from './heroCarousel/heroCarousel.load';
import introCarousel from './introCarousel/introCarousel.load';
import locationModule from './locationModule/locationModule.load';
import modal from './modal/modal.load';
import pagination from './pagination/pagination.load';
import primaryNav from './primaryNav/primaryNav.load';
import regionClick from './regionClick/regionClick.load';
import sticky from './sticky/sticky.load';
import tabScroll from './tabScroll/tabScroll.load';

export default {
  init: function() {
    const dataModules = document.querySelectorAll('[data-module]')
    for (const dataModule of dataModules) {
      const name = dataModule.dataset.module;

      try {
        const module = this.modules[name](dataModule)
      } catch (e) {
        // Check if the module is in ul_base_theme only;
        // No message for the module in ul_gidelines_theme or ul_enterprise_theme.
        if (this.modules[name]) {
          console.warn(`Base Theme: Data module ${name} not defined`)
        }
      }
    }
  },
  modules: {
    accordion,
    cardCarousel,
    carouselSlider,
    componentsFilter,
    contentAccordion,
    contentCarousel,
    dataTables,
    globalHeader,
    gridToggle,
    heroCarousel,
    introCarousel,
    locationModule,
    modal,
    pagination,
    primaryNav,
    regionClick,
    sticky,
    tabScroll
  }
}
