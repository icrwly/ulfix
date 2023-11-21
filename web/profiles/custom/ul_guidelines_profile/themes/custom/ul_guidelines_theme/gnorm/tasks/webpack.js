const config = require('../config').scripts,
  gulp = require('gulp'),
  gutil = require('gulp-util'),
  UglifyJsPlugin = require('uglifyjs-webpack-plugin'),
  webpack = require('webpack'),
  webpackConfig = require('../webpack.config.js')

gulp.task('webpack', [])


// PRODUCTION
gulp.task('webpack:build', function(callback) {
  const myConfig = Object.create(webpackConfig)

  myConfig.plugins = myConfig.plugins.concat(
    new webpack.DefinePlugin({
      PRODUCTION: JSON.stringify(true),
      'process.env': {
        'NODE_ENV': JSON.stringify('production')
      }
    }),
    new UglifyJsPlugin({uglifyOptions: config.uglifyOptions})
  )

  webpack(myConfig, function(err, stats) {
    if (err) {
      throw new gutil.PluginError('webpack:build', err)
    }
    gutil.log('[webpack:build]', stats.toString({
      colors: true
    }))
    callback()
  })
})


// DEVELOPMENT
gulp.task('webpack:build-dev', function(callback) {
  const myDevConfig = Object.create(webpackConfig)
  myDevConfig.devtool = 'sourcemap'

  webpack(myDevConfig, function(err, stats) {
    if (err) {
      throw new gutil.PluginError('webpack:build-dev', err)
    }
    // gutil.log('[webpack:build]', stats.toString('verbose'))
    gutil.log('[webpack:build-dev]', stats.toString({
      colors: true
    }))
    callback()
  })
})
