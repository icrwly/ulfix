'use strict';
import Module from './gridToggle.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
