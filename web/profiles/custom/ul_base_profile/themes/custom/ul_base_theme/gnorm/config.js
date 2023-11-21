const src = './app';
const dest = './build';
const templates = './templates';
const path = require('path')

module.exports = {
  app: src,
  build: dest,
  mode: 'development',
  browserSync: {
    ui: false,
    server: false,
    open: false,
    reloadDelay: 500,
    notify: false,
    files: [
      dest + "/**",
      // Exclude Map files
      "!" + dest + "/**.map"
    ]
  },
  favicon: {
    src: `${src}/favicon*.*`,
    dest: dest
  },
  fonts: {
    src: `${src}/fonts/**/*`,
    fonts: `${src}/fonts`,
    dest: `${dest}/fonts`,
    clean: `${dest}/fonts`
  },
  images: {
    src: `${src}/images/**`,
    dest: `${dest}/images`
  },
  scripts: {
    all: `${src}/scripts/**/*.js`,
    modules: `${src}/scripts/modules`,
    dest: `${dest}/scripts`,
    publicPath: path.resolve(
      "/profiles/custom/ul_base_profile/themes/custom/ul_base_theme"
    ),
    path: path.resolve(`${dest}/scripts`),
    libsSrc: `${src}/scripts/libs/**/*.js`,
    libsDest: `${dest}/scripts/libs/`
  },
  styles: {
    // Exclude files not currently in development, avoids slow 'watch' task
    watchSrc: [
      `${src}/styles/**/*.{sass,scss}`,
      `!${src}/styles/screen.scss`,
      // `!${src}/styles/screen-ltr.scss`,
      `!${src}/styles/screen-rtl.scss`,
      `!${src}/styles/editor.scss`,
      `!${src}/styles/editor-ltr.scss`,
      `!${src}/styles/editor-rtl.scss`,
      `!${src}/styles/styleguide.scss`
    ],
    // All files included in 'build'
    src: `${src}/styles/**/*.{sass,scss}`,
    dest: `${dest}/styles`
  },
  twig: {
    watchSrc: [src + "/*.twig", src + "/json/*.json", templates + "/**/*.twig"],
    source: "app",
    pattern: "app/*.twig",
    dest: "build",
    data: "app/json",
    namespaces: {
      ul_base_theme: "../../../../ul_base_profile/themes/custom/ul_base_theme/templates"
    },
    global: "app/json/global.json",
    vendor: "../../../../../../../vendor"
  }
};
