# Gnorm Component Library Generator

This contains information on running and configuring the custom 'components' gulp task. This task is intended to be largely dynamic based on the components-config.js file and the actual file system.

## Setup
1. Change the `show` value of the `index.json` (`./app/json/`) to `true`
2. From the root of the project, run this command from terminal: `gulp components`
3. Run `gulp` from the command line and confirm that a "Components Library" is now present in the list

## Configuring

The Components Config (`./gnorm/templates/components/components-config.js`) provides options for customizing your Components Library. Not all of these options should be touched because many of them are file paths to that allow the task to run correctly. The following a description of the fields you should modify.

  * `title`: *(string)* This title will be displayed at the top of the Components Library page.
  * `dest`: *(string)* This is the file path of where you want the \_components.twig file to live.
  * `includesDataPath`: *(string)* This is the path to the includes folder, which is used to generate the list of components.
  * `includesRenderPath`: *(string)* This is the path from the \_components.twig file to the includes folder, which will be used on the twig includes in the \_components.twig file. For example, if the components file was on the top level it would go to "includes/", or if the components file was within a folder it would go to "../includes/"
  * `filter.filterBy`: Contains the values to configure the Components Libraries' filter. Important options that are found in this object are:
    * `includeFilter`: *(boolean)* This option controls the rendering of the Component Filter on the Components Library page.
    * `abcOrder`: *(boolean)* This option controls the rendering of alphabetical order filtering (both ascending and descending will be included).
    * `type`: *(boolean)* This option controls the rendering of the Type Filter. This will create a select box with the components type in it ("pieces", "components", "regions"), each component will be assigned to a group based on the folder it is in ("2_pieces", "3_components", "4_regions").
    * `clear`: *(boolean)* This option controls the rendering of a "clear" option on the filter.
  * `add`: **_(Optional)_** This array contains objects that are used to manually add components into the Components Library. For example, multiple examples of an element can be included showing different content. The options are found here include:
    * `title`: *(string)* This will be the title that is displayed at the top of the component on the Component Library.
    * `folder`: *(string)* This is the containing sub folder that contains the element ("2_pieces", "3_components", "4_regions").
    * `base`: *(string)* This value will control the placement of the component within in the page. When the task is compiling the Components Library page it will make a comparison between this value and the component's file name. If it has a match then it will render this new element _after_ the existing one. For example, if you wanted to show another version of an card (card.twig) element you would put. `"card.twig"` as a value here.
    * `withAttribute`: *(string)* This option allows the control of twigs "with" attribute on the new element. By design the components task will display the first found "with" attribute for each include. Meaning, if you are using this element on multiple templates (T2, T7, and T8) and each have a "with" attribute, the rendered component will only every use the data from T2 by default (it stops the loop once a match is found).
  * `add.block`: **_(Optional)_** This object can be used to replace a twig "block" upon rendering. The following are the options to configure this.
    * `variable`: *(string)* This will define the "block" variable, the twig markup will be printed like this {% block `[variable]` %}.
    * `blockTemplate` *(string)* This is the containing sub folder that contains the blocks include ("2_pieces", "3_components", "4_regions"). This will be used _inside_ the blocks rendering.
    * `blockBase` *(string)* This is the name of the file that will be used on the blocks include ("2_pieces", "3_components", "4_regions"). This will be used _inside_ the blocks rendering.
  * `excludes`: This controls the rendering of components, include any within this array to exclude them from rendering. To exclude a component add it's file name (`card.twig`) as a string to this array.

## Helpful Hints

* The Components Library page has access to _all_ the twig JSON files, so when adding an include within the components-config.js you don't have to worry about where the JSON data exists it will always be available.
* Since this task is dynamic (grabbing the first data it comes across) there is good chance something within the rendering will not be rendering meaningful data. To customize your output, exclude the component (`card.twig`) in the `excludes` array and then add an `include` object with `card.twig` in the includes’ `base` option `card.twig`. When the page is being rendered it will first exclude it from the `exclude` array and then check the `include`, this will essentially replace the content of the original component.
