'use strict';
import Module from './tabScroll.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
