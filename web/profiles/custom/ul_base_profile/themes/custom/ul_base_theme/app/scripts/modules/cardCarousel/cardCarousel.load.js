'use strict';
import Module from './cardCarousel.main';

export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
