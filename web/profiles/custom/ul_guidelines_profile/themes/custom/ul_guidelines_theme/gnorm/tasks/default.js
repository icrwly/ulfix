const gulp = require('gulp')

//Builds dev and starts browsersync
gulp.task('default', ['watch'])

//Builds dev
gulp.task('dev', ['webpack:build-dev', 'styles', 'images', 'twig:build-dev', 'copy'])

//Builds dist
gulp.task('dist', ['browserSync:dist'])

//Builds dist and starts browsersync
gulp.task('build', ['webpack:build', 'styles', 'images', 'twig:build', 'copy'])
