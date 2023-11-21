'use strict'
import Module from './componentReveal.main'
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
