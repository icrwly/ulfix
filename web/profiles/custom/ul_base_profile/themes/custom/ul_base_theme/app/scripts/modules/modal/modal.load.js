'use strict';
import Module from './modal.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
