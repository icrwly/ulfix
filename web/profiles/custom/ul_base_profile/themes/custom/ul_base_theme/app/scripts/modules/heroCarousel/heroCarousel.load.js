'use strict';
import Module from './heroCarousel.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
