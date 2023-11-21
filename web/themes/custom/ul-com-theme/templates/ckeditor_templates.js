// Override the default template set
CKEDITOR.addTemplates( 'default', {
	// The name of sub folder which hold the shortcut preview images of the
	// templates.  Determine base path of drupal installation if any
	// (ckeditor could possibly be loaded w/o drupalSettings).
	imagesPath: '/themes/custom/ul-com-theme/ul_com_theme/img/ckeditor/',

	// The templates definitions.
	templates: [
  {
      title: 'Two Columns',
      image: 'template-two-col.png',
      description: 'Content separated into two vertical columns.',
      html: '<div class="editor-template flex-grid">' +
              '<div class="grid-item grid-item--half">' +
                '<p>Lorem ipsum dolor sit...</p>' +
            '</div>' +
              '<div class="grid-item grid-item--half">' +
                '<p>Lorem ipsum dolor sit...</p>' +
            '</div>' +
          '</div>'
  } ]
} );
