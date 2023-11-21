'use strict';
import Module from './introCarousel.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
