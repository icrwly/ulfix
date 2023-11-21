const config = require('./config').scripts,
  path = require('path'),
  webpack = require('webpack');

// Replace uglifyjs with terser.
const TerserPlugin = require("terser-webpack-plugin");

module.exports = {
  cache: true,
  context: path.resolve('app'),
  mode: 'development',
  entry: {
    app_guidelines: './scripts/app_guidelines'
  },
  output: {
    path: config.path,
    // leaving publicPath empty here, setting it via __webpack_public_path__ in app.js
    publicPath: `${config.publicPath}/build/scripts/`,
    filename: '[name].built.js',
    chunkFilename: '[name].bundle.js?[hash:6]',
    libraryTarget: 'umd'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: [/libs/, /node_modules/],
        enforce: 'pre',
        loader: 'eslint-loader'
      },
      {
        test: /\.js$/,
        exclude: [/libs/, /node_modules/],
        loader: 'babel-loader',
        options: {
          // if Browserslist in package.json is not getting picked up here,
          // specify them via `targets`
          presets: [['@babel/preset-env', {
            targets: {
              browsers: [
                "> .25%"
              ]
            },
            // debug: true
          }]]
        }
      },
      {
        test: /\.twig$/,
        use: [
          {
            loader: path.resolve('gnorm/loaders/twig/index.js')
          }
        ]
      }
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      jQuery: 'jquery',
      $: 'jquery'
    })
    // Use this if you want to chunk shared libraries
    // new webpack.optimize.CommonsChunkPlugin('shared.js')
  ],

  // Replace modules by other modules or paths.
  // https://webpack.github.io/docs/configuration.html#resolve
  resolve: {
    // alias: {}
    extensions: ['.js', '.json']
  },

  optimization: {
    minimize: true,
    minimizer: [new TerserPlugin()],
  }
}
