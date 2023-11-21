'use strict';
import Module from './sticky.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
