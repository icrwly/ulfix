'use strict';
import Module from './primaryNav.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
