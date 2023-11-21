// This particular flavor also includes a tiny Promise shim for cross-browser compatibility
import FontFaceObserver from 'fontfaceobserver/fontfaceobserver.js';
const html = document.documentElement

// Replace these with your project web fonts
const normal = new FontFaceObserver('OpenSans')
const bold = new FontFaceObserver('OpenSans', {
  'font-weight': 700
})

// Should reference any and all custom Font Families being used in our so we
// don't hide any text during the intial page load.

if (!html.classList.contains('fonts-loaded')) {
  html.classList.add('fonts-loading')

  Promise.all([
    normal.load(),
    bold.load()
  ]).then(
    function () {
      html.classList.remove('fonts-loading')
      html.classList.add('fonts-loaded')
      sessionStorage.fontsLoaded = true
    },
    // Timeout fallback if something fails with the promises.
    function () {
      html.classList.remove('fonts-loading')
      html.classList.add('fonts-failed')
    }
  )
}
