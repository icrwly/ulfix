(function ($, window) {
  // Check if lift javascript is being loaded AKA lift module enabled.
  var liftTimeout;

  window.addEventListener('DOMContentLoaded', function(e) {
    delayedHeroOpacityChange();
  })

  function setOpacityAndClearTimeout() {
    $('.hero.hero--default').css('opacity', '1');
    window.clearTimeout(liftTimeout);
  }

  var len = $('script[src="https://builder.lift.acquia.com/lift.js"]').length;
  if (len > 0) {
    window.addEventListener('acquiaLiftStageCollection', function (e) {
      setOpacityAndClearTimeout();
    });
  } else {
    window.addEventListener('load', function(e) {
      setOpacityAndClearTimeout();
    });
  }
  if (typeof window.AcquiaLift !== 'undefined') {
    if (typeof window.AcquiaLift.inExperienceBuilderIframe !== 'undefined'
      && window.AcquiaLift.inExperienceBuilderIframe) {
      $('html').addClass('in-lift-builder');
    }
  }
  // Check if things are loading correctly, if not fallback to display hero.
  function delayedHeroOpacityChange() {
    liftTimeout = window.setTimeout(function() {
      $('.hero.hero--default').css('opacity', '1');
    }, 3*1000)
  }
})(jQuery, window);
