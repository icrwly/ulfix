'use strict';
import Module from './dropdown.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  })
}
