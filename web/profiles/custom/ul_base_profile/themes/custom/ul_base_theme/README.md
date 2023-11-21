# GNorm - Genuine UI Starting Point

Genuine Norm (GNorm) is an all-encompassing starting point for Genuine UI projects that provides architectural direction, fast scaffolding, code familiarity and promotes developer scalability.

## Dependencies
* [Node JS](http://nodejs.org/)
* [Gulp](http://gulpjs.com/)

## Installation
Once you have installed the above dependencies:

1. `cd` into the root of the new project folder and run `npm install` from the command line

## Running
From the root of the project, several commands can be issued from terminal:

1. `gulp`: Runs the default Gulp task. This builds the project with source maps from the `app` folder into the `build` folder, spawns a Node server, opens a new browser with the website at http://localhost:3000, and listens for subsequent changes. When you edit and save a new file, Gulp will recompile accordingly and refresh your browser window with the latest changes automatically.
2. `gulp dev`: Runs the above Gulp task without spawning a server.
3. `gulp build`: Builds project from the `app` folder into the `build`, uglifies the JS, and minifies the CSS. This should generally be run prior to committing/pushing your code to the repo.
4. `gulp dist`: Runs the above task and spawns a localhost server. This is useful if you want to proof the production build before pushing it to a remote server.

## Project Architecture
#### Folder Structure

* `app/`: _All_ work should be done in the `app` folder. This is where your website's source code lives.
* `build/`: When running Gulp, files from `app` are compiled into `build`. If you work out of the `build` folder, your work will be overwritten and you will be sad. Don't work out of the `build` folder.
* `gulp/`: This contains all of the Gulp tasks that the project relies on. There is also a `config.js` file that most of the tasks reference to make file paths and preferences more manageable.
* `node_modules/`: The folder where node modules are installed when you run `npm install`.
* `.jshintrc`: The configuration file that dictates syntactical rules for JS linting. These should be followed closely.
* `.jsbeautifyrc`: The configuration file that can be used with Sublime Text's [HTML-CSS-JS Prettify](https://packagecontrol.io/packages/HTML-CSS-JS%20Prettify) and Atom's [atom-beautify](https://atom.io/packages/atom-beautify) plugins.  Allows you to format your HTML, CSS, JavaScript, and JSON code. These plugins look for a `.jsbeautifyrc` file in the same directory as the source file you're prettifying (or any directory above if it doesn't exist, or in your home folder if everything else fails) and uses those options along the default ones.
* `.scss-lint.yml`: The configuration file for "linting" Sass/SCSS files.  Not currently enforced during build-time, but can be used with Sublime Text's [SublimeLinter](http://sublimelinter.readthedocs.org/en/latest/) plugin along with [Sublime​Linter-contrib-scss-lint](https://packagecontrol.io/packages/SublimeLinter-contrib-scss-lint), or Atom's [atom-lint](https://atom.io/packages/atom-lint) and [linter-scss-lint](https://atom.io/packages/linter-scss-lint) (among others).
* `gulpfile.js`: This references all of the tasks in `gulp/tasks/`. Tasks are broken apart for organizational purposes and referenced from this root file when you run `gulp`.
* `package.json`: Contains the project's Node dependencies. Modules specified in this files are installed into `node_modules` when you run `npm install`.
* `README.md`: You're reading it.

#### Atomic Organization
To encourage organization, scalability, and code-reuse, we generally take an [Atomic Design](http://bradfrost.com/blog/post/atomic-web-design/) approach when structuring our HTML and Sass. Our Sass partials and HTML includes are broken apart in folders denoted by Atomic-style building blocks (atoms/pieces, molecules/components, organisms/regions, etc.).

## Usage
#### Writing HTML

Init utilizes twig.js for creating templates and reusable components. Twig provides the ability to:

* Write a piece of code once and reuse it in multiple places.
* Use conditional code to allow for variation in the template or component.
* Use json data to populate content of each template, which allows the use of the same component partial within one or multiple templates with different content for each instance.

Your HTML lives in the `app` folder, and can contain references to reusable includes (which live in `app/includes/`). For example:

    {% include 'includes/3_components/media--person-card.twig' %}

Each main template in the `app` folder also requires a `json` file that includes any data utilized in the template. These data files live in the `app/json` folder and must use the same name as the template file.

When Gulp runs, it takes the includes and data, and compiles the full HTML into the `build` folder.

_Note: The paths in which these includes are referenced are relative to the HTML file you are authoring. If you include an include from another include, that path will need to be prefixed with `../` accordingly._

Want to know how to get the most out of twig? [Check out the documentation](http://twig.sensiolabs.org/documentation).

*Note: Init uses [twig.js](https://github.com/justjohn/twig.js/wiki) which is an implementation of the Twig PHP templating language, so some features in the documentation may not be available. Check the [implementation support for twig.js](https://github.com/justjohn/twig.js/wiki/Implementation-Notes) to verify feature support.*

#### Accessibility
When developing we should be aware of how our sites preform for users with accessibility needs. Our sites should be level AA compliant, refer to [Accessibility Checklist](http://webaim.org/standards/wcag/checklist) and [Some best practices for accessibility](https://www.webaccessibility.com/best_practices.php) for more information on level AA. We recommend using these tools to help check the accessibility of sites:

* [Cythina Says](http://www.cynthiasays.com) allows users to test individual pages on their website and provides feedback in a reporting format that is clear and easy to understand.
* [Wave Chrome Extension](https://chrome.google.com/webstore/detail/wave-evaluation-tool/jbbplnpkjmmeebjpijfedlgcdilocofh?hl=en-US) is a google extension that provides visual feedback about the accessibility of your web content by injecting icons and indicators into your page.
* [Contrast Checker](http://webaim.org/resources/contrastchecker) is a great tool to test the contrast of text to background within your website
* [ChromeVox screenreader](https://chrome.google.com/webstore/detail/chromevox/kgejglhpjiefppelpmljglcjbhoiplfn?hl=en) is an extension that is a screenreader allowing you to understand how your site will perform. Please refer to [How to use ChromeVox](http://www.chromevox.com) for more information on how to use ChromeVox.

##### Aria Basics
[WAI-ARIA](https://www.w3.org/TR/WCAG20), the **Accessible Rich Internet Applications** Suite, defines a way to make Web content and Web applications more accessible to people with accessibility needs. For background on ARIA and extensive examples on how and where to use them, refer to the [ARIA in HTML](https://specs.webplatform.org/html-aria/webspecs/master) page. For practical examples of working with ARIA can be found [here](http://heydonworks.com/practical_aria_examples) and information on browser support for the ARIA tags can be found [here](http://caniuse.com/#feat=wai-aria).

The following ARIA tags are the baseline accessibility tags we recommend using while writing your HTML. Each will have a markup example on its use.

* `role="navigation":` A collection of navigational elements (usually links) for navigating the document or related documents.

```html
<nav role="navigation">
  <ul>
    <li><a href="#">Home</a></li>
    <li><a href="#">Landing Page</a></li>
    <li><a href="#">Inner Page</a></li>
  </ul>
</nav>
```

_Note: the `<nav>` element is a HTML 5 element that is not supported in all browsers, so putting the `role="navigation` makes this backwards compatible_

* `role="menu":` A type of widget that offers a list of choices to the user.
* `role="menuitem":` An option in a group of choices contained by a menu.

```html
<ul role="menu">
  <li><a href="#" role="menuitem">Sub Menu</a></li>
  <li><a href="#" role="menuitem">Sub Menu</a></li>
  <li><a href="#" role="menuitem">Sub Menu</a></li>
</ul>
```

* `aria-haspopup="true":` Indicates that the element has a popup context menu or sub-level menu.
* `aria-expanded="false:` Indicates that the element is expanded or closed.

```html
<a aria-haspopup="true">Menu Item</a>
<ul class="sub-menu" aria-expanded="false">
  <li><a>Sub Menu Item</a></li>
  <li><a>Sub Menu Item</a></li>
  <li><a>Sub Menu Item</a></li>
</ul>
```

_Note: `aria-expanded="false"` should be changed to `true` when javascript shows the .sub-menu_
_Note: this aria tag should only be used for a hidden menu_
_Note: the `role="menu"` and `role="menuitem"` have been omitted from this example for clarity purposes_

* `role="main":` The main content of a document.

```html
<div role="main">
  <h1>Page title</h1>
  <p>Page description</p>
  ...
</div>
```

* `role="tab":` A grouping label providing a mechanism for selecting the tab content that is to be rendered to the user.
* `role="tablist":` A list of tab elements, which are references to tabpanel elements.
* `role="tabpanel":` A container for the resources associated with a tab, where each tab is contained in a tablist.
* `aria-selected="true":` Sets or retrieves the selection state of this element.

```html
<div>
  <ul role="tablist">
    <a role="tab" aria-selected="true">Click here to change tabs</a>
  </ul>
  <div role="tabpanel">
    <p>This is the tab content that will show when the tab is clicked</p>
  </div>
</div>
```

_Note: `aria-selected="true":` should be changed to `false` when the javascript fires (the new active tab should get the new `true)_

* `role="button":` An input that allows for user-triggered actions when clicked or pressed.

```html
<a role="button">This Link is a Button</a>
```

##### `hidden` helper class
Init launches with a `hidden` helper class that will help hide the content of a linked icon (for example an arrow that moves a carousel). To makes this element accessible you will need to add text within the element. To prevent this from breaking the layout we can add the class `hidden` to remove the text from the DOM visually while still allowing a screenreader to access it. The following is an example of use:

```html
<a class="hidden icon-arrow-l">View the next slide</a>
```

The text "View the next slide" will be moved off screen visually but the markup will be semantically correct.

#### Writing Sass
##### Sass Structure
Your styles live in the `app/styles/sass` folder. This folder is organized atomically:

* `screen.scss`: This contains globbing patterns that `@include` tools and partials contained in the following folders
* `0_utility/`: This contains font, helper, mixin, and variable declarations.
* `1_core/`: All bare (classless) HTML is styled in the `html-elements` folder. Icons are specified in the `_icons.scss` partial and the site's grid system is specified in the `_layout.scss` partial.
* `2_pieces/`: This is where your "Atoms" live.
* `3_components/`: This is where your "Molecules" live.
* `4_regions/`: This is where your "Organisms" live.
* `5_pages/`: This is where your "Layouts" live.

##### Using the Grid
###### Grid Defaults
Init comes with a custom flex-based grid system for building responsive layouts. Grid configuration options are found in `app/styles/0_utility/_settings.grid.scss` and the default grid can be viewed by going to the 'Grid Structure' page on your localhost. Default settings are:

```css
$grid-columns: 12;
$grid-gutter: $spacing-unit--l;
$add-grid-breakpoints: true;
$add-grid-offsets: true;
```

###### Grid Classes
Init provides some helpful grid classes by default:
* `span-#` is the standard grid class. For example, `span-4` makes an element 4 columns wide.
* `offset-#` is the standard offset class. For example, `offset-2` offsets an element 2 columns to the right.

###### Grid Classes with Responsive Modifiers
The standard grid classes can be modified to target a certain breakpoint:
* `span-#-BREAKPOINT` is the standard responsive grid class. For example, `span-4-t` makes an element 4 columns wide starting at the 'tablet' breakpoint.
* `offset-#-BREAKPOINT` is the standard responsive offset class. For example, `offset-4-ld` offsets an element 4 columns to the right starting at the 'large-desktop' breakpoint.

To shorten classnames, acronyms are used for each breakpoint modifier, e.g. 'm' for 'mobile' and 'lt' for 'large-tablet'.

The list of breakpoints used to generate these responsive classes is set in `_settings.grid.scss` via the `$grid-breakpoints` variable. Here you can also disable responsive and offset classes via the `$add-grid-breakpoints` and `$add-grid-offsets` variables.

_Note: We develop mobile first, so all responsive modifier classes use `min-width` media queries._

##### Changing/Adding Fonts
To prevent render blocking, Init leverages [Font Face Observer](https://github.com/bramstein/fontfaceobserver). Init comes with a font set of Roboto to demonstrate how fonts are set up using this pattern. In short, the script in `app/includes/1_core/head.html` listens for the font requests coming from `app/styles/sass/0_utility/_fonts.scss`. Using a promise, it will add a class of `fonts-loaded` to the body once the fonts have downloaded. This class is leveraged in `app/styles/sass/1_core/html-elements/_base.scss` to update the page with the custom webfonts. If you want to change or add fonts, follow these steps:

1. In `app/styles/sass/0_utility/_fonts.scss`, change or add a new font making sure to include the appropriate weight. Then update your font variable(s) accordingly.
2. In `app/includes/1_core/head.html`, create a new variable for each weight and update the weight property with the same weight you specified in step 1.

##### RTL Support
Init supports LTR and RTL languages by generating separate stylesheets for each. These stylesheets include all of the same SCSS partials, but are passed different 'direction' variables based on language direction. An overview on this approach is given below, but for a more detailed explanation read [this article](https://medium.com/@elad/the-best-way-to-rtl-your-website-with-sass-105e34a4298a).

###### Overview
The init build process will compile two stylesheets, one for LTR languages (`build/styles/screen-ltr.css`) and one for RTL languages (`build/styles/screen-rtl.css`). When writing SCSS replace 'left/right' properties and values with the appropriate 'direction' variables and the proper values will be output for each stylesheet.

###### Using direction variables
The language direction variables are as follows:

```css
// LTR languages
$direction: ltr;
$opposite-direction: rtl;
$start-direction: left;
$end-direction: right;
$transform-direction: 1;

// RTL languages
$direction: rtl;
$opposite-direction: ltr;
$start-direction: right;
$end-direction: left;
$transform-direction: -1;
```

`$start-direction` is the starting direction of the language, `$end-direction` the ending direction. So, for a LTR language, `$start-direction` is 'left', `$end-direction` is 'right'.

Use these variables in place of 'left/right' property names and values. For example:

```css
.foo {
  float: $end-direction; // LTR => float: right;
  margin-#{$start-direction}: 30px; // LTR => margin-left: 30px;
}
```

Additionally, a transform variable is used for transform properties that translate and/or scale differently from LTR to RTL, for example:
```css
.icon-arrow-right {
  #{$end-direction}: 50%; // RTL => left: 50%;
  position: absolute;
  transform: translateX(50% * $transform-direction) scale($transform-direction); // RTL => transform: translateX(-50%) scale(-1);
}
```

###### Configuration options
By default, only the LTR stylesheet is watched by the 'watch' task so as not to slow down routine development, but all SCSS files (LTR *and* RTL) are built when the 'build' task is run. To add any files to the 'watch' task, simply comment-out these lines from the list of excluded files in `gnorm/config.js` (`styles.watchSrc`) and restart gulp.

##### Updating Icomoon Set
Init comes with a generic font set of icons that includes common things like arrows and social chiclets. To add or edit the icon font, upload `app/fonts/icomoon/selection.json` to the [Icomoon web-app](https://icomoon.io/app/#/select). From here, you can add and remove icons, then re-export. When you re-export, make sure you save the updated `selection.json`.

#### Writing JS
##### JS Structure
Init comes with a JS module loading system. This is how the JS folder is structured:

* `libs/`: This is where your vendor libraries and plugins live. Alternatively, you can use [Bower](http://bower.io/) if you really prefer.
* `modules/`: This is where your JS modules live.
* `modules/index.js`: This is the "module registry". It is essentially an object that contains a key/value for each module in the project.
* `app.js`: This is your application bootstrap. All JS kicks off from here.

##### Create A New Module Using The JS Module Loader

You can quickly generate a new module right from the command line. Run `gulp create-module --name=moduleName`, where _moduleName_ is the name of your new module. Be sure to keep it camelCase. Running this command will generate a new module folder as well as add a _require_ reference to the module registry file. If you use this CLI method instead of manually creating modules, you shouldn't ever have to touch the module registry file.

You can also pass an _async_ flag to the command. For example `gulp create-module --name=moduleName --async`. This will tell Webpack to bundle this file separately from the rest of the application and will asynchronously load this module in only when the module is referenced on the page.

When you generate a new module, it will have two files; the _.load_ file and the _.main_ file. You don't need to touch the _.main_ file. This simply tells Webpack how to require the module in the build (depending on whether you specified it to be asynchronous or not).

The _.main_ file is where you write your code. It exports an ES6 class:

```js
'use strict';

var $ = require('jquery');

module.exports = class SampleModule{
  constructor($el){
    this.$el = $el;
    this.method(this.$el);
  }
  method($element){
    console.log($element);
  }
};
```

If you're unfamiliar with the ES6 class syntax, you can read more about it [here](http://javascriptplayground.com/blog/2014/07/introduction-to-es6-classes-tutorial/).

Then reference the new module from somewhere in the HTML with the `data-module` attribute:

```html
<section class="outer-wrapper" data-module="sampleModule">
  <div class="inner-wrapper">
    <h1>My Module</h1>
  </div>
</section>
```

That's it! Now, when the page loads, `app/scripts/app.js` kicks off the module registry, which loops through all of the `data-module` attributes on the page and instantiates a `new` version of that module via the reference in the module registry.

_Note: The module loader passes a jQuery object of the element that contains `data-module`. This allows for easy scoping if you need to load more than one instance of the module on a page._

#### Using the Component Reveal Functionality
Init comes with a reveal utility that allows for the highlighting of components on a particular page. To highlight a component simply pass in a search query at the end of the url `?r=[COMPONENT SELECTOR]`. For example to hightlight all the .example-cards components, simply pass in this query in the url, `?r=.example-card`. This query value can be any valid jQuery selector.

##### Using Third-Party Libraries/Plugins
Some plugins might not be available via NPM. Rather than use another package manager, we can leverage [Napa](https://www.npmjs.com/package/napa). Add the repo URL to the `napa` object in the `package.json` folder and reference it just like an NPM package.

If for whatever reason, there is no repository for the library you want to include, you can download it manually to `app/scripts/libs`, include via the `browser` object in `package.json`, and reference it just like an NPM module.

#### Using Sourcemaps
Init leverages JS and Sass sourcemapping for easy debugging. The sourcemaps are automatically built — all you need to do is configure your browser to use them.

##### Example set up using Chrome Dev Tools:

1. Run `gulp` to get your server running.
2. In Chrome Inspector, go into settings and make sure sourcemaps are enabled for both JS _and_ CSS.
3. Open the "Sources" tab in the inspector, and in the side pane on the left, right-click and select "Add Folder to Workspace". Select the root folder of the project, from your file system.
4. At the top of the browser, Chrome will prompt you for access. Click the "Allow" button.
5. In the left side pane of the Sources tab, you should now see your project folder. Expand it and and drill down to any one of your Sass partials. Click it once. In the middle pane, an alert should come up asking if you want to map the workspace resource. Click the "more" link to expand the dialog, then click "Establish the mapping now...".
6. A list of files should come up. Select the one that matches the file you just clicked on (generally the first one).
7. Inspector will want to restart.
8. That's it! Your local files should be tied to the sourcemaps the site loads. Now, when you inspect an element, look at the CSS pane and it should have a link to the exact partial for each rule declaration.

## Common issues

#### When I run `gulp`, the command line errors out. WTF?
Make sure you've installed _ALL_ dependencies specified above. Also, make sure you have up-to-date versions of Node.

#### Why is Gulp not picking up on changes that I made to a file?
The `watch` task only picks up on changes made to files that existed when the task was started. When you edit a Gulp task, a config file, a twig `json` file, or add any new file to the `app` folder, you must stop and restart Gulp.
