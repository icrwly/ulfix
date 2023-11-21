<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for views unformatted rows templates.
 *
 * Default template: views-view-experience-hub.html.twig.
 * Note: Originally pulled from views.theme.inc.
 *
 * @param array $variables
 *   An associative array containing:
 *   - view: The view object.
 *   - rows: An array of row items. Each row is an array of content.
 */
function template_preprocess_hub__views_view_experience_hub(array &$variables) {
  $view = $variables['view'];
  $rows = $variables['rows'];
  $style = $view->style_plugin;
  $options = $style->options;

  $variables['default_row_class'] = !empty($options['default_row_class']);
  foreach ($rows as $id => $row) {
    $variables['rows'][$id] = [];
    $variables['rows'][$id]['content'] = $row;
    $variables['rows'][$id]['attributes'] = new Attribute();
    if ($row_class = $view->style_plugin->getRowClass($id)) {
      $variables['rows'][$id]['attributes']->addClass($row_class);
    }
  }
}

/**
 * Prepares variables for view templates.
 *
 * Default template: hub--views-view.html.twig.
 * Note: Originally pulled from views.theme.inc.
 *
 * @param array $variables
 *   An associative array containing:
 *   - view: The ViewExecutable object.
 */
function template_preprocess_hub__views_view(array &$variables) {
  $view = $variables['view'];
  $id = $view->storage->id();

  $variables['css_name'] = Html::cleanCssIdentifier($id);
  $variables['id'] = $id;
  $variables['display_id'] = $view->current_display;
  // Override the title to be empty by default. For example, if viewing a page
  // view, 'title' will already be populated in $variables. This can still be
  // overridden to use a title when needed. See views_ui_preprocess_views_view()
  // for an example of this.
  $variables['title'] = '';

  $css_class = $view->display_handler->getOption('css_class');
  if (!empty($css_class)) {
    // Views uses its own sanitization method. This is preserved to keep
    // backwards compatibility.
    // @todo https://www.drupal.org/project/drupal/issues/2977950 Decide what to
    //   do with the backwards compatibility layer.
    $bc_classes = explode(' ', preg_replace('/[^a-zA-Z0-9- ]/', '-', $css_class));
    // Sanitize the classes using the classes using the proper API.
    $sanitized_classes = array_map('\Drupal\Component\Utility\Html::cleanCssIdentifier', explode(' ', $css_class));
    $view_classes = array_unique(array_merge($bc_classes, $sanitized_classes));
    // Merge the view display classes into any existing classes if they exist.
    $variables['attributes']['class'] = !empty($variables['attributes']['class']) ? array_merge($variables['attributes']['class'], $view_classes) : $view_classes;
    $variables['css_class'] = implode(' ', $view_classes);
  }

  // contextual_preprocess() only works on render elements, and since this theme
  // hook is not for a render element, contextual_preprocess() falls back to the
  // first argument and checks if that is a render element. The first element is
  // view_array. However, view_array does not get set anywhere, but since we do
  // have access to the View object, we can also access the View object's
  // element, which is a render element that does have #contextual_links set if
  // the display supports it. Doing this allows contextual_preprocess() to
  // access this theme hook's render element, and therefore allows this template
  // to have contextual links.
  // @see views_theme()
  $variables['view_array'] = $variables['view']->element;

  // Attachments are always updated with the outer view, never by themselves,
  // so they do not have dom ids.
  if (empty($view->is_attachment)) {
    // Our JavaScript needs to have some means to find the HTML belonging to
    // this view.
    //
    // It is true that the DIV wrapper has classes denoting the name of the view
    // and its display ID, but this is not enough to unequivocally match a view
    // with its HTML, because one view may appear several times on the page. So
    // we set up a hash with the current time, $dom_id, to issue a "unique"
    // identifier for each view. This identifier is written to both
    // drupalSettings and the DIV wrapper.
    $variables['dom_id'] = $view->dom_id;
  }
}
