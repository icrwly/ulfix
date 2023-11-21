/**
 * @file
 **/

(function ($, window, Drupal) {

  Drupal.ulAlerts = {};

  Drupal.behaviors.ulAlertsService = {
    attach: function attach(context, settings) {

      var path = "/" + drupalSettings.path.currentPath;
      var offset = 0;
      var language = Drupal.ulAlerts.GetLanguage();
      var params = {
        '_format':'json',
        'path': path,
        'offset': offset,
        'lang': language
      };
      var query = $.param(params, true);

      var url = window.location.protocol + "//" + window.location.host + '/ul-alerts?' + query;

      $.ajax({
        url: url,
        method: "GET",
        headers: {
          'Content-Type': "application/vnd.api+json",
          'Accept': 'application/vnd.api+json"',
          'Accept-language': language
        },
        success: function(data, status, xhr){
          data.some(function (item) {
            var cookieName = 'ul-alert--' + item.id;
            // Check to see if user has no cookie set for this alert or if
            // they have not yet dismissed it.
            var show = (!$.cookie(cookieName) || $.cookie(cookieName) == 'shown');
            // Check if item has no language specified OR if current user language
            // is defined as the alert language.
            var validLanguage = (item.languages.length == 0) || (item.languages.includes(language));
            // Add alert if it has rendered content, has not been dismissed and
            // language matches the users current language.
            if (item.content && show && validLanguage) {
              $('#ul-alerts').append(item.content);
              return true;
            }
          });
        },
        complete: function(xhr, status) {
          $(once('setupAlerts', $('.alert'), context))
            .each(function () {
              let a = new Drupal.ulAlerts.Alert(e);
            });
        }
      });
    }
  }

  /**
   * Note: We only use the first part of the Language Codes,
   * and remove the region. This was agreed upon with
   * the content team, who wrote the alert messages.
   */
  Drupal.ulAlerts.GetLanguage = function() {
    // Site default language.
    var language = drupalSettings.ulAlerts.defaultLanguage;
    // The browser language.
    var browserLanguage = navigator.language || navigator.browserLanguage;
    // If the browser language.
    if (browserLanguage) {
      // If there is a dash (hyphen).
      if (browserLanguage.includes('-')) {
        // Get the root language, the first 2 characters only.
        language = browserLanguage.substring(0,2);
      }
      else {
        language = browserLanguage;
      }
    }

    return language;
  }

  Drupal.ulAlerts.Alert = function ($target) {
    this.$el = $($target)
    this.$closeButton = this.$el.find('.alert--dismiss')
    this.$primaryButton = this.$el.find('.button--primary')
    this.hideOnScroll = this.$el.data('hide-on-scroll')
    this.hideOnTimer = this.$el.data('hide-on-timer')
    this.cookieName = this.$el.data('id')
    this.speed = 500
    this.scrollThreshold = 100
    var self = this;

    this.close = function() {
      self.$el.slideUp(this.speed, function(e) {
        $.cookie(self.cookieName, 'dismissed', {'path': '/'})
      });
    }

    this.open = function() {
      self.$el.slideDown(this.speed, function(e) {
        $.cookie(self.cookieName, 'shown', {'path': '/'})
      });
    }

    this.$closeButton.on('click', function(e) {
      e.preventDefault();
      self.close()
    });

    if (!$.cookie(this.cookieName) || $.cookie(this.cookieName) == 'shown') {
      self.open()
    }

    if (this.hideOnScroll) {
      const originalScrollPosition = $(window).scrollTop()
      $(window).on('scroll.closeAlert', function(e) {
        let currentScrollPosition = $(window).scrollTop()
        if (self.getDifference(originalScrollPosition, currentScrollPosition) >= self.scrollThreshold) {
          self.close()
          $(window).off('scroll.closeAlert')
        }
      })
    }

    if (this.hideOnTimer) {
      setTimeout( function() {
        self.close()
      }, this.hideOnTimer)
    }

    this.getDifference = function(a, b) {
      return Math.abs(a - b);
    }
  }

})(jQuery, window, Drupal);
