'use strict';
import Module from './pagination.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
