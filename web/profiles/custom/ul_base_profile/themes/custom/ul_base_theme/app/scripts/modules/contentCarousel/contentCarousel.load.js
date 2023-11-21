'use strict';
import Module from './contentCarousel.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
