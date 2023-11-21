// Rewrite code to fix the Uncaught Error:
// ES Modules may not assign module.exports or exports.*, Use ESM export syntax.
// Change all modales JS under the directory ./modules/**/ => *.load.js & *.main.js.
import actionApproval from './actionApproval/actionApproval.load';
import actionFavorite from './actionFavorite/actionFavorite.load';
import actionFiles from './actionFiles/actionFiles.load';
import actionFilesScroll from './actionFilesScroll/actionFilesScroll.load';
import actionShare from './actionShare/actionShare.load';
import backButton from './backButton/backButton.load';
import dropdown from './dropdown/dropdown.load';
import mobileHeader from './mobileHeader/mobileHeader.load';
import modal from './modal/modal.load';
import modalSwiper from './modalSwiper/modalSwiper.load';
import navMobile from './navMobile/navMobile.load';
import search from './search/search.load';
import searchTakeover from './searchTakeover/searchTakeover.load';
import submitForm from './submitForm/submitForm.load';
import supportSubmit from './supportSubmit/supportSubmit.load';
import swiper from './swiper/swiper.load';
import upload from './upload/upload.load';

export default {
  init: function() {
    const dataModules = document.querySelectorAll('[data-module]');
    for (const dataModule of dataModules) {
      const name = dataModule.dataset.module;
      try {
        const module = this.modules[name](dataModule);
      } catch (e) {
        // Check if the module is in ul_gidelines_theme only;
        // No message for the module in ul_base_theme.
        if (this.modules[name]) {
          console.warn(`Guidelines: Data module ${name} not defined`);
        }
      }
    }
  },
  modules: {
    actionApproval,
    actionFavorite,
    actionFiles,
    actionFilesScroll,
    actionShare,
    backButton,
    dropdown,
    mobileHeader,
    modal,
    modalSwiper,
    navMobile,
    search,
    searchTakeover,
    submitForm,
    supportSubmit,
    swiper,
    upload
  }
}
