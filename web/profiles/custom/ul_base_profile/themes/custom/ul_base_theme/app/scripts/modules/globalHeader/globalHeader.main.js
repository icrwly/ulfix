'use strict'

import $ from 'jquery'
import LazyLoad from 'vanilla-lazyload'
import PubSub from 'pubsub-js'
import Stickyfill from 'stickyfilljs';
import throttle from 'raf-throttle'

let Drupal = window.Drupal || {}
let lazyLoadInstance = window.lazyLoadInstance || false

export default class globalHeader {
  constructor(el) {
    this.$el = $(el)
    this.$html = $('html, body')
    this.$mainContent = $('#main-content')
    this.$navGroup = this.$el.find('.title-menu--will-toggle')
    this.$primaryNav = this.$el.find('.global-header__primary-nav')
    this.$secondaryNav = this.$el.find('.global-header__secondary-nav')
    this.$search = this.$el.find('.global-header__search')
    this.$searchCancel = this.$el.find('.global-header__search-cancel')

    // Note: the "hamburger" menu is also known as the "title menu"
    this.$hamburger = this.$el.find('.global-header__hamburger')
    this.$hamburgerButton = this.$hamburger.find('.hamburger__button')
    this.$searchButton = this.$search.find('.accordion')
    this.$title = this.$el.find('.global-header__title')

    // This will pull from the hero or the main page title, whichever is available
    this.$targetTitle = $('[data-page-title]').first()
    this.titleText = this.$targetTitle.text()

    // The distance from top to title + title height - nav height
    this.threshold = this.$targetTitle.length ? this.$targetTitle.offset().top + this.$targetTitle.outerHeight() - $('.global-header--fix').outerHeight() : false

    // Max window width where hamburger is showing (and desktop menu is hidden)
    this.mobileMenuMaxWidth = 991;

    this.activeClass = 'is-active'
    this.activeAccordionClass = 'active-trail'
    this.titleMenuClass = 'title-menu-is-open'
    this.navClass = 'nav-is-open'
    this.speed = 500

    this.bindEventListeners()
    this.handleResize()
    this.$el.addClass('loaded')

    Stickyfill.add(this.$el.closest('.global-header--fix'))

    // The lazy load object.
    if(lazyLoadInstance == false){
      lazyLoadInstance = new LazyLoad({
        elements_selector: '.lazy'
      });
      Drupal.behaviors.updateLazy = {
        attach: function(context, settings) {
          lazyLoadInstance.update()
        }
      };
    }
  }

  bindEventListeners() {
    this.$searchButton.on('click.searchButton', (e) => {
      e.preventDefault()
      if (this.menuIsOpen) {
        this.closeMenu()
      }
    })

    this.$searchCancel.on('click.searchCancel', (e) => {
      e.preventDefault()
      this.$search.find('.active-trail') && this.closeSearch()
    })

    $(window).on('resize', throttle(() => {
      this.handleResize()
    }))

    if (this.titleText.length) {
      this.$title.text(this.titleText)

      $(window).on('scroll', throttle(() => {
        this.handleScroll()
      }))
    }

    const subscriber = (msg, data) => {
      if (data.group == 'main-menu') {
        $('body').on('click.offMainMenu', (e) => {
          if(!$(e.target).closest('.global-header').length && !$(e.target).closest('.mega-menu').length) {
            this.$el.find('.menu-item--primary-nav > .active-trail').trigger('click')
            $('body').off('click.offMainMenu')
          }
        })
      }
    }
    const token = PubSub.subscribe('ui.accordion.show', subscriber);

    // Temporary work around until we switch to position sticky
    this.$el.find('.global-header__row--top').length && this.$el.addClass('has-top-row')
  }

  // perform resize functions
  handleResize() {
    if ($(window).outerWidth() > this.mobileMenuMaxWidth) {
      !this.desktopIsSetup && this.setupDesktop()
    } else {
      !this.mobileIsSetup && this.setupMobile()
    }
  }

  // perform scroll functions
  handleScroll() {
    if (this.desktopIsSetup && !this.titleIsAnimating) {
      if ($(window).outerWidth() <= this.mobileMenuMaxWidth) {
        if (this.threshold && $(window).scrollTop() >= this.threshold) {
          !this.titleMenuIsOpen && this.showTitleMenu()
        } else {
          this.titleMenuIsOpen && this.hideTitleMenu()
        }
      }
    }
  }

  // set up for desktop
  setupDesktop() {
    this.mobileIsSetup && this.resetMobile()

    this.$hamburgerButton.on('click.hamburgerDesktop', (e) => {
      e.preventDefault()
      this.closeAccordions()
      if (this.titleIsShown) {
        this.openButton()
        this.hideTitle()
        this.titleMenuIsToggled = true
      } else {
        this.closeButton()
        this.showTitle()
        this.titleMenuIsToggled = false
      }
    })

    this.desktopIsSetup = true
  }

  // set up for mobile
  setupMobile() {
    this.desktopIsSetup && this.resetDesktop()

    this.$hamburgerButton.on('click.hamburgerMobile', (e) => {
      e.preventDefault()
      if (this.menuIsOpen) {
        this.closeMenu()
      } else {
        this.$search.find('.active-trail') && this.closeSearch()
        this.openMenu()
      }
    })

    this.$primaryNav.addClass('mobile-is-setup')
    this.mobileIsSetup = true
  }

  // reset the desktop settings
  resetDesktop() {
    this.$hamburgerButton.off('click.hamburgerDesktop')
    this.closeAccordions()
    this.resetTitleMenu()
    this.closeMenu()
    this.closeSearch()
    this.$el.removeClass('title-menu-is-toggled')
    this.desktopIsSetup = false
  }

  // reset the mobile settings
  resetMobile() {
    this.$primaryNav.removeClass('is-open mobile-is-setup')
    this.closeButton()
    this.$hamburgerButton.off('click.hamburgerMobile')
    this.mobileIsSetup = false
  }

  // resets the title menu
  resetTitleMenu() {
    this.$el.removeClass(this.titleMenuClass)
    this.closeButton()
    this.titleMenuIsOpen = false
    this.titleMenuIsToggled = false
  }

  // hamburger menu clicked to show mobile menu
  openButton() {
    this.$hamburgerButton
      .addClass(this.activeClass).attr('aria-expanded', true)
  }

  // hamburger menu clicked to hide mobile menu
  closeButton() {
    this.$hamburgerButton
      .removeClass(this.activeClass).attr('aria-expanded', false)
  }

  // opens mobile menu
  openMenu() {
    this.openButton()
    this.$primaryNav.addClass('is-open')
    this.$html.addClass(this.navClass)
    this.menuIsOpen = true
  }

  // closes mobile menu
  closeMenu() {
    PubSub.publish('ui.mobileMenu.close');
    this.closeButton()
    this.$primaryNav.removeClass('is-open')
    this.$html.removeClass(this.navClass)
    this.menuIsOpen = false
  }

  // shows the hamburger menu
  showTitleMenu() {
    this.$el.addClass(this.titleMenuClass)
    this.showTitle()
    this.titleMenuIsOpen = true
  }

  // hides the hamburger menu
  hideTitleMenu() {
    this.$el.removeClass(this.titleMenuClass)
    this.closeButton()
    this.hideTitle()
    this.titleMenuIsOpen = false
    this.titleMenuIsToggled = false
  }

  // shows the title (where the desktop menu was)
  showTitle() {
    this.$el.addClass('title-menu-is-toggled')
    this.titleIsShown = true
  }

  // hides the title
  hideTitle() {
    this.$el.removeClass('title-menu-is-toggled')
    this.titleIsShown = false
  }

  // close the mobile search bar
  closeSearch() {
    this.$searchButton
      .removeClass(this.activeAccordionClass).next().slideUp(this.speed)
  }

  // closes any open sub-menu panels
  closeAccordions() {
    this.$primaryNav.find('a.accordion.active-trail')
      .removeClass('active-trail').next().hide()
    this.$secondaryNav.find('a.accordion.active-trail')
      .removeClass('active-trail').next().hide()
  }
}
