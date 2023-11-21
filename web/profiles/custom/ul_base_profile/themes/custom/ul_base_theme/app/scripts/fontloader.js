// This particular flavor also includes a tiny Promise shim for cross-browser compatibility
import FontFaceObserver from 'fontfaceobserver/fontfaceobserver.js';
const html = document.documentElement;

// Replace these with your project web fonts
const light = new FontFaceObserver('OpenSans', {
  'font-weight': 300
})
const lightItalic = new FontFaceObserver('OpenSans', {
  'font-weight': 300,
  'font-style': 'italic'
})
const normal = new FontFaceObserver('OpenSans')
const italic = new FontFaceObserver('OpenSans', {
  'font-style': 'italic'
})
const semiBold = new FontFaceObserver('OpenSans', {
  'font-weight': 600
})
const semiBoldItalic = new FontFaceObserver('OpenSans', {
  'font-weight': 600,
  'font-style': 'italic'
})
const bold = new FontFaceObserver('OpenSans', {
  'font-weight': 700
})
const boldItalic = new FontFaceObserver('OpenSans', {
  'font-weight': 700,
  'font-style': 'italic'
})
const extraBold = new FontFaceObserver('OpenSans', {
  'font-weight': 800
})
const extraBoldItalic = new FontFaceObserver('OpenSans', {
  'font-weight': 800,
  'font-style': 'italic'
})

// Should reference any and all custom Font Families being used in our so we
// don't hide any text during the initial page load.
if (!html.classList.contains('fonts-loaded')) {
  html.classList.add('fonts-loading')

  Promise.all([
    light.load(),
    lightItalic.load(),
    normal.load(),
    italic.load(),
    semiBold.load(),
    semiBoldItalic.load(),
    bold.load(),
    boldItalic.load(),
    extraBold.load(),
    extraBoldItalic.load()
  ]).then(
    function() {
      html.classList.remove('fonts-loading')
      html.classList.add('fonts-loaded')
      sessionStorage.fontsLoaded = true
    },
    // Timeout fallback if something fails with the promises.
    function() {
      html.classList.remove('fonts-loading')
      html.classList.add('fonts-failed')
    }
  )
}
