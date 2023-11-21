/**
 * Marketo Munchkin (data layer) JS
 * New Marketo Instance: "Enterprise"
 * @Last Updated: 3/08/2023
 * @Version: 1.1.1
 */
(function() {

  function initMunchkin() {
    if(didInit === false) {
      didInit = true;
      Munchkin.init('117-ZLR-399');
    }
  }

  var didInit = false;
  var s = document.createElement('script');
  s.type = 'text/javascript';
  s.async = true;
  s.src = '//munchkin.marketo.net/munchkin.js';
  s.onreadystatechange = function() {
    if (this.readyState == 'complete' || this.readyState == 'loaded') {
      initMunchkin();
    }
  };
  s.onload = initMunchkin;
  document.getElementsByTagName('head')[0].appendChild(s);

  // Marketo Munchkin Helper: Add query string to path.
  // Wait two seconds for Munchkin object to be available.
  if (window.location.search) {
    setTimeout(function(){
      var queryString = window.location.search.substring(1)
      var path = window.location.pathname + '/$query:';
      if(path == '//$query:') {
        path = '/$query:';
      }
      Munchkin.munchkinFunction('visitWebPage', {
        'url': path + queryString,
        'params': queryString,
      });
    }, 2000);
  }

})();
