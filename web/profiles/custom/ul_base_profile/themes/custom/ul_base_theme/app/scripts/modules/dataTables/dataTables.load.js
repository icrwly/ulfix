'use strict';
import Module from './dataTables.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
