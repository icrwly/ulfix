
// Override the default template set
CKEDITOR.addTemplates( 'default', {
  // The folder with the preview images.
  imagesPath: '/profiles/custom/ul_enterprise_profile/themes/custom/ul_enterprise_theme/images/ckeditor/',

  // The templates definitions:
  templates: [{
      // 50/50.
      title: 'Two Columns (50/50)',
      image: 'template-two-col.png',
      description: 'Content separated into two equal-width columns.',
      html: '<div class="editor-template flex-grid">' +
              '<div class="grid-item grid-item--half">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
              '<div class="grid-item grid-item--half">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
            '</div>'
    },
    {
      // 30/70.
      title: 'Two Columns (30/70)',
      image: 'template-30-70.png',
      description: 'Content separated into two columns, left 30% wide and right 70%.',
      html: '<div class="editor-template flex-grid">' +
              '<div class="grid-item grid-item--thirty">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
              '<div class="grid-item grid-item--seventy">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
            '</div>'
    },
    {
      // 70/30.
      title: 'Two Columns, (70,30)',
      image: 'template-70-30.png',
      description: 'Content separated into two columns, left 70% wide and right 30%.',
      html: '<div class="editor-template flex-grid">' +
              '<div class="grid-item grid-item--seventy">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
              '<div class="grid-item grid-item--thirty">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
            '</div>'
    },
    {
      // Responsive Bulleted List (3-column layout).
      title: 'Three Columns',
      image: 'template-30-30-30.png',
      description: 'Content separated into three columns',
      html: '<div class="editor-template row">' +
              '<div class="span-4-d">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
              '<div class="span-4-d">' +
                '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
              '<div class="span-4-d">' +
              '<p>Lorem ipsum dolor sit...</p>' +
              '</div>' +
            '</div>'
    },
    {
      // Icons with centered text.
      title: 'Icons + centered text',
      image: 'template-30-30-30.png',
      description: 'Row of 4-6 icons with centered text. Image and text are wrapped with a link.',
      html: '<div class="editor-template icons-flex-grid">' +
              '<div class="grid-icon">' +
                '<a href="/">' + 
                '<img src="/sites/g/files/qbfpbp251/files/2019-04/Globe_ULRed-CMYK_MD.png" alt="alttext">' +
                '<p>Lorem ipsum dolor sit...</p>' +
                '</a>' +
              '</div>' +
              '<div class="grid-icon">' +
                '<a href="/">' + 
                '<img src="/sites/g/files/qbfpbp251/files/2019-04/Globe_ULRed-CMYK_MD.png" alt="alttext">' +
                '<p>Lorem ipsum dolor sit...</p>' +
                '</a>' +
              '</div>' +
              '<div class="grid-icon">' +
                '<a href="/">' + 
                '<img src="/sites/g/files/qbfpbp251/files/2019-04/Globe_ULRed-CMYK_MD.png" alt="alttext">' +
                '<p>Lorem ipsum dolor sit...</p>' +
                '</a>' +
              '</div>' +
              '<div class="grid-icon">' +
                '<a href="/">' + 
                '<img src="/sites/g/files/qbfpbp251/files/2019-04/Globe_ULRed-CMYK_MD.png" alt="alttext">' +
                '<p>Lorem ipsum dolor sit...</p>' +
                '</a>' +
              '</div>' +
            '</div>'
    }]
});
