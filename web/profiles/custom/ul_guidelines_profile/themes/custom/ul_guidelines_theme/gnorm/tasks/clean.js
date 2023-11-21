const changed = require('gulp-changed'),
  config = require('../config'),
  gulp = require('gulp')

gulp.task('copy', ['copy:scripts', 'copy:fonts', 'copy:favicon'])

gulp.task('copy:scripts', function() {
  return gulp.src(config.scripts.libsSrc)
    .pipe(changed(config.scripts.libsDest))
    .pipe(gulp.dest(config.scripts.libsDest))
})

gulp.task('copy:fonts', function() {
  return gulp.src(`${config.fonts.src}/**/*`)
    .pipe(changed(config.fonts.clean))
    .pipe(gulp.dest(config.fonts.clean))
})

gulp.task('copy:favicon', function() {
  return gulp.src(config.favicon.src)
    .pipe(changed(config.favicon.dest))
    .pipe(gulp.dest(config.favicon.dest))
})
