/**
 * gulpfile.js
 * ===========
 *
 * Node JS: 16.19.1
 * NPM    : 8.19.3
 * Gulp   : CLI version: 2.3.0
 *          Local version: 4.0.2
 * webpack: 5.75.0
 *
 * Author: UL Web Team & gung.wang@ul.com
 * Update: Feb. 2023
 *
 * Update notes:
 *  - The code of `tasks` is coming from ./gnorm/tasks/*.js, and
 *  - all tasks are combined into this main script.
 *
 *  - Update the Node JS from 14 to 16 LTS.
 *  - Update dependent packages via `fin exec npm install <package-name>`.
 *  - Failed to update Node JS 18 LTS as the fatal errors in dependencies.
 */

var gulp = require('gulp');

// var requireDir = require('require-dir');
// Require all tasks in gnorm/tasks, including subfolders
// const requireDir = require('./gnorm/tasks');
// requireDir('./gnorm/tasks', {recurse: false});

// dependencies and constants

const browserSync = require('browser-sync');
const changed = require('gulp-changed'),
  config = require('./gnorm/config');

const _ = require('underscore'),
  argv = require('yargs').argv,
  gutil = require('gulp-util'),
  modify = require('gulp-modify'),
  prettify = require('gulp-jsbeautifier'),
  rename = require('gulp-rename'),
  template = require('gulp-template');

const plumber = require('gulp-plumber');

// Use the async function to import() intead of the require()
// which caused the error in NodeJS 16 & Gulp 4.
const startup = async () => {
  // @ts-ignore
  imagemin = (await import("gulp-imagemin")).default;
  // @ts-ignore
  imageminJpegtran = (await import("imagemin-jpegtran")).default;
  imageminPngquant = (await import("imagemin-pngquant")).default;
  mozjpeg = (await import('imagemin-mozjpeg')).default;
  pngquant = (await import('imagemin-pngquant')).default;
  gifsicle = (await import('imagemin-gifsicle')).default;
  svgo = (await import('imagemin-svgo')).default;
  console.log("===>>> const startup :: HTTP Server Started");
};

const cssnano = require('cssnano'),
  postcss = require('gulp-postcss'),
  sass = require('gulp-sass')(require('sass')),
  sassGlob = require('gulp-sass-glob'),
  sourcemaps = require('gulp-sourcemaps');

const exec = require('child_process').exec,
  shellescape = require('shell-escape');


const TerserPlugin = require('terser-webpack-plugin'),
  webpack = require('webpack'),
  webpackConfig = require('./gnorm/webpack.config.js')

/** From the ./gnorm/tasks/webpack.js */

// webpack.js

gulp.task('webpack', function () { });


// PRODUCTION
gulp.task('webpack:build', async function (callback) {

  const myConfig = Object.create(webpackConfig)

  myConfig.plugins = myConfig.plugins.concat(
    new webpack.DefinePlugin({
      PRODUCTION: JSON.stringify(true),
      'process.env': {
        'NODE_ENV': JSON.stringify('production')
      }
    }),
    new TerserPlugin()
  )

  webpack(myConfig, function (err, stats) {
    if (err) {
      throw new gutil.PluginError('webpack:build', err)
    }
    gutil.log('[webpack:build]', stats.toString({
      colors: true
    }))
    console.log("===>>> webpack:build END ");
    callback()
  })
})

// DEVELOPMENT
gulp.task('webpack:build-dev', async function (callback) {
  console.log("===>>> webpack:build-dev START");

  const myDevConfig = Object.create(webpackConfig)
  myDevConfig.devtool = 'source-map'

  webpack(myDevConfig, function (err, stats) {
    // console.log("===>>> webpack IN ");
    if (err) {
      throw new gutil.PluginError('webpack:build-dev', err)
    }
    // gutil.log('[webpack:build]', stats.toString('verbose'))
    gutil.log('[webpack:build-dev]', stats.toString({
      colors: true
    }))
    console.log("===>>> webpack:build-dev END ");
    callback()
  })
})

/** From the ./gnorm/tasks/styles.js */

// styles.js

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


gulp.task('styles', async () => {
  console.log("===>>> gulp.task.styles");
  return gulp
    .src(config.styles.src)
    .pipe(plumber({
      errorHandler: function (error) {
        gutil.log(error.message);
        this.emit('end');
      }
    }))
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(
      sass({
        includePaths: [ './node_modules/' ]
      })
    )
    .pipe(postcss(plugins))
    .pipe(sourcemaps.write('.'))
    .pipe(plumber.stop())
    .pipe(gulp.dest(config.styles.dest))
    .pipe(browserSync.reload({
      stream: true
    }))
})

// Notes:
//
// CSSnano options documentation:
//   http://cssnano.co/options/
//   http://cssnano.co/optimisations/
//
// PostCSS plugins and options:
//   https://github.com/postcss/gulp-postcss
//   https://github.com/postcss/postcss#plugins

/** From the ./gnorm/tasks/images.js */

// images.js

gulp.task('images', async () => {
  await startup();

  return gulp
    .src(config.images.src)
    // Ignore unchanged files
    .pipe(changed(config.images.dest))
    .pipe(
      plumber({
        errorHandler: function (error) {
          gutil.log(error.message);
          this.emit('end');
        }
      })
    )
    .pipe(
      imagemin(
        [
          gifsicle({
            interlaced: true
          }),
          svgo({
            multipass: true
          }),
          pngquant({
            quality: '60-80',
            speed: 2
          }),
          mozjpeg({
            dcScanOpt: 0,
            quality: 75,
            quantTable: 2
          })
        ],
        {
          verbose: true
        }
      )
    )
    .pipe(plumber.stop())
    .pipe(gulp.dest(config.images.dest));

});

/** From the ./gnorm/tasks/twig.js */

// twig.js

// Default command to run.  Passing a json encoded config.
var cmd = [ 'php', 'gnorm/scripts/twig.php', '-c', JSON.stringify(config.twig) ];

// PRODUCTION
gulp.task('twig:build', async () => {
  'use strict';
  cmd.push('-b', 'TRUE');
  exec(shellescape(cmd), function (err, stdout, stderr) {
    if (err) {
      console.log(err)
    }
    console.log(stdout, stderr)
  });
  console.log("===>>> gulp.task.twig:build");
});

// DEVELOPMENT
gulp.task('twig:build-dev', async () => {
  'use strict';
  cmd.push('-b', 'FALSE');
  exec(shellescape(cmd), function (err, stdout, stderr) {
    if (err) {
      console.log(err)
    }
    console.log(stdout, stderr)
  });
  console.log("===>>> gulp.task.twig:build-dev");
});

/** From the ./gnorm/tasks/copy.js */

// copy.js

gulp.task('copy:scripts', function () {
  return gulp.src(config.scripts.libsSrc)
    .pipe(changed(config.scripts.libsDest))
    .pipe(gulp.dest(config.scripts.libsDest))
});

gulp.task('copy:fonts', function () {
  return gulp.src(config.fonts.src)
    .pipe(changed(config.fonts.dest))
    .pipe(gulp.dest(config.fonts.dest))
});

gulp.task('copy:favicon', function () {
  return gulp.src(config.favicon.src)
    .pipe(changed(config.favicon.dest))
    .pipe(gulp.dest(config.favicon.dest))
});

gulp.task('copy', gulp.series('copy:scripts', 'copy:fonts', 'copy:favicon'), function () { });

//Builds dev
gulp.task('dev', gulp.series('webpack:build-dev', 'styles', 'images', 'twig:build-dev', 'copy'), function () { });

/** From the ./gnorm/tasks/browserSync.js */

// browserSync.js

gulp.task('browserSync', gulp.series('dev'), async function () {

  await browserSyncAsync();

  return new Promise(function (resolve, reject) {
    browserSync(config.browserSync);
    console.log("===>>> gulp.task.browserSync :: HTTP Server Started");
    resolve();
  });
});

//Builds dist and starts browsersync
gulp.task('build', gulp.series('webpack:build', 'styles', 'images', 'twig:build', 'copy'), function () {
  return new Promise(function (resolve, reject) {
    // browserSync(config.browserSync);
    resolve();
  });
});

gulp.task('browserSync:dist', gulp.series('build'), async function () {
  await browserSyncAsync();

  return new Promise(function (resolve, reject) {
    browserSync(config.browserSync);
    console.log("===>>> gulp.task.browserSync:dist :: HTTP Server Started");
    resolve();
  });
});

//Builds dist
gulp.task('dist', gulp.series('browserSync:dist'), function () {
  return new Promise(function (resolve, reject) {
    // browserSync(config.browserSync);
    resolve();
  });
});

/** From the ./gnorm/tasks/clean.js */
// clean.js : NOT USED

// gulp.task('copy', ['copy:scripts', 'copy:fonts', 'copy:favicon'])

// gulp.task('copy:scripts', function() {
//   return gulp.src(config.scripts.libsSrc)
//     .pipe(changed(config.scripts.libsDest))
//     .pipe(gulp.dest(config.scripts.libsDest))
// })

// gulp.task('copy:fonts', function() {
//   return gulp.src(`${config.fonts.src}/**/*`)
//     .pipe(changed(config.fonts.clean))
//     .pipe(gulp.dest(config.fonts.clean))
// })

// gulp.task('copy:favicon', function() {
//   return gulp.src(config.favicon.src)
//     .pipe(changed(config.favicon.dest))
//     .pipe(gulp.dest(config.favicon.dest))
// })

/** From the ./gnorm/tasks/createModule.js */

// createModule.js

'use strict'

const util = {
  capitalizeFirstLetter: function (string) {
    return string.charAt(0).toUpperCase() + string.slice(1)
  },
  checkIfExists: function (arr, name) {
    let exists = false
    const regEx = new RegExp(name + '.*', 'gi')
    arr.forEach(function (v) {
      if (v.match(regEx)) {
        exists = true
      }
    })
    return exists
  },
  getArrayofModules: function (string) {
    string = string[ 0 ].match(/({)([\w\s\r\t\/:()'".,]*)/g)
    string = string[ 0 ].split('{')
    return string[ 1 ].split(',')
  },
  getModulesObject: function (string) {
    return string.match(/(modules+[:\s]*{)([\w\s\r\t\/:()'".,]*)*(})/g)
  },
  sanitizeArray: function (arr) {
    let newArr = []
    arr.forEach(function (v, i) {
      newArr.push(v.replace(/\s/g, ''))
    })
    return newArr
  }
}

// Scaffold a regular JavaScript module with:
// $ gulp create-module --name="moduleName"
//
// Scaffold an asynchronous JavaScript module with:
// $ gulp create-module --name="moduleName" --async
gulp.task('create-module', function () {

  argv.constructor = util.capitalizeFirstLetter(argv.name)

  gulp.src(`${config.scripts.modules}/index.js`)
    .pipe(modify({
      fileModifier: function (file, contents) {
        // Get the file content
        let content = contents.toString()

        // Extract the object that contains module references
        let modulesObj = util.getModulesObject(content)

        // Add each module definitation to an array.
        let modulesArr = util.getArrayofModules(modulesObj)

        // Sanitize every item in that aray (spaces, tabs, breaks).
        let modulesArrSanitized = util.sanitizeArray(modulesArr)

        // If it's the first module, then make sure the array is empty
        if (modulesArrSanitized[ 0 ] === '') {
          modulesArrSanitized = []

          // Check to make sure module doesn't exist already
        } else if (util.checkIfExists(modulesArrSanitized, argv.name)) {
          let err = new gutil.PluginError('template', 'The module "' +
            argv.name +
            '" already exists. Please choose a unique module name.'
          )
          throw err
        }

        // Add module to sanitized array
        modulesArrSanitized.push(argv.name + ':require(\'./' + argv.name + '/' + argv.name + '.load\')')
        modulesArrSanitized.sort()

        // Replace the content in the index file
        return content.replace(
          /(modules+[:\s]*{)([\w\s\r\t\/:()'".,]*)*(})/g, '$1' + modulesArrSanitized + '$3')
      }
    }))

    // Beautify the file
    .pipe(prettify({
      config: '.jsbeautifyrc',
      mode: 'VERIFY_AND_WRITE'
    }))
    .pipe(gulp.dest(`${config.scripts.modules}/`))

  // Scaffold the template files into the module folder
  gulp.src('./gnorm/templates/loader.js')
    .pipe(rename(argv.name + '.load.js'))
    .pipe(template(argv))
    .pipe(gulp.dest(`${config.scripts.modules}/${argv.name}`))
  gulp.src('./gnorm/templates/module.js')
    .pipe(rename(argv.name + '.main.js'))
    .pipe(template(argv))
    .pipe(gulp.dest(`${config.scripts.modules}/${argv.name}`))

})

/** From the ./gnorm/tasks/watch.js */

// watch.js

gulp.task('watch', gulp.series('browserSync', async () => {
  console.log("===>>> watch :: styles");
  gulp.watch(config.styles.src, gulp.series('styles'))
  console.log("===>>> watch :: images");
  gulp.watch(config.images.src, gulp.series('images'))
  console.log("===>>> watch :: twig:build-dev");
  gulp.watch(config.twig.watchSrc, gulp.series('twig:build-dev'))
  console.log("===>>> watch :: webpac:build-dev");
  gulp.watch(config.scripts.all, gulp.series('webpack:build-dev'))
}));

/** From the ./gnorm/tasks/default.js */

// default.js

//Builds dev and starts browsersync
// gulp.task('default', ['watch'])
gulp.task('default', gulp.series('watch', function () { }));
