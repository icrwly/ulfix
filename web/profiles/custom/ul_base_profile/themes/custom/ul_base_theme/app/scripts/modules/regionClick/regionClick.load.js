'use strict';
import Module from './regionClick.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
