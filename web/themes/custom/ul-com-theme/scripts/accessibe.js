/**
 * Accessibe JS functions.
 * @Version: 1.2
 * @Last_Updated: Ded 4, 2023
 */

setTimeout(function () {

  // Is there a cookie banner?
  var cookieBannerExists = jQuery("#consent_blackbar").length;

  // Cookie banner height + 20 = widget offset from bottom:
  var bannerOffset = 20;

  // Function to move the widget:
  function moveAcsbWidget() {
    if (cookieBannerExists) {
      jQuery('<style class="acsb_widget_css">:host{ --offset-top: 20px !important;}</style>').appendTo('head');
      cookieBannerExists = false;
    }
  }

  // The widget is placed vertically above the cookie
  // banner (if it exists) or just above the bottom
  // left edge of the page.
  function getCookieBannerHeight() {
    if (cookieBannerExists) {
      // The cookie banner element:
      var trustarc = document.getElementById("consent_blackbar");
      // If the banner exists, add height to this var:
      if (typeof (trustarc) != 'undefined' && trustarc != null) {
        bannerOffset = trustarc.clientHeight + 20;
      }
    }
  }

  // NOTE: Only do all this if the cookie banner exists:
  if (cookieBannerExists) {
    // Attach the event listener to window's resize:
    window.addEventListener("resize", getCookieBannerHeight);

    // The OneTrust button:
    onetrustBtn = document.getElementById("truste-consent-button");

    // If there is a OneTrust cookie banner button, add event listeners
    // so we can move the Accessibe widget down once the banner is
    // removed:
    if (typeof (onetrustBtn) != 'undefined' && onetrustBtn != null) {
      document.getElementById("truste-consent-button").addEventListener('click', moveAcsbWidget);
      document.getElementById("truste-consent-required").addEventListener('click', moveAcsbWidget);
      document.getElementById("truste-show-consent").addEventListener('click', moveAcsbWidget);
      jQuery('.truste-close2').on('click', function () {
        moveAcsbWidget();
      });
    }

    // Get the value before instantiating the ACSB widget:
    getCookieBannerHeight();
  }

  // Define accessibe widget:
  var s = document.createElement('script');
  var h = document.querySelector('head') || document.body;
  s.src = 'https://acsbapp.com/apps/app/dist/js/app.js';
  s.async = true;
  s.onload = function () {
    acsbJS.init({
      statementLink: '',
      footerHtml: '',
      hideMobile: false,
      hideTrigger: false,
      disableBgProcess: false,
      language: 'en',
      position: 'left',
      leadColor: '#122C49',
      triggerColor: '#122C49',
      triggerRadius: '50%',
      triggerPositionX: 'left',
      triggerPositionY: 'bottom',
      triggerIcon: 'people',
      triggerSize: 'medium',
      triggerOffsetX: 20,
      triggerOffsetY: bannerOffset,
      mobile: {
        triggerSize: 'medium',
        triggerPositionX: 'left',
        triggerPositionY: 'bottom',
        triggerOffsetX: 10,
        triggerOffsetY: bannerOffset,
        triggerRadius: '50%'
      }
    });
  };

  // Load the ext. accessibe script:
  h.appendChild(s);

}, 2000);
