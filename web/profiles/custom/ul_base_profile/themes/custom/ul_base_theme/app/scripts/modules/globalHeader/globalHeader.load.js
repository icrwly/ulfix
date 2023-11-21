'use strict';
import Module from './globalHeader.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
