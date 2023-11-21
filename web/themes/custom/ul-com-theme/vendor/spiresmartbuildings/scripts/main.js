/**
 * @file
 *
 * Slider code.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.sliderCaseStudiesHandler = {
    attach: function () {

      $(once('sliderCaseStudiesHandler', $('.paragraph--type--case-studies'))).each(function () {
        const $paragraph = $(this).find('.field--name-field-paragraph');
        $paragraph.slick({
          arrows: false,
          dots: true,
          swipeToSlide: true,
          autoplay: true,
          autoplaySpeed: 4000,
          pauseOnHover: true
        });

      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 *
 * Info graphic related JS.
 */

const d = document;
const windowWidth = window.innerWidth;
const acc = document.getElementsByClassName("accordion");
const panels = document.getElementsByClassName("panel");
const assessmentTitle = d.getElementById('assessmentTitle');
const assessmentDetails = d.getElementById('assessmentDetails');
const assessmentIcon = d.getElementById('assessmentIcon');
const assessmentBg = d.getElementsByClassName('paragraph--type--infografics-');
const themePath = '/themes/custom/smart_buildings/creative/';

/**
 * Handles the info graphics
 * @param {string} item
 *   Item to process
 */
function assessmentOverlay(item) {

  if (item === 'power') {
    TweenLite.set('.assessment-overlay', {top: '-50%', yPercent: 0, left: '0px', right: 'auto'});
    assessmentTitle.innerHTML = 'Power and Energy';
    assessmentDetails.innerHTML = 'Energy demand charges and purchases remain one of the largest components of a building’s operating budget. We evaluate criteria in:<br><br>&bull; Energy use management and analysis<br>&bull; Demand response<br>&bull; Grid interoperability<br>&bull;  Distributed energy resources';
    assessmentIcon.style.background = 'url(/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/spiresmartbuildings/icon-power.svg) no-repeat';
  } else if (item === 'health') {
    TweenLite.set('.assessment-overlay', {top: '-50%', yPercent: 0, left: '-12px', right: 'auto'});
    assessmentTitle.innerHTML = 'Health and Wellbeing';
    assessmentDetails.innerHTML = 'With emerging digital health and well-being tools and the impact of the COVID-19 pandemic, smart buildings must exceed expectations for health and well-being. We evaluate criteria in:<br><br>&bull; Indoor air quality<br>&bull; Thermal management<br>&bull; Visual human comfort<br>&bull; Light and noise control<br>&bull; Potable water quality<br>&bull; Odor management';
    assessmentIcon.style.background = 'url(/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/spiresmartbuildings/icon-health.svg) no-repeat';
  } else if (item === 'life') {
    if (windowWidth < 1024) {
      TweenLite.set('.assessment-overlay', {top: '-100%', yPercent: 0, left: '-12px', right: 'auto'});
    } else {
      TweenLite.set('.assessment-overlay', {top: '-60%', yPercent: 72, left: '-12px', right: 'auto'});
    }
    assessmentTitle.innerHTML = 'Life and Property Safety';
    assessmentDetails.innerHTML = 'Optimizing the safety of building occupants is the top priority for owners and operators. From fire safety to emergency response, building liability starts with preparation. We evaluate criteria in:<br><br>&bull; Building emergency plans<br>&bull; Integrated system performance<br>&bull; Situational awareness<br>&bull; Emergency communications';
    assessmentIcon.style.background = 'url(/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/spiresmartbuildings/icon-life.svg) no-repeat';
  } else if (item === 'connectivity') {
    TweenLite.set('.assessment-overlay', {top: '-50%', yPercent: 0, left: 'auto', right: '0px'});
    assessmentTitle.innerHTML = 'Connectivity';
    assessmentDetails.innerHTML = 'Often dubbed the fourth utility, connectivity has become the most essential utility of a smart building. We evaluate criteria in:<br><br>&bull; Media<br>&bull; Security<br>&bull; Coverage<br>&bull; Expansion<br>&bull; Resilience';
    assessmentIcon.style.background = 'url(/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/spiresmartbuildings/icon-connectivity.svg) no-repeat';
  } else if (item === 'cybersecurity') {
    TweenLite.set('.assessment-overlay', {top: '-10%', yPercent: -49, left: 'auto', right: '-12px'});
    assessmentTitle.innerHTML = 'Cybersecurity';
    assessmentDetails.innerHTML = 'Cyberattacks and back-door mechanisms threaten to disrupt critical smart building infrastructure. We measure and provide best practices through:<br><br>&bull; Identification<br>&bull; Protection<br>&bull; Detection<br>&bull; Response<br>&bull; Recovery';
    assessmentIcon.style.background = 'url(/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/spiresmartbuildings/icon-cybersecurity.svg) no-repeat';
  } else if (item === 'sustainability') {
    if (windowWidth < 1024) {
      TweenLite.set('.assessment-overlay', {top: '-60%', yPercent: 0, left: 'auto', right: '-12px'});
    } else {
      TweenLite.set('.assessment-overlay', {top: '-60%', yPercent: 44, left: 'auto', right: '-12px'});
    }
    assessmentTitle.innerHTML = 'Sustainability';
    assessmentDetails.innerHTML = 'A smart building is a sustainable building. We assess performance based on a wide array of existing frameworks and certification programs:<br><br>&bull; LEED<br>&bull; BREEAM<br>&bull; Green Globes<br>&bull; Living Building Challenge<br>&bull; WELL Building Standard<br>&bull; Fitwel<br>&bull; International Green Construction Code and CALGreen';
    assessmentIcon.style.background = 'url(/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/spiresmartbuildings/icon-sustainability.svg) no-repeat';
  }
  assessmentIcon.style.backgroundSize = 'contain';
  assessmentIcon.style.backgroundPosition = 'top right';

  TweenLite.set('.overlay-bg', {display: 'block'});
  TweenLite.to(['.assessment-type.power-energy', '.assessment-type.health-wellbeing', '.assessment-type.life-property', '.assessment-type.connectivity', '.assessment-type.cybersecurity', '.assessment-type.sustainability', '.assessment-graphic .lines'], .2, {
    autoAlpha: .22,
    ease: Expo.easeOut
  });
  if (windowWidth < 1366) {
    TweenLite.to('.assessment-graphic .building', .2, {autoAlpha: .22, ease: Expo.easeOut});
  }

  TweenLite.fromTo('.assessment-overlay', .2, {display: 'block', autoAlpha: 0}, {
    autoAlpha: 1, ease: Expo.easeOut, onComplete: function () {
      assessmentBg.onclick = function () {
        closeOverlay();
      };
    }
  });
}

/**
 * Closes the overlay.
 */
function closeOverlay() {
  TweenLite.set('.overlay-bg', {display: 'none'});
  TweenLite.to(['.assessment-type.power-energy', '.assessment-type.health-wellbeing', '.assessment-type.life-property', '.assessment-type.connectivity', '.assessment-type.cybersecurity', '.assessment-type.sustainability', '.assessment-graphic .lines'], .2, {
    autoAlpha: 1,
    ease: Expo.easeOut
  });
  if (windowWidth < 1366) {
    TweenLite.to('.assessment-graphic .building', .2, {autoAlpha: 1, ease: Expo.easeOut});
  }
  TweenLite.to('.assessment-overlay', .2, {autoAlpha: 0, ease: Expo.easeOut});
  assessmentBg.onclick = null;
}

/**
 * Assessment Desktop Functionality
 *
 * @param {string} item.
 *   Item name.
 */
function assessmentHover(item) {
  if (item === 'power') {
    TweenLite.to('.assessment-type.power-energy', .4, {y: -8, ease: Expo.easeOut});
  } else if (item === 'health') {
    TweenLite.to('.assessment-type.health-wellbeing', .4, {y: -8, ease: Expo.easeOut});
  } else if (item === 'life') {
    TweenLite.to('.assessment-type.life-property', .4, {y: -8, ease: Expo.easeOut});
  } else if (item === 'connectivity') {
    TweenLite.to('.assessment-type.connectivity', .4, {y: -8, ease: Expo.easeOut});
  } else if (item === 'cybersecurity') {
    TweenLite.to('.assessment-type.cybersecurity', .4, {y: -8, ease: Expo.easeOut});
  } else if (item === 'sustainability') {
    TweenLite.to('.assessment-type.sustainability', .4, {y: -8, ease: Expo.easeOut});
  }
}

/**
 * Hover out handler.
 *
 * @param {string} item.
 *   Item name.
 */
function assessmentHoverOut(item) {
  if (item === 'power') {
    TweenLite.to('.assessment-type.power-energy', .2, {y: 0, ease: Expo.easeOut});
  } else if (item === 'health') {
    TweenLite.to('.assessment-type.health-wellbeing', .2, {y: 0, ease: Expo.easeOut});
  } else if (item === 'life') {
    TweenLite.to('.assessment-type.life-property', .2, {y: 0, ease: Expo.easeOut});
  } else if (item === 'connectivity') {
    TweenLite.to('.assessment-type.connectivity', .2, {y: 0, ease: Expo.easeOut});
  } else if (item === 'cybersecurity') {
    TweenLite.to('.assessment-type.cybersecurity', .2, {y: 0, ease: Expo.easeOut});
  } else if (item === 'sustainability') {
    TweenLite.to('.assessment-type.sustainability', .2, {y: 0, ease: Expo.easeOut});
  }
}

// Accordian functionality on mobile
for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function () {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    }
  });
}

// Accorion open first child and make active
acc[0].classList.toggle("active");
panels[0].style.maxHeight = panels[0].scrollHeight + "px";
;
