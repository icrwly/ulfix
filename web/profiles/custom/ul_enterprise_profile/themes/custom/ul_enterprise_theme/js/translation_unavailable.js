/**
 * @file: "translation_unavailable.js".
 * When user is redirected due to a missing/unavailable
 * translation, display a helpful message. This is to
 * show the System Message that is not possible due to
 * CloudFlare cache.
 */
(function ($, window) {
  // Helper function: Get URL query string parameters:
  function _getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
  };
  // The main redirect message function:
  function redirectMessage() {
    var translation = _getUrlParameter('translation');
    var languageCode = _getUrlParameter('langcode');
    var messages = {};
    messages['de'] = 'Dieser Inhalt ist noch nicht in deutscher Sprache verfügbar, daher wird die englische Version angezeigt.';
    messages['es'] = 'Este contenido aún no está disponible en español, por lo que le mostramos la versión en inglés.';
    messages['fr'] = 'Ce contenu n\'est pas encore disponible en français, nous affichons donc la version anglaise.';
    messages['fr-ca'] = 'Le contenu n\'est pas encore disponible en français. La version anglaise est donc affichée.';
    messages['it'] = 'Il contenuto non è ancora disponibile in lingua italiana, quindi lo stai visualizzando nella versione inglese.';
    messages['ja'] = '本コンテンツは未訳のため、英語で表示しています';
    messages['ko'] = '이 콘텐츠는 아직 [한국어]로 제작되지 않아 영어 버전으로 제공되고 있습니다.';
    messages['pt-br'] = 'Este conteúdo não está disponível em português. Por isso, estamos exibindo a versão em inglês.';
    messages['zh-hans'] = '此内容尚未提供[中文版本]，因此将显示英文版本。';
    messages['zh-hant'] = '本內容尚未有 繁體中文 版本，目前暫以英文版本顯示。';
    // If translation & lang code query-string values are set:
    if(translation == 0 && languageCode){
      // If there is no warning in the DOM:
      if (!$('.messages--warning').length){
        var mssg = '<div role="contentinfo" aria-label="Warning message" class="messages messages--warning">';
            mssg += '<h2 class="visually-hidden">Warning message</h2>';
            if (messages.hasOwnProperty(languageCode)){
              mssg += messages[languageCode];
            } else {
              mssg += 'This content is not yet available in the language selected so we are displaying the English version.';
            }
            mssg += '</div>';
        // add to the top of region-content
        $('.region-content').prepend(mssg);
      }
    }
  }
  // Call the main function:
  redirectMessage();
})(jQuery, window);
