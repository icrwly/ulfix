const config = require('../config').twig,
  gulp = require('gulp'),
  exec = require('child_process').exec,
  shellescape = require('shell-escape');

// Default command to run.  Passing a json encoded config.
var cmd = ['php', 'gnorm/scripts/twig.php', '-c', JSON.stringify(config)];

// PRODUCTION
gulp.task('twig:build', function () {
  'use strict';
  cmd.push('-b', 'TRUE');
  exec(shellescape(cmd), function (err, stdout, stderr) {
    if (err) {
      console.log(err)
    }
    console.log(stdout, stderr)
  });
});

// DEVELOPMENT
gulp.task('twig:build-dev', function () {
  'use strict';
  cmd.push('-b', 'FALSE');
  exec(shellescape(cmd), function (err, stdout, stderr) {
    if (err) {
      console.log(err)
    }
    console.log(stdout, stderr)
  });
});
