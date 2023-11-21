
// Override the default template set
CKEDITOR.addTemplates( 'default', {
	// The name of sub folder which hold the shortcut preview images of the
	// templates.
	imagesPath: '/profiles/custom/ul_guidelines_profile/themes/custom/ul_guidelines_theme/images/ckeditor/',

	// The templates definitions.
	templates: [{
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
	}]
} );
