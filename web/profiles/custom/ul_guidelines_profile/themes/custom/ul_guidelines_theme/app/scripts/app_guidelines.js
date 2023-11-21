// https://webpack.js.org/configuration/output/#output-publicpath,
// https://github.com/webpack/webpack/issues/2776#issuecomment-233208623
/* eslint-disable no-undef */
if (typeof asset_path !== "undefined") {
  __webpack_public_path__ = asset_path;
}

import $ from "jquery";
import moduleRegistry from "./modules/";

moduleRegistry.init();
// console.log(window.Drupal);

if (typeof window.Drupal != "undefined") {
  window.Drupal.behaviors.supportSubmit = {
    attach: function (context, settings) {
      if ($(".messages--error").length === 0) {
        if ($(context).hasClass("contact-message-submit-request-form")) {
          // Commenting out for now. Does not work as expected.
          // Currently fires every time a new attachment is added.
          // $('#submitResponseSuccessModalTrigger').trigger('click')
        }
      }
    },
  };
}

// Custom jQuery functions, can be invoked by Drupal
if (typeof window.jQuery != "undefined") {
  // triggerClick: pass a jQuery selector and trigger a click on that element
  window.jQuery.fn.triggerClick = function (selector) {
    $(selector).trigger("click");
  };

  // scrollToElement: pass a jQuery selector and scroll to first instance of that element
  window.jQuery.fn.scrollToElement = function (selector) {
    let $target = $(selector);
    if ($target.length) {
      $("html, body").animate(
        {
          scrollTop: $target.first().offset().top,
        },
        500
      );
    }
  };
}

window.zESettings = {
  webWidget: {
    helpCenter: {
      title: {
        "*": "Contact Marketing Support",
      },
    },
    contactForm: {
      title: {
        "*": "Contact Marketing Support",
      },
    },
  },
};
