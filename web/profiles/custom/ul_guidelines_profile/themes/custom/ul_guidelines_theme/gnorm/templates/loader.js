module.exports = ($el) => {
  <% if ( typeof(async) !== 'undefined' ){ %>require.ensure([], (require) => { <% } %>
    const Module = require('./<%= name %>.main')
    new Module($el)
  <% if ( typeof(async) !== 'undefined' ){ %>}) <% } %>
}
