/**
 * @file: "global-scripts.js".
 * @last_updated: 06/14/2023
 * Add any needed JS script for the Enterprise Theme here.
 * This file has jQuery set as a dependency in the library
 * definition.
 */

// New Prototype function, replaceArray:
String.prototype.replaceArray = function(find, replacement) {
    var replaceString = this.trim();
    for (var i = 0; i < find.length; i++) {
        replaceString = replaceString.replaceAll(find[i], replacement[i]);
    }
    return replaceString;
};

// If this does not exist, set to false:
var suppressStickyCta = suppressStickyCta || false;

// ===================================== //
// Put ALL the doc.ready() scripts here: //
// ===================================== //
jQuery(document).ready(function($) {

    /**
     * Fix (R) and (TM) symbols in page titles:
     */
    $('.region-content h1, .region-content h2').each(function() {
        if (!$(this).hasClass('visually-hidden')) {
            var text = $(this).text();
            var find = ["®", "™"];
            var replacement = ["<sup>®</sup>", "<sup>™</sup>"];
            text = text.replaceArray(find, replacement);
            $(this).html(text);
        }
    });

    /**
     * Header Login CTA:
     */
    if ($('#hdrLoginCta').length) {
        $('#hdrLoginCta a').addClass('button button--red');
        $('#hdrLoginCta').appendTo(".global-header__main");
    }

    /**
     * Fix "Skip to content" link weirdness:
     */
    if($('#skip-to-content').length){
        $(document).on("click", "button", function(){
            $('#skip-to-content').blur();
            _scroll_target();
        });
    }

    /**
     * Add title to header CTAs
     */
    $(".hero .content-container a").attr("title",$(".hero .content-container a").first().text());
    $(".hero .content-container>a:last-child").attr("title",$(".hero .content-container>a:last-child").text());

    /**
     * Files paragraphs: Limit filenames to two lines on cards:
     */
    if($('.section--file-list').length){
      $('.file_name p').css(
        {
          'display': '-webkit-box',
          '-webkit-box-orient': 'vertical',
          '-webkit-line-clamp': 2,
          'overflow': 'hidden'
        }
      );
    }

    /**
     * Remove the word 'bytes' from the file size of CRC asset:
     */
    $(".crc_filesize").each(function(index, value) {
      if (value) {
        var text = value.innerText;
        text = text.replace('bytes', '')
        $(this).html(text);
      }
    });

    /**
     * Replace encoded HTML break with valid HTML:
     */
    $(".card h3").each(function(index, value) {
        if (value) {
            var text = value.innerText;
            var find = ["&lt;br/&gt;", "&lt;br /&gt;"];
            var replacement = ["<br />", "<br />"];
            text = text.replaceArray(find, replacement);
            $(this).html(text);
        }
    });

    /**
     * Scroll to the hash-target on page load:
     */
    _scroll_target();

    /**
     * Reset view filters on window resize:
     */
    if($('.filter-bar__filter-options .form-group').length){
        const dsktop = 992;
        let winwidth = $(window).width();
        // Credit to Chris Coyier
        // https://css-tricks.com/snippets/jquery/done-resizing-event/
        var resizeTimer;
        jQuery(window).on('resize', function(e) {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                winwidth = $(window).width();
                if(winwidth >= dsktop){
                    $('.filter-bar__filter-options .form-group').css('display', 'block');
                }
            }, 300);
        });
    }

    /**
     * Sticky CTA JS:
     */
    (function(){
        // IMPORTANT: If we need to suppress the Sticky CTA:
        if(suppressStickyCta == true){
            return;
        }

        // If admin toolbar present:
        if ($('#toolbar-bar').length) {
            adminHeaderHeight = 79;
        } else {
            adminHeaderHeight = 0;
        }

        // If GNaaS present:
        if ($('#ul-global-nav').length) {
            gnaasHeaderHeight = 42;
        } else {
            gnaasHeaderHeight = 0;
        }

        var sticky_cta_full = $('.sticky_cta_full')[0];
        var siderail_cta = $('.siderail--cta')[0];
        var useAirlock = false;
        var link_attributes = 'class="button button--red"';

        // If Right Rail CTA:
        if (siderail_cta) {
            var mainHeader = $('.auto-hide-header');
            var secondaryNavigation = $('.sticky-cta-nav');
            var belowNavHeroContent = $('.two-col');
            var headerHeight = mainHeader.height();

            headerLink = $('.siderail--cta a');
            headerUrl = headerLink.attr('href');
            headerText = headerLink.text();
            headerLinkHtml = headerLink.html();

            // TODO: These values should be clean before they get here.
            // And can we do some or all of this manipulation in twig templates rather than JS?
            headerText = headerText.replace('(link is external)', '');
            headerText = headerText.replace('(link sends email)', '');
            headerText = $.trim(headerText);

            if(headerLink.hasClass('ext')){
                useAirlock = true;
                link_attributes = 'class="button button--red new-ext"';
            }

            sticky_cta_full.innerHTML += '<a ' + link_attributes + ' href="' + headerUrl + '" title="' + headerText + '">' + headerLinkHtml + '</a>';
        }

        // Else look for the other Hdr CTAs.
        else {
            if ($('.hero__content .hero__buttons')[0]) {
                headerLink = $('.hero__content .hero__buttons a').first();
            }
            else if ($('.hero--default .content-container')[0]) {
                headerLink = $('.hero--default .content-container a').first();
            }
            else {
                return;
            }

            headerUrl = headerLink.attr('href');
            headerText = headerLink.text();
            headerLinkHtml = headerLink.html();
            belowNavHeroContent = headerLink;

            // TODO: These values should be clean before they get here.
            // And can we do some or all of this manipulation in twig templates rather than JS?
            headerText = headerText.replace('(link is external)', '');
            headerText = headerText.replace('(link sends email)', '');
            headerText = $.trim(headerText);

            if(headerLink.hasClass('ext')){
                useAirlock = true;
                link_attributes = 'class="button button--red new-ext"';
            }

            // If header CTA on page:
            if (typeof headerUrl !== 'undefined' && headerUrl) {
                var mainHeader = $('.auto-hide-header');
                var secondaryNavigation = $('.sticky-cta-nav');
                var headerHeight = mainHeader.height();

                sticky_cta_full.innerHTML += '<a ' + link_attributes + ' href="' + headerUrl + '" title="' + headerText + '">' + headerLinkHtml + '</a>';

            } else {
                return false;
            }
        }

        if(useAirlock){
            $('a.new-ext').on("click", function (e) {
                e.preventDefault();
                return Drupal.extlink.popupClickHandler(e, this);
            });
        }

        // Set page title:
        var sticky_title = $('.sticky_cta_title')[0];
        if (sticky_title) {
            page_title = document.title.split('|')[0];
            sticky_title.innerHTML += "<h3>" + page_title + "</h3>";
        }

        // Set scrolling variables:
        var scrolling = false,
            previousTop = 0,
            scrollDelta = 1,
            scrollOffset = 150;

        $(window).on('scroll', function() {
            if (!scrolling) {
                scrolling = true;
                (!window.requestAnimationFrame) ? setTimeout(autoHideHeader, 250) : requestAnimationFrame(autoHideHeader);
            }
        });

        $(window).on('resize', function() {
            headerHeight = mainHeader.height();
        });

        function autoHideHeader() {
            var currentTop = $(window).scrollTop();
            // Is the secondary navigation below intro?
            (belowNavHeroContent.length > 0) ? checkStickyNav(currentTop) : checkSimpleNav(currentTop);
            previousTop = currentTop;
            scrolling = false;
        }

        function checkSimpleNav(currentTop) {
            // If there's no secondary nav or the
            // secondary nav is below primary nav:
            if (previousTop - currentTop > scrollDelta) {
                // If scrolling up:
                mainHeader.removeClass('is-hidden');
            } else if (currentTop - previousTop > scrollDelta && currentTop > scrollOffset) {
                // If scrolling down:
                mainHeader.addClass('is-hidden');
            }
        }

        function checkStickyNav(currentTop) {
            //secondary nav below intro section - sticky secondary nav
            var secondaryNavOffsetTop = belowNavHeroContent.offset().top - secondaryNavigation.height() - mainHeader.height() + 100;
            if (previousTop >= currentTop) {
                //if scrolling up...
                if (currentTop < secondaryNavOffsetTop) {
                    //secondary nav is not fixed
                    mainHeader.removeClass('is-hidden');
                    secondaryNavigation.removeClass('fixed slide-up');
                    belowNavHeroContent.removeClass('secondary-nav-fixed');
                } else if (previousTop - currentTop > scrollDelta) {
                    //secondary nav is fixed
                    mainHeader.removeClass('is-hidden');
                    secondaryNavigation.removeClass('slide-up').addClass('fixed');
                    belowNavHeroContent.addClass('secondary-nav-fixed');
                    secondaryNavigation.css("top", headerHeight + adminHeaderHeight + gnaasHeaderHeight);
                }
            } else {
                //if scrolling down...
                if (currentTop > secondaryNavOffsetTop + scrollOffset) {
                    //hide primary nav
                    mainHeader.addClass('is-hidden');
                    secondaryNavigation.addClass('fixed slide-up');

                    if (adminHeaderHeight > 0) {
                        secondaryNavigation.css("top", secondaryNavigation.height() + 42 + adminHeaderHeight + gnaasHeaderHeight);
                    } else {
                        secondaryNavigation.css("top", gnaasHeaderHeight + 105);
                    }

                    belowNavHeroContent.addClass('secondary-nav-fixed');

                } else if (currentTop > secondaryNavOffsetTop) {
                    //once the secondary nav is fixed, do not hide primary nav if you haven't scrolled more than scrollOffset
                    mainHeader.removeClass('is-hidden');
                    secondaryNavigation.addClass('fixed').removeClass('slide-up');
                    belowNavHeroContent.addClass('secondary-nav-fixed');
                    secondaryNavigation.css("top", headerHeight + adminHeaderHeight + gnaasHeaderHeight);
                }

            }
        }
    })();

});

// Helper function to scroll to the Hash Target:
function _scroll_target(){
    var hsh = encodeURIComponent(window.location.hash);
    // Preserve hash + encode items not covered by encodeURIComponent.
    hsh = hsh.replace('%23', '#').replace("'", '%27').replace('(', '%28').replace(')', '%29');
    if(jQuery(hsh).length){
        var el = jQuery(hsh);
        if (hsh && el) {
            var hdrHeight = jQuery('.global-header').height();
            var ctaHeight = jQuery('.full-sticky-cta').height();
            jQuery('html, body').animate({
                scrollTop: el.offset().top - (hdrHeight + ctaHeight + 25)
            }, 'slow');
        }
    }
}

/**
 * "Back to top" button:
 * Removed temporarily for CHAT PILOT, 3/22/2023.
 */
(function(){

    var scrollToTopBtn = document.querySelector(".scrollToTopBtn");
    var rootElement = document.documentElement;
    var main_content = document.getElementById("main-content");
    // Content area height
    if(main_content) {
    var contentHeight = main_content.offsetHeight;
    } else {
    var contentHeight = 801;
    }

    // Language code in the URL:
    var pathArray = window.location.pathname.split('/');
    var langCode = pathArray[1];
    var drupal_lang;

    // The language:
    drupal_lang = drupalSettings.path.currentLanguage;

    // Translations:
    var tooltiptext = {};
    tooltiptext['en'] = 'Back <br>to top';
    tooltiptext['de'] = 'Zurück nach oben';
    tooltiptext['es'] = 'Volver arriba';
    tooltiptext['fr'] = 'Haut de page';
    tooltiptext['fr-ca'] = 'Retour en haut';
    tooltiptext['it'] = 'Torna all\'inizio';
    tooltiptext['ja'] = 'ページトップに戻る';
    tooltiptext['ko'] = '맨 위로';
    tooltiptext['pt-br'] = 'Voltar ao topo';
    tooltiptext['zh-hans'] = '返回到顶部';
    tooltiptext['zh-hant'] = '回到頁首';
    tooltiptext['vi'] = 'Trở lại đầu trang';

    // Function to insert content to the element:
    function content(divSelector, value) {
        var el = document.querySelector(divSelector);
        if (el !== null) {
          el.innerHTML = value;
        }
    }
    if (scrollToTopBtn !== null) {
        if(drupal_lang == 'en') {
            content('.scrollToTopBtn','<div class="btn_btt">'+tooltiptext[drupal_lang]+'</div>');
            document.querySelector(".scrollToTopBtn").classList.remove("arrow");
          }else{
            content('.tooltiptext',tooltiptext[drupal_lang]);
            document.querySelector(".scrollToTopBtn").classList.add("arrow");
          }
    }
    var lastScrollTop = 0;
    // Show/hide "back to top" on scroll:
    function handleScroll() {
        // footer height
        var footer = document.getElementsByClassName('footer');
        if (footer.length > 0) {
            var footerH = document.querySelector(".footer").offsetHeight;
        }
        // pre-footer height
        var preFooter = document.getElementsByClassName('region-bottom');
        if (preFooter.length > 0) {
            var preFooterH = document.querySelector(".region-bottom").offsetHeight;
        } else {
            var preFooterH = 0;
        }
        // check if site use GNaaS footer
        var footerBottom = document.getElementById("ul-global-footer");
        if(footerBottom) {
            var footerBottomrH = footerBottom.offsetHeight;
        } else {
            var footerBottomrH = 0;
        }
        // check if pagination exist
        var pagination = document.getElementsByClassName('pager__select');
        if (pagination.length > 0) {
            var paginationH = 92;
        } else {
            var paginationH = 0;
        }

        var mblFooterH = 0;
        var isMobile = false;

        if(navigator.userAgentData) {
            isMobile = navigator.userAgentData.mobile;
        }
        else if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
            isMobile = true;
        }

        if(isMobile) {
            mblFooterH = 100;
        }

        var screen_height = window.innerHeight;
        var page_height = document.body.scrollHeight;



        var footerHeight = footerH + preFooterH + footerBottomrH + paginationH + mblFooterH;
        var scrollTotal = rootElement.scrollHeight - rootElement.clientHeight

        // count footer height
        var margin_btm = rootElement.scrollTop + rootElement.clientHeight - rootElement.scrollHeight + footerHeight;
        if (page_height > 4*screen_height) {
          var st = window.pageYOffset || document.documentElement.scrollTop;
          if (st > lastScrollTop) {
             scrollToTopBtn.classList.remove("showBtn");
          } else if (st < lastScrollTop) {
            // Show button
            if ((rootElement.scrollTop / scrollTotal) > 0.30) {
              scrollToTopBtn.classList.add("showBtn");
              // Keep the scroll to top widget in the main content area and from entering pre-footer
              if (rootElement.scrollTop + rootElement.clientHeight > rootElement.scrollHeight - footerHeight) {
                  scrollToTopBtn.setAttribute("style", "margin-bottom: "+ margin_btm +"px; transition: none;");
              }else{
                  scrollToTopBtn.setAttribute("style", "margin-bottom: 0px;");
              }
            } else {
              scrollToTopBtn.classList.remove("showBtn");
            }
          } // else was horizontal scroll
          lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling
        }
    }

    // Scroll to top logic:
    function scrollToTop() {
        //rootElement.scrollTop = 0;
        jQuery('html, body').animate({
            scrollTop: "0"
        }, 1000);
        window.location.hash = "top";
        return false;
    }
    if (scrollToTopBtn !== null) {
        scrollToTopBtn.addEventListener("click", scrollToTop)
    }
    document.addEventListener("scroll", handleScroll)
    window.addEventListener("resize", handleScroll)
})();

/**
 * Browser Update JS:
 */
(function(){

    var $buoop = {
        required: {e:79},
        // Specifies required browser versions
        // Browsers older than this will be notified.
        // f:22 ---> Firefox < 22 gets notified
        // Negative numbers specify how much versions behind current version.
        // c:-5 ---> Chrome < 35  gets notified if latest Chrome version is 40.
        // more details (in english)

        reminder: 24,
        // after how many hours should the message reappear
        // 0 = show all the time

        reminderClosed: 150,
        // if the user explicitly closes message it reappears after x hours

        onshow: function(infos){},
        onclick: function(infos){},
        onclose: function(infos){},
        // callback functions after notification has appeared / was clicked / closed

        l: false,
        // set a fixed language for the message, e.g. "en". This overrides the default detection.

        test: false,
        // true = always show the bar (for testing)

        text: "Internet Explorer (IE) 11 desktop application will end support starting <b>June 15, 2022</b>. To get the best experience using our site, please upgrade to a newer browser.",
        // custom notification text (html)
        // Placeholders {brow_name} will be replaced with the browser name, {up_but} with contents of the update link tag and {ignore_but} with contents for the ignore link.
        // Example: "Your browser, {brow_name}, is too old: <a{up_but}>update</a> or <a{ignore_but}>ignore</a>."
        // more details (in english)

        text_in_xx: "",
        // custom notification text for language "xx"
        // e.g. text_de for german and text_it for italian

        newwindow: true,
        // open link in new window/tab

        url: null,
        // the url to go to after clicking the notification

        noclose:false,
        // Do not show the "ignore" button to close the notification

        nomessage: false,
        // Do not show a message if the browser is out of date, just call the onshow callback function

        jsshowurl: "//browser-update.org/update.show.min.js",
        // URL where the script, that shows the notification, is located. This is only loaded if the user actually has an outdated browser.

        container: document.body,
        // DOM Element where the notification will be injected.

        no_permanent_hide: false,
        // Do not give the user the option to permanently hide the notification

        api: 2022.05
        // This is the version of the browser-update api to use. Please do not remove.

    };

    function $buo_f(){
        var e = document.createElement("script");
        e.src = "//browser-update.org/update.min.js";
        document.body.appendChild(e);
    };

    try {
        document.addEventListener("DOMContentLoaded", $buo_f,false)
    }
    catch(e){
        window.attachEvent("onload", $buo_f)
    }

})();
