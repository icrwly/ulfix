/**
 * Marketo Throbber / Loading animation.
 * @Last Updated: Jan 11, 2022
 * @Version: 1.0.1
 */

// Create object in global scope.
var cntDwn = {};

(function($) {

  // If there is no spinner wrap:
  if(!$('.spinner-wrap').length){
    return;
  }

  cntDwn.cnt = 0;
  cntDwn.max = 45;
  cntDwn.mssg = {};
  
  // Error messages:
  cntDwn.mssg['en'] = 'Sorry, an error occurred while loading the form. Please try refreshing the page. If that doesn’t help, you may need to disable your browser’s Ad Blocker, which may prevent the form from loading properly. Should this error persist, please contact our Customer Service team at cec@ul.com for further assistance.';
  cntDwn.mssg['es'] = 'Lo sentimos, se produjo un error al cargar el formulario. Intente actualizar la página. Si eso no ayuda, es posible que deba deshabilitar el bloqueador de anuncios de su navegador, ya que este puede evitar que el formulario se cargue correctamente. Si este error persiste, comuníquese con nuestro equipo de servicio al cliente escribiendo a cec@ul.com para obtener más ayuda.';
  cntDwn.mssg['pt-br'] = 'Pedimos desculpas, ocorreu um erro ao carregar o formulário. Tente atualizar a página. Se isto não ajudar, pode ser necessário desativar o Ad Blocker do seu navegador, que pode estar impedindo o formulário de carregar corretamente. Caso o erro persista, entre em contato com a nossa equipe de Atendimento ao Cliente, via cec@ul.com, para obter ajuda.';
  cntDwn.mssg['fr-ca'] = 'Nous sommes désolés, une erreur s’est produite lors du chargement du formulaire. Veuillez essayer de rafraîchir la page. Si cela ne règle pas le problème, vous devrez peut-être désactiver le bloqueur de publicité de votre navigateur, ce qui pourrait empêcher le chargement correct du formulaire. Si cette erreur persiste, veuillez communiquer avec notre service à la clientèle à l’adresse cec@ul.com pour obtenir de l’aide.';
  
  // English as default language:
  var errorMssg = cntDwn.mssg.en;
  
  // If `optinlang` is set:
  if(typeof optinlang !== 'undefined'){
    // If the `optinlang` has a message:
    if(cntDwn.mssg.hasOwnProperty(optinlang)){
      // Use that language for the error:
      errorMssg = cntDwn.mssg[optinlang];
    }
  }
  
  // The error when timed out.
  cntDwn.timedOut = function(){
    $('.activeForm, .spinner-wrap, .spinner-text').hide();
    $('.loading-container').addClass('messages messages--error').removeClass('loading-container').text(errorMssg).show();
  };
  
  // The interval.
  cntDwn.interval = setInterval(function(){
    if(jQuery !== undefined){
      if($('.activeForm').hasClass('formLoaded')){
        clearInterval(cntDwn.interval);
      }
    }
    if(cntDwn.cnt == cntDwn.max){
      cntDwn.timedOut();
      clearInterval(cntDwn.interval);
    }
    cntDwn.cnt++;
  }, 1000);

})(jQuery);
