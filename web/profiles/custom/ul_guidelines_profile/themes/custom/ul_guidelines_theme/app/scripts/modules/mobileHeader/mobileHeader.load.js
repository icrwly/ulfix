'use strict';
import Module from './mobileHeader.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
