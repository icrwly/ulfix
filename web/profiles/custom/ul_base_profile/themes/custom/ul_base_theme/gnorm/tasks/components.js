'use strict';
var fs = require('fs');
var gulp = require('gulp');
var argv = require('yargs').argv;
var template = require('gulp-template');
var htmlPrettify = require('gulp-beautify');
var jsPrettify = require('gulp-jsbeautifier')
var config = require('../templates/components/components-config');
var componentsFileName = 'components.twig';
var coreFolder = '1_core';
var templateType = 'twig';

gulp.task('components', function() {
  // Create a string that will be used to create the component page output
  var componentsString = '';
  // Create a string that will be used to store the twig templates
  var twigTemplates = '';
  // Create a string that will be used to store the filter HTML
  var filterMarkup = '';
  // Create an array that will store all the component objects
  var componentsArray = [];
  // Create an array to store all the templates that are in each 'type' folder (ex. 2_pieces, 3_components..)
  var folderComponents = [];
  // Create an object to be used to push to the componentsArray
  var componentObject = {};
  // Create a token to assign a unique ID to each component within the loop
  var fileIndex = 0;
  // Grab all the twig templates located in the config.src (components-config.js) directory
  var fileGrab = fs.readdirSync(config.src);

  /********************************/
  // Create the twigTemplates array
  /********************************/

  // Loop through each of the files in side the config.src (components-config.js) directory
  for (var tt = 0; tt < fileGrab.length; tt++) {
    // If the current file contains the suffix '.twig' and is not the components.twig file
    if (fileGrab[tt].substr(-5) === '.twig' && fileGrab[tt] !== componentsFileName) {
      // Read the current file as a string
      var templateString = fs.readFileSync(config.src + '/' + fileGrab[tt], 'utf8');
      // Concatenate the string with the global twigTemplates string (will keep adding to this variable)
      twigTemplates = twigTemplates + templateString;
    };
  }

  /****************************/
  // Create the componentsArray
  /****************************/

  // getDirectories Function
  /**************************/
  // Description: Returns an object that contains all the directories immediately inside a given path
  // Parameters:
  //  * path: (string) path to a directory
  var getDirectories = function(path) {
    // Return contents of a directory
    return fs.readdirSync(path).filter(function(item) {
      // ...minus items that are not directories
      return fs.statSync(path + '/' + item).isDirectory()
    })
  }

  // getAllTemplates Function
  /**************************/
  // Description: Returns an object that contains all twig files within a directory (recursively) and a path to each file
  // Parameters:
  //  * path: (string) path to a directory that contains twig files
  //  * componentFolder: (string) name of component type, e.g. "2_pieces"
  var getAllTemplates = function(path, componentFolder) {
    // Create an array that will store all twig file names and paths
    var files = [];
    // Get array of filenames in directory
    var contents = fs.readdirSync(path);
    for (let value of contents) {
      // Check if item is a file and matches the template type
      if (fs.statSync(path + '/' + value).isFile() && value.split('.').pop() === templateType) {
        // Split path into array
        var pathArray = path.split('/');
        // Get index of componentFolder in array
        var key = pathArray.indexOf(componentFolder)
          // Remove items before componentFolder and join as string
        var modifiedPath = pathArray.slice(key).join('/')
          // Add name and path to array
        files.push([{
          name: value,
          path: modifiedPath
        }]);
      } else if (fs.statSync(path + '/' + value).isDirectory()) {
        // Otherwise, if it's a directory, pass the directory path back to this function
        var children = getAllTemplates(path + '/' + value, componentFolder)
        for (let value of children) {
          // Add the result of each to the array
          files.push(value)
        }
      }
    }
    // Return array of all twig file names and paths
    return files
  }

  // Get names of all the sub folders immediately within 'includes' directory
  var componentFolders = getDirectories(config.includesDataPath);

  // Sub folder array creation
  /***************************/
  // Loop through the componentFolder array
  for (var cf = 0; cf < componentFolders.length; cf++) {
    // Create and object for each of the include folders that includes the sub folder name ('componentType') and it's file contents ('fileContents')
    var folders = {
      'componentType': componentFolders[cf],
      'folderContents': getAllTemplates(config.includesDataPath + '/' + componentFolders[cf], componentFolders[cf])
    };
    // Push this object to the folderComponents Array
    folderComponents.push(folders);
  }

  // Loop through the componentFolders array
  for (var fc = 0; fc < componentFolders.length; fc++) {
    // Within the folderComponents array, loop through the folder's contents
    for (var f = 0; f < folderComponents[fc].folderContents.length; f++) {
      // Create a boolean to control the adding of a component to the componentsArray array
      var render = true;

      // Store current file name
      var currentFileName = folderComponents[fc].folderContents[f][0].name;
      // Store current file path
      var currentFilePath = folderComponents[fc].folderContents[f][0].path;

      // Read the current file as a string
      var currentFile = fs.readFileSync(config.includesDataPath + '/' + currentFilePath + '/' + currentFileName, 'utf8');

      // createComponent Function
      /**************************/
      // Description: Returns an object that contains all a component's information
      // Parameters:
      //  * fileIndex: (boolean) used to create a unique ID for the component
      //  * componentType: (string) the parent folder of a component, used to define the structure type (ex. 2_pieces, 3_components...)
      //  * fileName: (string) the file name of the twig template, used to check against excludes and includes and make the component display title
      //  * title: (string) the component title (ONLY AVAIBLABLE FROM THE CONFIG ADD)
      //  * withAttribute: (string) the component title (ONLY AVAIBLABLE FROM THE CONFIG ADD)
      //  * block: (string) the template twig block which will be used in the twig embed (ONLY AVAIBLABLE FROM THE CONFIG ADD)
      var createComponent = function(fileIndex, componentType, fileName, filePath, sectionTitle, withAttribute, block) {
        // Return a newly created component object
        return {
          'index': fileIndex,
          'componentType': componentType,
          'fileName': fileName,
          'filePath': filePath,
          'title': sectionTitle,
          'with': withAttribute,
          'block': block
        };
      };

      // Check for config excludes
      /***************************/
      // Loop through the config.exclude (components-config.js) array
      for (var ce = 0; ce < config.excludes.length; ce++) {
        // Check the current file's name matches the file name of the current config.exclude (components-config.js)
        if (config.excludes[ce] === currentFileName) {
          // Set the render variable to false to prevent this component from rendering
          render = false;
        }
      }

      // Check for in-file excludes ({# exclude component #})
      /*****************************************************/
      // If the current file contains the string 'exclude component'
      if (currentFile.match('exclude component') !== null) {
        // Set the render variable to false to prevent this component from rendering
        render = false;
      }

      // Add the component to the componentsArray to be rendered
      /*********************************************************/
      // If render is true
      if (render) {
        // Create a component object using the createComponent function
        componentObject = createComponent(fileIndex, folderComponents[fc].componentType, currentFileName, currentFilePath);
        // Increase the fileIndex value by one
        fileIndex = fileIndex + 1;
        // Push the newly created component object to the componentsArray (to be rendered later)
        componentsArray.push(componentObject);
      }
      // Check for config adds
      /***********************/
      // Loop through the config.add (components-config.js) array
      for (var ac = 0; ac < config.add.length; ac++) {
        // Check to see if the current file name matches the current object in the config.add (components-config.js) array
        if (config.add[ac].base === currentFileName) {
          // Create a component object using the createComponent function (sectionTitle, withAttribute, and block arguments are passed in)
          componentObject = createComponent(fileIndex, config.add[ac].folder, config.add[ac].base, currentFilePath, config.add[ac].title, config.add[ac].withAttribute, config.add[ac].block);
          // Increase the fileIndex value by one
          fileIndex = fileIndex + 1;
          // Push the newly created component object to the componentsArray (to be rendered later)
          componentsArray.push(componentObject);
        }
      }
    }
  }

  /************************************************/
  // Prepare the componentsArray for page rendering
  /************************************************/

  // Loop through the componentsArray (this contains the component objects that are going to be rendered)
  for (var c = 0; c < componentsArray.length; c++) {
    // Ignore if the component is in the core folder
    if (componentsArray[c].componentType !== coreFolder) {
      // Create a string that will be used to render the twig include
      var twigInclude = '';
      // Create a string that will be used to contain the with attribute data
      var withReturn = '';
      // Create a string that will be used to contain the formatted component title for display
      var titleReturn = '';

      // createTitle Function
      /**************************/
      // Description: Returns an component's display title to be shown on the page
      // Parameters:
      //  * component: (object) used to create the display title for the component
      function createTitle(component) {
        // Replace the current component's file name's '-' with spaces
        var componentTitleReplace = component.fileName.replace(/-/g, ' ');
        // Split the current component's file name on the '.' (this will be targeting the '.twig' suffix)
        var componentTitleClean = componentTitleReplace.split('.twig');
        // Return the
        return componentTitleClean[0].replace(/[.]/g, ' ').replace(/[_]/g, ' ');
      }

      // withFind Function
      /*******************/
      // Description: Finds the FIRST twig 'with' attribute to be used with a component
      // Parameters:
      //  * contents: (string) a concatenated string of all the twig templates (none includes directory) to search
      //  * component: (string) a component to search for within the twig templates (will use the component twig file name)
      function withFind(contents, component) {
        // Concatenate a string with the isolated file name and the 'with' attribute
        var includeSearch = '/' + component.fileName + "' with ";
        // Split the twig templates by the includeSearch string (will be in this format; '/[file name].twig" with ')
        var splitContents = contents.split(includeSearch);
        // Grab the second part of the split (to isolate the with attribute data)
        var secondSplit = splitContents[1];
        // If the second part of the split is NOT undefined (the with does exist)
        if (secondSplit !== undefined) {
          // Get everything before the first closing twig tag, trim and return
          return ' with ' + secondSplit.split('%}')[0].trim()
        } else {
          // Return a blank string to end the function
          return '';
        }
      }

      // toTitleCase Function
      /**********************/
      // Description: Turns the first character of each word to uppercase
      // Parameters:
      //  * title: (string) used to pass in a title to title case (first letter in each word to uppercase)
      function toTitleCase(title) {
        // Return the title with each word containing a leading uppercase character
        return title.replace(/\w\S*/g, function(txt) {
          return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
      }

      // twigIncludeMarkup Function
      /****************************/
      // Description: Returns the twig 'include' string to be added to the page
      // Parameters:
      //  * componentType: (string) used to create the URL for the twig include
      //  * fileName: (string) used to create the URL for the twig include
      //  * withReturn: (string) added to the twig 'include' to manage the data
      function twigIncludeMarkup(componentType, fileName, withReturn) {
        // Return the title with each word containing a leading uppercase character ('{% include '/includes/[component type]/[file name]' with {[data]} %}')
        return "{% include '" + config.includesRenderPath + componentType + '/' + fileName + "'" + withReturn + " %}";
      }

      // twigComponentStart Function
      /*****************************/
      // Description: Returns the start of the component markup
      // Parameters:
      //  * indexValue: (string) used to add a unique ID to the component div
      //  * title: (string) used to create a display name on the component
      function twigComponentStart(indexValue, componentType, title) {
        // Return the title with each word containing a leading uppercase character ('<hr><div class='row' data-filter="[index value]"><h3>[title]</h3>')
        return "<div class='components__item' data-filter='" + indexValue + "' data-type='" + componentType + "'><h3>" + title + "</h3>";
      }

      // Create the twig include string to be added to the page
      /********************************************************/
      // Check to see if the components requires a twig 'embed' (ONLY AVAIBLABLE FROM THE CONFIG ADD)
      if (componentsArray[c].block !== undefined) {
        // Define the title of the component (defined in the config (components-config.js))
        titleReturn = componentsArray[c].title;
        // Concatenate a string with the with data of the component (defined in the config (components-config.js))
        withReturn = ' with {' + componentsArray[c].with + '}';
        // Create the markup that will be rendered on the template
        twigInclude = twigComponentStart(componentsArray[c].index, componentsArray[c].componentType, titleReturn) + "{% embed '" + config.includesRenderPath + componentsArray[c].filePath + '/' + componentsArray[c].fileName + "'" + withReturn + " %}{% block " + componentsArray[c].block.variable + " %}" + twigIncludeMarkup(componentsArray[c].block.blockTemplate, componentsArray[c].block.blockBase, withReturn) + "{% endblock %}{% endembed %}</div>";
        // Else the component requires a twig 'include'
      } else {
        // Check to see if this component have 'with' data already (ONLY AVAIBLABLE FROM THE CONFIG ADD)
        if (componentsArray[c].with !== undefined) {
          // Define the title of the component (defined in the config (components-config.js))
          titleReturn = componentsArray[c].title;
          // Concatenate a string with the with data of the component (defined in the config (components-config.js))
          withReturn = ' with {' + componentsArray[c].with + '}';
          // Else this component does not have 'with' data
        } else {
          // Use the 'toTitleCase' function to transform the file name into a display text
          titleReturn = toTitleCase(createTitle(componentsArray[c]));
          // Use the 'withFind' function to parse the twig templates for example 'with' data
          withReturn = withFind(twigTemplates, componentsArray[c]);
        }
        // Create the markup that will be rendered on the template
        twigInclude = twigComponentStart(componentsArray[c].index, componentsArray[c].componentType, titleReturn) + twigIncludeMarkup(componentsArray[c].filePath, componentsArray[c].fileName, withReturn) + "</div>";
      }
      // Concatenate each component into the componentString variable
      componentsString = componentsString + twigInclude;
    }
  }

  /***************************************/
  // Prepare the Filter Markup for page rendering
  /***************************************/
  // Description: Builds out the filter based on the configuration in config.filter.filterBy
  // Parameters:
  //  * parameters: (object) this is the config.filter.filterBy object from components-config.js
  function createFilterMarkup(parameters) {
    // Create a variable that will contain the filter markup that will be printed on the page
    filterMarkup = '';
    // If the config's includeFilter is set to true
    if (parameters.includeFilter === true) {
      // Define the filterMarkup as HTML
      filterMarkup = '<div class="components-filter" data-module="componentsFilter"><h2 class="components-filter__title">Filter By:</h2>';
      // Else the config's includeFilter is set to false
    } else {
      // Leave the filterMarkup as an empty string
      return filterMarkup;
    }
    // Loop thorugh the parameters that are defined in the components-config.js
    for (var key in parameters.filterParameters) {
      // If the current key is 'abcOrder' and has a value of true (components-config.js)
      if (key === 'abcOrder' && parameters.filterParameters[key] === true) {
        // Add the ascending and descending sort buttons to the existing filterMarkup string
        filterMarkup = filterMarkup + '<button type="button" name="abcOrderAscending" class="components-filter__category ascending" data-filter-category="' + key + '">Sort A > Z</button><button type="button" name="abcOrderDescending" class="components-filter__category descending" data-filter-category="' + key + '">Sort Z > A</button>';
        // If the current key is 'type' and has a value of true  (components-config.js)
      } else if (key === 'type' && parameters.filterParameters[key] === true) {
        // Define an empty string that will contain the type select's options
        var optionsList = '';
        // Loop through the components folder subfolders
        for (var i = 0; i < componentFolders.length; i++) {
          // If the current folder is not 1_core
          if (componentFolders[i] !== '1_core') {
            // Loop through the componentsArray to see if one of the components is in the folder
            for (var j = 0; j < componentsArray.length; j++) {
              // If the current component folder matches the current components 'componentType' value
              if (componentFolders[i] === componentsArray[j].componentType) {
                // Split the folder name to only render the 2nd part of it (ie. "pieces", components", ...)
                var typeTextSplit = componentFolders[i].split('_');
                // Add the option to the optionsList string
                optionsList = optionsList + '<option value="' + componentFolders[i] + '">' + typeTextSplit[1] + '</option>';
                // Stop the for loop once the match is determined
                break;
              }
            }
          }
        }
        // Add the type options to the optionsList string
        filterMarkup = filterMarkup + '<div class="components-filter__category select-container"><select class="components-filter__category" name="type" data-filter-category="' + key + '"><option value="all" selected>All Types</option>' + optionsList + '</select></div>';
        // If the current key is 'clear' and has a value of true  (components-config.js)
      } else if (key === 'clear' && parameters.filterParameters[key] === true) {
        // Add the clear button to the optionsList string
        filterMarkup = filterMarkup + '<button type="button" name="abcOrderAscending" class="components-filter__category" data-filter-category="' + key + '">Clear All</button>';
      }
    }
    // Add a trailing div to complete the markup
    filterMarkup = filterMarkup + '</div>';
    // Return the complete markup to be added to the page
    return filterMarkup;
  }
  // Run the createFilterMarkup function to create the filter HTML based on the components-config.js values
  createFilterMarkup(config.filter.filterBy);

  /****************************/
  // Render the Components page
  /****************************/

  // Assign values to the output variables (will be printed on the template when rendered)
  /**************************************************************************************/
  // Assign the component title value to the titleOutput variable (defined in the config (components-config.js))
  argv.titleOutput = config.title;
  // Assign the componentsString (containing all the markup needed for the components) to the componentOutput variable
  argv.componentsOutput = componentsString;
  // Assign the filterMarup to the filters variable
  argv.filters = filterMarkup;
  // Assign the path for the components includes
  argv.includesPath = config.includesRenderPath;


  // Target the components template
  gulp.src('./gnorm/templates/components/template/' + componentsFileName)
    // Update the template tokens with the correct values
    .pipe(template(argv))
    // Clean up the markup
    .pipe(htmlPrettify({
      indent_char: ' ',
      indent_size: 2
    }))
    // Include the updated components.twig file in the /templates/1_core directory
    .pipe(gulp.dest(config.dest));

  /****************************/
  // Create the Filter module
  /****************************/

  // Update the index.js with the new module path
  /**************************************************************************************/

  fs.readFile(config.filter.indexPath, 'utf8', (err, data) => {
    if (err) {
      throw err;
    };
    // Create an object to contain methods to render the JS into the index.js
    var util = {

      // checkIfExists Function
      /***************************************/
      // Description: Loops through the module array and check to see if i matches the value of the config.filter.componentsFilter
      // Parameters:
      //  * arr: (array) the array that contains the index.js module list
      //  * name: (string) the name you are checking for
      checkIfExists: function(arr, name) {
        // Define a variable that will be default return false
        var exists = false;
        // Define the regex match variable using the name to be matched
        var regEx = new RegExp(name + '.*', 'gi');
        // Loop though the module array
        arr.forEach(function(v) {
          // If the current module string against the regex match variable
          if (v.match(regEx)) {
            // Change the exists value to 'true'
            exists = true;
          }
        });
        // Return the value of exists when finished
        return exists;
      },

      // getArrayofModules Function
      /***************************************/
      // Description: Uses regex to convert an object to an array and returns the results
      // Parameters:
      //  * arr: (string) this is the modules object from the 'index.js'
      getArrayofModules: function(string) {
        // Isolate the inside of the modules object (removes most unneeded characters)
        string = string[0].match(/({)([\w\s\r\t\/:()'".\-\_,]*)/g);
        // Split the isolated object insides by the "{" (removes the final unneeded character)
        string = string[0].split('{');
        // Split the isolated object by ',' to transform it into an array
        return string[1].split(',');
      },

      // getModulesObject Function
      /***************************************/
      // Description: Return the "index.js" modules object to check to see if the componentsFilter module has been added
      // Parameters:
      //  * string: (string) this is the contents of "index.js" in a string form
      getModulesObject: function(string) {
        // Return isolated modules object
        return string.match(/(modules+[:\s]*{)([\w\s\r\t\/:()'"\-\_.,]*)(})/g);
      },

      // sanitizeArray Function
      /***************************************/
      // Description: Removes all spaces from the module array
      // Parameters:
      //  * arr: (array) this is the array that will be used to check if a module has been added
      sanitizeArray: function(arr) {
        // Create a new array to contain the updated module data
        var newArr = [];
        // Loop through the moduel data array
        arr.forEach(function(v, i) {
          // Remove spaces and move it out to the newArr array
          newArr.push(v.replace(/\s/g, ''));
        });
        // Return the newArr after completing the loop through the module list
        return newArr;
      }
    };

    // Extract the object that contains module references
    var modulesObj = util.getModulesObject(data);
    // Add each module definitation to an array.
    var modulesArr = util.getArrayofModules(modulesObj);
    // Sanitize every item in that Array (spaces, tabs, breaks).
    var modulesArrSanitized = util.sanitizeArray(modulesArr);

    // If it's the first module, then make sure the array is empty
    if (modulesArrSanitized[0] === '') {
      // If the module is blank then set it to a default array again
      modulesArrSanitized = [];
    }

    // If the modules arrray is empty or the config.filter.componentsFilter is not found in the module
    if (modulesArrSanitized.length === 0 || !util.checkIfExists(modulesArrSanitized, config.filter.componentsFilter)) {
      // Push the component module to the module array
      modulesArrSanitized.push(config.filter.componentsFilter + ':require(\'./' + config.filter.componentsFilter + '/' + config.filter.componentsFilter + '.load\')');
      // Clean up the module array after
      modulesArrSanitized.sort();
    }
    // Using the data from the index.js file, replace the module array with the updated version
    data = data.replace(/(modules+[:\s]*{)([\w\s\r\t\/:()\-\_'".,]*)*(})/g, '$1' + modulesArrSanitized + '$3');
    // Rewrite the index.js file with the updated module list
    fs.writeFile(config.filter.indexPath, new Buffer(data), 'utf8', (err) => {
      if (err) {
        throw err;
      };
    });
    // Beautify the rewritten file
    gulp.src(config.filter.indexPath)
      .pipe(jsPrettify({
        config: '.jsbeautifyrc',
        mode: 'VERIFY_AND_WRITE'
      }))
      .pipe(gulp.dest('./app/scripts/modules'))
  });

  // Create the '/components/' directory, create the componentsFilter.main.js file, and
  // write the componentsArray into the file
  /**************************************************************************************/
  // Read from the ./gnorm/templates/components/filtermain.js file
  fs.readFile(config.filter.componentsModulePath, 'utf8', (err, data) => {
    if (err) {
      throw err;
    }
    // Using the output from the componentsFilter.main.js file, replace the filterOutput token with the componentsArray
    var moduleFile = data.replace(/(<%= filterOutput %>)/g, JSON.stringify(componentsArray));
    // Create a ./app/scripts/modules/componentFilter directory
    fs.mkdir(config.filter.modulesPath + config.filter.componentsFilter, () => {
      // Create the componentsFilter.main.js inside the newly create directory
      fs.writeFile(config.filter.modulesPath + config.filter.componentsFilter + '/' + config.filter.componentsFilter + '.main.js', new Buffer(moduleFile), 'utf8', (err) => {
        if (err) {
          throw err;
        }
      });
    });
  });

  // Create the componentsFilter.load.js file
  /**************************************************************************************/
  // Read from the ./gnorm/templates/components/filter/load.js file
  fs.readFile(config.filter.componentsLoadPath, 'utf8', (err, data) => {
    if (err) {
      throw err;
    };
    // Create the componentsFilter.main.js inside the ./app/scripts/modules/componentFilter
    fs.writeFile(config.filter.modulesPath + config.filter.componentsFilter + '/' + config.filter.componentsFilter + '.load.js', new Buffer(data), 'utf8', (err) => {
      if (err) {
        throw err;
      };
    });
  });

  // Create the _components.scss file
  /**************************************************************************************/
  // Read from the ./gnorm/templates/components/styles/_components.scss file
  if (!fs.existsSync(config.sass.partialDest)) {
    fs.readFile(config.sass.partialSrc, 'utf8', (err, data) => {
      if (err) {
        throw err;
      };
      // Create the _components.scss inside the ./app/styles/5_pages directory
      fs.writeFile(config.sass.partialDest, new Buffer(data), 'utf8', (err) => {
        if (err) {
          throw err;
        };
      });
    });
  }
});
