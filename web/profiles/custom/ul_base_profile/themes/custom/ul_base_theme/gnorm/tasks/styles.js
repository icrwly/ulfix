const browserSync = require('browser-sync'),
  config = require('../config').styles,
  gulp = require('gulp'),
  gutil = require('gulp-util'),
  cssnano = require('cssnano'),
  plumber = require('gulp-plumber'),
  postcss = require('gulp-postcss'),
  sass = require('gulp-sass'),
  sassGlob = require('gulp-sass-glob'),
  sourcemaps = require('gulp-sourcemaps');


const plugins = [
  cssnano({
    safe: true,
    autoprefixer: {
      // CSSNano's implementation of Autoprefixer only removes unnecessary
      // prefixes by default.  `add: true` fixes that.
      // To define browser support, see package.json > browserslist.
      add: true
    },
    filterPlugins: false,
    discardComments: {
      removeAll: true
    },
    zindex: false,
    options: {
      sourcemap: true
    }
  })
];

const stylesTask = function(src){
  return function(){
    return gulp
      .src(src)
      .pipe(plumber({
        errorHandler: function(error) {
          gutil.log(error.message);
          this.emit('end');
        }
      }))
      .pipe(sourcemaps.init())
      .pipe(sassGlob())
      .pipe(
        sass({
          includePaths: ['./node_modules/']
        })
      )
      .pipe(postcss(plugins))
      .pipe(sourcemaps.write('.'))
      .pipe(plumber.stop())
      .pipe(gulp.dest(config.dest))
      .pipe(browserSync.reload({
        stream: true
      }))
  }
}

gulp.task('styles:watch', stylesTask(config.watchSrc))
gulp.task('styles:build', stylesTask(config.src))

// Notes:
//
// CSSnano options documentation:
//   http://cssnano.co/options/
//   http://cssnano.co/optimisations/
//
// PostCSS plugins and options:
//   https://github.com/postcss/gulp-postcss
//   https://github.com/postcss/postcss#plugins
