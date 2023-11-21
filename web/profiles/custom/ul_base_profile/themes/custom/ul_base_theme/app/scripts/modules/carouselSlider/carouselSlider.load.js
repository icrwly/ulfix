'use strict';
import Module from './carouselSlider.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
