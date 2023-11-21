// Rewrite code to fix the Uncaught (in promise) TypeError:
// Module is not a constructor.
'use strict';
import Module from './accordion.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
