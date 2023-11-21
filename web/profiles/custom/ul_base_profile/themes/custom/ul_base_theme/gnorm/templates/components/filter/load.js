'use strict';

module.exports = ($el) => {

  require.ensure([], (require) => {

    var Module = require('./componentsFilter.main');
    new Module($el);

  });

};
