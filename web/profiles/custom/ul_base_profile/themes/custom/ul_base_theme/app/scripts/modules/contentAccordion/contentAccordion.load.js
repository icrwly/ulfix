'use strict';
import Module from './contentAccordion.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el);
  });
}
