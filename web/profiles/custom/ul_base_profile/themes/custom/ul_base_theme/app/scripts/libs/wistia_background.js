(function ($, Drupal) {
  'use strict';


$(document).ready(function() {
      if ($("#wistiaBackground").length) {
        var videoControls = $('#hero-video-controls'),
          vidId = $("#wistiaBackground").attr('data-vid-id'),
          vidFallback = $("#wistiaBackground").attr('data-fallback');

        window._wq = window._wq || [];

        // customer player to control play/pause button
        function videoCustomPlayer(video) {
          var playing = true,
            pause = "M11,10 L18,13.74 18,22.28 11,26 M18,13.74 L26,18 26,18 18,22.28",
            play = "M11,10 L17,10 17,26 11,26 M20,10 L26,10 26,26 20,26",
            $animation = videoControls.find('animate');

          // set init for ie
          if(playing) {
            videoControls.attr('aria-pressed', true);
          }

          if (detectIEEdge()) {
            videoControls.addClass('ie-mode');
            buttonControls(video, playing, pause, play, $animation, true);
          } else {
            buttonControls(video, playing, pause, play, $animation, false);
          }

        };

        function buttonControls(video, playing, pause, play, $animation, IE) {
          videoControls.on('click', function () {
            if (!playing) {
              video.play();
              videoControls.attr('aria-pressed', true);
              if (!IE) {
                $animation.attr({
                  "from": pause,
                  "to": play
                }).get(0).beginElement();
              }
              playing = true;
            } else {
              video.pause();
              videoControls.attr('aria-pressed', false);
              if (!IE) {
                $animation.attr({
                  "from": play,
                  "to": pause
                }).get(0).beginElement();
              }
              playing = false;
            }
          })
        };

        function detectIEEdge() {
          var ua;
          // IE has not navigator.userAgentData.
          if ( window.navigator.userAgentData ) {
            return false;
          }
          else {
            ua = window.navigator.userAgent;
          }

          var msie = ua.indexOf('MSIE ');

          if (msie > 0) {
            // IE 10 or older => return version number
            return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
          }

          var trident = ua.indexOf('Trident/');
          if (trident > 0) {
            // IE 11 => return version number
            var rv = ua.indexOf('rv:');
            return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
          }

          var edge = ua.indexOf('Edge/');
          if (edge > 0) {
            // Edge => return version number
            return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
          }

          // other browser
          return false;
        };

        // push video init state with customer player
        _wq.push({
          id: vidId,
          options: {
            autoPlay: true,
            muted: true,
            stillUrl: vidFallback,
            endVideoBehavior: 'loop',
            playbar: false,
            plugin: {
              cropFill: {
                src: "//fast.wistia.com/labs/crop-fill/plugin.js"
              }
            }
          },
          onReady: function (video) {
            videoCustomPlayer(video);
          }
        });
      }
    })

})(jQuery);
