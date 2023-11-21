'use strict';
import Module from './componentsFilter.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
