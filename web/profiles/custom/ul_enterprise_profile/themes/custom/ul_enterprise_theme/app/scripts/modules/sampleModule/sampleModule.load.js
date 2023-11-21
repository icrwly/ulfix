import Module from './sampleModule.main';
export default ($el) => {
  require.ensure([], (require) => {
    new Module($el)
  })
}
