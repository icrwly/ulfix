/**
 * NAME: Default GDPR (opt-in) message
 * LAST UPDATED: Dec 4, 2023
 * This overwrites a method with the
 * same name in "marketo-countries.js".
 */

function get_optin_mssg(lang){

  // Opt-in messages obj.
  var mssg = {};

  // Switch based upon language code:
  switch(lang){

    // German:
    case 'de':
      mssg = {
        soi: 'Durch Absenden dieses Formulars stimme ich den <a href="https://www.ul.com/de/resources/online-policies" class="link--policies" target="_blank">Online-Richtlinien von UL Solutions</a> zu und erkl&auml;re mich damit einverstanden, regelm&auml;&szlig;ig E-Mails von UL Solutions zu den Themen bew&auml;hrte Verfahren, Weiterbildung, industrielle Forschung, Neuigkeiten und Updates sowie zu Werbeaktionen im Zusammenhang mit UL Solutions Produkten und Dienstleistungen zu erhalten. Ich wei&szlig;, dass ich diese Einwilligung jederzeit &uuml;ber das <a href="https://www.ul.com/de/preference-center" class="link--prefcntr" target="_blank">Preference Center</a> widerrufen kann.',
        doi: {
          pre: 'Durch Absenden dieses Formulars stimme ich den <a href="https://www.ul.com/de/resources/online-policies" class="link--policies" target="_blank">Online-Richtlinien von UL Solutions</a> zu.',
          txt: 'Ich erkl&auml;re mich damit einverstanden, regelm&auml;&szlig;ig E-Mails von UL Solutions zu den Themen bew&auml;hrte Verfahren, Weiterbildung, industrielle Forschung, Neuigkeiten und Updates sowie zu Werbeaktionen im Zusammenhang mit UL Solutions Produkten und Dienstleistungen zu erhalten. Ich wei&szlig;, dass ich diese Einwilligung jederzeit &uuml;ber das <a href="https://www.ul.com/de/preference-center" class="link--prefcntr" target="_blank">Preference Center</a> widerrufen kann.',
        },
        pipl: {
          txt: 'Durch Anklicken dieses Kontrollk&auml;stchens stimme ich den <a href="https://www.ul.com/de/resources/online-policies" class="link--policies" target="_blank">Online-Richtlinien von UL Solutions</a> zu und erkl&auml;re mich damit einverstanden, regelm&auml;&szlig;ig E-Mails von UL Solutions zu den Themen bew&auml;hrte Verfahren, Weiterbildung, industrielle Forschung, Neuigkeiten und Updates sowie zu Werbeaktionen im Zusammenhang mit UL Solutions Produkten und Dienstleistungen zu erhalten. Ich wei&szlig;, dass ich diese Einwilligung jederzeit &uuml;ber das <a href="https://www.ul.com/de/preference-center" class="link--prefcntr" target="_blank">Preference Center</a> widerrufen kann.',
          btm: 'Durch Anklicken dieses Kontrollk&auml;stchens willige ich ein, dass UL Solutions meine personenbezogenen Daten in &Uuml;bereinstimmung mit den <a href="https://www.ul.com/de/resources/online-policies" class="link--policies" target="_blank">Online-Richtlinien von UL Solutions</a> verarbeitet und sie grenz&uuml;berschreitend in die Vereinigten Staaten von Amerika weiterleitet.'
        }
      }
    break;

    // Spanish:
    case 'es':
      mssg = {
        soi: 'Al enviar este formulario, acepto las <a href="https://www.ul.com/es/resources/online-policies" class="link--policies" target="_blank">pol&iacute;ticas en l&iacute;nea de UL Solutions</a> y acepto recibir correos electr&oacute;nicos peri&oacute;dicamente de UL Solutions que contengan buenas pr&aacute;cticas, educaci&oacute;n, investigaci&oacute;n de la industria, noticias, actualizaciones y promociones relacionadas con los productos y servicios de UL Solutions. Entiendo que puedo cancelar mi suscripci&oacute;n en cualquier momento visitando el <a href="https://www.ul.com/es/preference-center" class="link--prefcntr" target="_blank">centro de preferencias</a>.',
        doi: {
          pre: 'Al enviar este formulario, acepto las <a href="https://www.ul.com/es/resources/online-policies" class="link--policies" target="_blank">pol&iacute;ticas en l&iacute;nea de UL Solutions</a>.',
          txt: 'Me gustar&iacute;a recibir correos electr&oacute;nicos peri&oacute;dicamente de UL Solutions que contengan pr&aacute;cticas recomendadas, educaci&oacute;n, investigaciones relacionadas con la industria, noticias, actualizaciones y promociones relacionadas con productos y servicios de UL Solutions. Entiendo que puedo cancelar mi suscripci&oacute;n en cualquier momento visitando el <a href="https://www.ul.com/es/preference-center" class="link--prefcntr" target="_blank">centro de preferencias</a>.',
        },
        pipl: {
          txt: 'Al hacer clic en esta casilla de verificaci&oacute;n, acepto las <a href="https://www.ul.com/es/resources/online-policies" class="link--policies" target="_blank">pol&iacute;ticas en l&iacute;nea de UL Solutions</a> y acepto recibir correos electr&oacute;nicos peri&oacute;dicamente de UL Solutions que contengan buenas pr&aacute;cticas, educaci&oacute;n, investigaci&oacute;n de la industria, noticias, actualizaciones y promociones relacionadas con los productos y servicios de UL Solutions. Entiendo que puedo cancelar mi suscripci&oacute;n en cualquier momento visitando el <a href="https://www.ul.com/es/preference-center" class="link--prefcntr" target="_blank">centro de preferencias</a>.',
          btm: 'Al hacer clic en esta casilla de verificaci&oacute;n, acepto que UL Solutions puede procesar mi informaci&oacute;n personal de acuerdo con las <a href="https://www.ul.com/es/resources/online-policies" class="link--policies" target="_blank">Pol&iacute;ticas En L&iacute;nea de UL Solutions</a> y la transferencia transfronteriza de mi informaci&oacute;n personal a los Estados Unidos.'
        }
      }
    break;

    // French:
    case 'fr':
      mssg = {
        soi: 'En soumettant ce formulaire, j&rsquo;accepte les <a href="https://www.ul.com/fr/resources/online-policies" class="link--policies" target="_blank">Politiques en ligne d&rsquo;UL Solutions</a> et j&rsquo;accepte de recevoir des courriels p&eacute;riodiques d&rsquo;UL Solutions pr&eacute;sentant les meilleures pratiques, des formations, des recherches sur le secteur, des nouvelles, des mises &agrave; jour et des promotions li&eacute;es aux produits et services d&rsquo;UL Solutions. Je comprends que je peux me d&eacute;sabonner &agrave; tout moment en me rendant dans notre <a href="https://www.ul.com/fr/preference-center" class="link--prefcntr" target="_blank">Centre de pr&eacute;f&eacute;rences</a>.',
        doi: {
          pre: 'En soumettant ce formulaire, j&rsquo;accepte les <a href="https://www.ul.com/fr/resources/online-policies" class="link--policies" target="_blank">Politiques en ligne d&rsquo;UL Solutions</a>.',
          txt: 'Je souhaite recevoir des courriels p&eacute;riodiques d&rsquo;UL Solutions contenant des informations sur les meilleures pratiques, des formations, des recherches sur le secteur, des nouvelles, des mises &agrave; jour et des promotions li&eacute;es aux produits et services d&rsquo;UL Solutions. Je comprends que je peux me d&eacute;sabonner &agrave; tout moment en me rendant dans notre <a href="https://www.ul.com/fr/preference-center" class="link--prefcntr" target="_blank">Centre de pr&eacute;f&eacute;rences</a>.',
        },
        pipl: {
          txt: 'En cochant cette case, j&rsquo;accepte les <a href="https://www.ul.com/fr/resources/online-policies" class="link--policies" target="_blank">Politiques en ligne d&rsquo;UL Solutions</a> et j&rsquo;accepte de recevoir des courriels p&eacute;riodiques d&rsquo;UL Solutions contenant les meilleures pratiques, des formations, des recherches sur le secteur, des nouvelles, des mises &agrave; jour et des promotions li&eacute;es aux produits et services d&rsquo;UL Solutions. Je comprends que je peux me d&eacute;sabonner &agrave; tout moment en me rendant dans notre <a href="https://www.ul.com/fr/preference-center" class="link--prefcntr" target="_blank">Centre de pr&eacute;f&eacute;rences</a>.',
          btm: 'En cliquant sur cette case, j&#x2019;accepte qu&#x2019;UL Solutions traite mes informations personnelles conform&eacute;ment aux <a href="https://www.ul.com/fr/resources/online-policies" class="link--policies" target="_blank">politiques en ligne d&#x2019;UL Solutions</a> et au transfert transfrontalier de mes informations personnelles vers les &Eacute;tats-Unis.'
        }
      }
    break;

    // French-Canada.
    case 'fr-ca':
      mssg = {
        soi: 'En soumettant ce formulaire, j&rsquo;accepte les <a href="https://www.ul.com/fr-ca/resources/online-policies" class="link--policies" target="_blank">Politiques en ligne d&rsquo;UL Solutions</a> et j&rsquo;accepte de recevoir des courriels p&eacute;riodiques d&rsquo;UL Solutions pr&eacute;sentant les meilleures pratiques, des formations, des recherches sur le secteur, des nouvelles, des mises &agrave; jour et des promotions li&eacute;es aux produits et services d&rsquo;UL Solutions. Je comprends que je peux me d&eacute;sabonner &agrave; tout moment en me rendant dans notre <a href="https://www.ul.com/fr-ca/preference-center" class="link--prefcntr" target="_blank">Centre de pr&eacute;f&eacute;rences</a>.',
        doi: {
          pre: 'En soumettant ce formulaire, j&rsquo;accepte les <a href="https://www.ul.com/fr-ca/resources/online-policies" class="link--policies" target="_blank">Politiques en ligne d&rsquo;UL Solutions</a>.',
          txt: 'Je souhaite recevoir des courriels p&eacute;riodiques d&rsquo;UL Solutions contenant des informations sur les meilleures pratiques, des formations, des recherches sur le secteur, des nouvelles, des mises &agrave; jour et des promotions li&eacute;es aux produits et services d&rsquo;UL Solutions. Je comprends que je peux me d&eacute;sabonner &agrave; tout moment en me rendant dans notre <a href="https://www.ul.com/fr-ca/preference-center" class="link--prefcntr" target="_blank">Centre de pr&eacute;f&eacute;rences</a>.',
        },
        pipl: {
          txt: 'En cochant cette case, j&rsquo;accepte les <a href="https://www.ul.com/fr-ca/resources/online-policies" class="link--policies" target="_blank">Politiques en ligne d&rsquo;UL Solutions</a> et j&rsquo;accepte de recevoir des courriels p&eacute;riodiques d&rsquo;UL Solutions contenant les meilleures pratiques, des formations, des recherches sur le secteur, des nouvelles, des mises &agrave; jour et des promotions li&eacute;es aux produits et services d&rsquo;UL Solutions. Je comprends que je peux me d&eacute;sabonner &agrave; tout moment en me rendant dans notre <a href="https://www.ul.com/fr-ca/preference-center" class="link--prefcntr" target="_blank">Centre de pr&eacute;f&eacute;rences</a>.',
          btm: 'En cochant cette case, j&#x2019;accepte qu&#x2019;UL Solutions traite mes renseignements personnels conform&eacute;ment aux <a href="https://www.ul.com/fr-ca/resources/online-policies" class="link--policies" target="_blank">politiques en ligne d&#x2019;UL Solutions</a> et au transfert transfrontalier de mes renseignements personnels aux &Eacute;tats-Unis.'
        }
      }
    break;

    // Italian:
    case 'it':
      mssg = {
        soi: 'Inviando questo modulo, accetto le <a href="https://www.ul.com/it/resources/online-policies" class="link--policies" target="_blank">Policy online di UL Solutions</a> e acconsento di ricevere e-mail periodiche da UL Solutions su best practice, iniziative di formazione, risultati delle ricerche settoriali, novit&agrave;, aggiornamenti e promozioni relativi ai prodotti e ai servizi di UL Solutions. So di poter annullare l&rsquo;iscrizione in qualsiasi momento visitando il <a href="https://www.ul.com/it/preference-center" class="link--prefcntr" target="_blank">Centro preferenze</a> di UL Solutions.',
        doi: {
          pre: 'Inviando questo modulo, accetto le <a href="https://www.ul.com/it/resources/online-policies" class="link--policies" target="_blank">Policy online di UL Solutions</a>.',
          txt: 'Desidero ricevere e-mail periodiche da UL Solutions contenenti materiale formativo sulle best practice, risultati delle ricerche settoriali, novit&agrave;, aggiornamenti e promozioni sui prodotti e sui servizi di UL Solutions. So di poter annullare l&rsquo;iscrizione in qualsiasi momento visitando il <a href="https://www.ul.com/it/preference-center" class="link--prefcntr" target="_blank">Centro preferenze</a> di UL Solutions.',
        },
        pipl: {
          txt: 'Selezionando questa casella di controllo, accetto le <a href="https://www.ul.com/it/resources/online-policies" class="link--policies" target="_blank">Policy online di UL Solutions</a> e acconsento di ricevere e-mail periodiche da UL Solutions su best practice, iniziative di formazione, risultati delle ricerche settoriali, novit&agrave;, aggiornamenti e promozioni relativi ai prodotti e ai servizi di UL Solutions. So di poter annullare l&rsquo;iscrizione in qualsiasi momento visitando il <a href="https://www.ul.com/it/preference-center" class="link--prefcntr" target="_blank">Centro preferenze</a> di UL Solutions.',
          btm: 'Spuntando questa casella, acconsento al trattamento dei miei dati personali da parte di UL Solutions in conformit&agrave; alle <a href="https://www.ul.com/it/resources/online-policies" class="link--policies" target="_blank">policy online di UL Solutions</a> e al trasferimento transfrontaliero dei miei dati personali negli Stati Uniti.'
        }
      }
    break;

    // Japanese:
    case 'ja':
      mssg = {
        soi: 'このフォームを送信することで、<a href="https://www.ul.com/ja/resources/online-policies" class="link--policies" target="_blank">UL Solutionsのオンラインポリシー</a>に同意し、UL Solutionsの製品およびサービスに関連するベストプラクティス、セミナー等のご案内、業界研究、ニュース、アップデート、プロモーションに関するメールをUL Solutionsから定期的に受け取ることに同意します。メールは、UL Solutionsの<a href="https://www.ul.com/ja/preference-center" class="link--prefcntr" target="_blank">プリファレンスセンター</a>にアクセスすれば、いつでも購読停止できることを理解しました。',
        doi: {
          pre: 'このフォームを送信することで、<a href="https://www.ul.com/ja/resources/online-policies" class="link--policies" target="_blank">UL Solutionsのオンラインポリシー</a>に同意します。',
          txt: 'UL Solutionsの製品およびサービスに関連するベストプラクティス、セミナー等のご案内、業界研究、ニュース、アップデート、プロモーションに関するメールをUL Solutionsから定期的に受け取ることを希望します。メールは、UL Solutionsの<a href="https://www.ul.com/ja/preference-center" class="link--prefcntr" target="_blank">プリファレンスセンター</a>にアクセスすれば、いつでも購読停止できることを理解しました。',
        },
        pipl: {
          txt: 'このチェックボックスをクリックすることで、<a href="https://www.ul.com/ja/resources/online-policies" class="link--policies" target="_blank">UL Solutionsのオンラインポリシー</a>に同意し、UL Solutionsの製品およびサービスに関連するベストプラクティス、セミナー等のご案内、業界研究、ニュース、アップデート、プロモーションに関するメールをUL Solutionsから定期的に受け取ることに同意します。メールは、UL Solutionsの<a href="https://www.ul.com/ja/preference-center" class="link--prefcntr" target="_blank">プリファレンスセンター</a>にアクセスすれば、いつでも購読停止できることを理解しました。',
          btm: 'このチェックボックスをクリックすることで、<a href="https://www.ul.com/ja/resources/online-policies" class="link--policies" target="_blank">UL Solutionsが、UL Solutionsのオンラインポリシー</a>に従って個人情報の処理を行い、国境を越えて私の個人情報を米国に転送することに同意します。'
        }
      }
    break;

    // Korean:
    case 'ko':
      mssg = {
        soi: '이 양식을 제출함으로써 본인은 <a href="https://www.ul.com/ko/resources/online-policies" class="link--policies" target="_blank">UL Solutions의 온라인 정책</a>에 동의하고, UL Solutions의 제품 및 서비스와 관련된 모범 사례, 교육, 업계 연구, 뉴스, 업데이트 및 프로모션을 내용으로 포함하는 UL Solutions의 정기 이메일을 수신하는 데 동의합니다. 본인은 언제든지 <a href="https://www.ul.com/ko/preference-center" class="link--prefcntr" target="_blank">기본 설정 센터</a>에서 구독을 취소할 수 있음을 알고 있습니다.',
        doi: {
          pre: '이 양식을 제출함으로써 본인은 <a href="https://www.ul.com/ko/resources/online-policies" class="link--policies" target="_blank">UL Solutions의 온라인 정책</a>에 동의합니다.',
          txt: '본인은 UL Solutions의 제품 및 서비스와 관련된 모범 사례, 교육, 업계 연구, 뉴스, 업데이트 및 프로모션을 내용으로 포함하는 UL Solutions의 정기 이메일을 수신하는 데 동의합니다. 본인은 언제든지 <a href="https://www.ul.com/ko/preference-center" class="link--prefcntr" target="_blank">기본 설정 센터</a>에서 구독을 취소할 수 있음을 알고 있습니다.',
        },
        pipl: {
          txt: '이 확인란을 클릭함으로써 본인은 <a href="https://www.ul.com/ko/resources/online-policies" class="link--policies" target="_blank">UL Solutions의 온라인 정책</a>에 동의하고 UL Solutions의 제품 및 서비스와 관련된 모범 사례, 교육, 업계 연구, 뉴스, 업데이트 및 프로모션을 내용으로 포함하는 UL Solutions의 정기 이메일을 수신하는 데 동의합니다. 본인은 언제든지 <a href="https://www.ul.com/ko/preference-center" class="link--prefcntr" target="_blank">기본 설정 센터</a>에서 구독을 취소할 수 있음을 알고 있습니다.',
          btm: '이 확인란을 클릭함으로써, 본인은 UL Solutions가 <a href="https://www.ul.com/ko/resources/online-policies" class="link--policies" target="_blank">UL Solutions의 온라인 정책</a>에 따라 본인의 개인 정보를 처리하고 본인의 개인 정보를 미국으로 국외 전송하는 데 동의합니다.'
        }
      }
    break;

    // Portuguese:
    case 'pt-br':
      mssg = {
        soi: 'Ao enviar este formul&aacute;rio, concordo com as <a href="https://www.ul.com/pt-br/resources/online-policies" class="link--policies" target="_blank">Pol&iacute;ticas On-line da UL Solutions</a> e em receber e-mails peri&oacute;dicos da UL Solutions contendo pr&aacute;ticas recomendadas, instru&ccedil;&otilde;es, pesquisas do setor, not&iacute;cias, atualiza&ccedil;&otilde;es e promo&ccedil;&otilde;es relacionadas aos produtos e servi&ccedil;os da UL Solutions. Entendo que posso cancelar a inscri&ccedil;&atilde;o a qualquer momento por meio do <a href="https://www.ul.com/pt-br/preference-center" class="link--prefcntr" target="_blank">Centro de Prefer&ecirc;ncias</a> da UL Solutions.',
        doi: {
          pre: 'Ao enviar este formul&aacute;rio, concordo com as <a href="https://www.ul.com/pt-br/resources/online-policies" class="link--policies" target="_blank">Pol&iacute;ticas On-line da UL Solutions</a>.',
          txt: 'Eu gostaria de receber e-mails peri&oacute;dicos da UL Solutions contendo pr&aacute;ticas recomendadas, instru&ccedil;&otilde;es, pesquisas do setor, not&iacute;cias, atualiza&ccedil;&otilde;es e promo&ccedil;&otilde;es relacionadas aos produtos e servi&ccedil;os da UL Solutions. Entendo que posso cancelar a inscri&ccedil;&atilde;o a qualquer momento por meio do <a href="https://www.ul.com/pt-br/preference-center" class="link--prefcntr" target="_blank">Centro de Prefer&ecirc;ncias</a> da UL Solutions.',
        },
        pipl: {
          txt: 'Ao clicar nesta caixa de sele&ccedil;&atilde;o, concordo com as <a href="https://www.ul.com/pt-br/resources/online-policies" class="link--policies" target="_blank">Pol&iacute;ticas On-line da UL Solutions</a> e em receber e-mails peri&oacute;dicos da UL Solutions contendo pr&aacute;ticas recomendadas, instru&ccedil;&otilde;es, pesquisas do setor, not&iacute;cias, atualiza&ccedil;&otilde;es e promo&ccedil;&otilde;es relacionadas aos produtos e servi&ccedil;os da UL Solutions. Entendo que posso cancelar a inscri&ccedil;&atilde;o a qualquer momento por meio do <a href="https://www.ul.com/pt-br/preference-center" class="link--prefcntr" target="_blank">Centro de Prefer&ecirc;ncias</a> da UL Solutions.',
          btm: 'Ao clicar nesta caixa de sele&ccedil;&atilde;o, concordo que a UL Solutions pode processar minhas informa&ccedil;&otilde;es pessoais de acordo com as <a href="https://www.ul.com/pt-br/resources/online-policies" class="link--policies" target="_blank">Pol&iacute;ticas Online da UL Solutions</a> e com a transfer&ecirc;ncia de minhas informa&ccedil;&otilde;es pessoais para os Estados Unidos.'
        }
      }
    break;

    // Vietnamese:
    case 'vi':
      mssg = {
        soi: 'B&#7857;ng vi&#7879;c g&#7917;i bi&#7875;u m&#7851;u n&agrave;y, t&ocirc;i &dstrok;&#7891;ng &yacute; v&#7899;i <a href="https://www.ul.com/vi/resources/online-policies" class="link--policies" target="_blank">C&aacute;c ch&iacute;nh s&aacute;ch tr&#7921;c tuy&#7871;n c&#7911;a UL Solutions</a> v&agrave; &dstrok;&#7891;ng &yacute; nh&#7853;n email &dstrok;&#7883;nh k&#7923; t&#7915; UL Solutions &dstrok;&#7875; bi&#7871;t th&ecirc;m ki&#7871;n th&#7913;c m&#7899;i, nghi&ecirc;n c&#7913;u trong ng&agrave;nh, c&aacute;c ph&#432;&#417;ng ph&aacute;p th&#7921;c h&agrave;nh t&#7889;t nh&#7845;t, c&utilde;ng nh&#432; nh&#7919;ng tin t&#7913;c, c&#7853;p nh&#7853;t v&agrave; khuy&#7871;n m&#7841;i li&ecirc;n quan &dstrok;&#7871;n c&aacute;c s&#7843;n ph&#7849;m, d&#7883;ch v&#7909; c&#7911;a UL Solutions. T&ocirc;i hi&#7875;u r&#7857;ng t&ocirc;i c&oacute; th&#7875; h&#7911;y &dstrok;&abreve;ng k&yacute; b&#7845;t k&#7923; l&uacute;c n&agrave;o b&#7857;ng c&aacute;ch truy c&#7853;p <a href="https://www.ul.com/vi/preference-center" class="link--prefcntr" target="_blank">Trung t&acirc;m t&ugrave;y ch&#7885;n</a>.',
        doi: {
          pre: 'Khi g&#7917;i bi&#7875;u m&#7851;u n&agrave;y, t&ocirc;i &dstrok;&#7891;ng &yacute; v&#7899;i <a href="https://www.ul.com/vi/resources/online-policies" class="link--policies" target="_blank">C&aacute;c ch&iacute;nh s&aacute;ch tr&#7921;c tuy&#7871;n c&#7911;a UL Solutions</a>.',
          txt: 'T&ocirc;i &dstrok;&#7891;ng &yacute; nh&#7853;n email &dstrok;&#7883;nh k&#7923; t&#7915; UL Solutions &dstrok;&#7875; bi&#7871;t th&ecirc;m ki&#7871;n th&#7913;c m&#7899;i, nghi&ecirc;n c&#7913;u trong ng&agrave;nh, c&aacute;c ph&#432;&#417;ng ph&aacute;p th&#7921;c h&agrave;nh t&#7889;t nh&#7845;t, c&utilde;ng nh&#432; nh&#7919;ng tin t&#7913;c, c&#7853;p nh&#7853;t v&agrave; khuy&#7871;n m&#7841;i li&ecirc;n quan &dstrok;&#7871;n c&aacute;c s&#7843;n ph&#7849;m, d&#7883;ch v&#7909; c&#7911;a UL Solutions. T&ocirc;i hi&#7875;u r&#7857;ng t&ocirc;i c&oacute; th&#7875; h&#7911;y &dstrok;&abreve;ng k&yacute; b&#7845;t k&#7923; l&uacute;c n&agrave;o b&#7857;ng c&aacute;ch truy c&#7853;p <a href="https://www.ul.com/vi/preference-center" class="link--prefcntr" target="_blank">Trung t&acirc;m t&ugrave;y ch&#7885;n</a>.',
        },
        pipl: {
          txt: 'B&#7857;ng vi&#7879;c b&#7845;m v&amp;agrave;o &ocirc; ki&#7875;m n&agrave;y, t&ocirc;i &dstrok;&#7891;ng &yacute; v&#7899;i <a href="https://www.ul.com/vi/resources/online-policies" class="link--policies" target="_blank">C&aacute;c ch&iacute;nh s&aacute;ch tr&#7921;c tuy&#7871;n c&#7911;a UL Solutions</a> v&agrave; &dstrok;&#7891;ng &yacute; nh&#7853;n email &dstrok;&#7883;nh k&#7923; t&#7915; UL Solutions &dstrok;&#7875; bi&#7871;t th&ecirc;m ki&#7871;n th&#7913;c m&#7899;i, nghi&ecirc;n c&#7913;u trong ng&agrave;nh, c&aacute;c ph&#432;&#417;ng ph&aacute;p th&#7921;c h&agrave;nh t&#7889;t nh&#7845;t, c&utilde;ng nh&#432; nh&#7919;ng tin t&#7913;c, c&#7853;p nh&#7853;t v&agrave; khuy&#7871;n m&#7841;i li&ecirc;n quan &dstrok;&#7871;n c&aacute;c s&#7843;n ph&#7849;m, d&#7883;ch v&#7909; c&#7911;a UL Solutions. T&ocirc;i hi&#7875;u r&#7857;ng t&ocirc;i c&oacute; th&#7875; h&#7911;y &dstrok;&abreve;ng k&yacute; b&#7845;t k&#7923; l&uacute;c n&agrave;o b&#7857;ng c&aacute;ch truy c&#7853;p <a href="https://www.ul.com/vi/preference-center" class="link--prefcntr" target="_blank">Trung t&acirc;m t&ugrave;y ch&#7885;n</a>.',
          btm: 'B&#7857;ng vi&#7879;c &dstrok;&aacute;nh d&#7845;u ch&#7885;n &ocirc; n&agrave;y, t&ocirc;i &dstrok;&#7891;ng &yacute; cho ph&eacute;p UL Solutions x&#7917; l&yacute; th&ocirc;ng tin c&aacute; nh&acirc;n c&#7911;a t&ocirc;i theo <a href="https://www.ul.com/vi/resources/online-policies" class="link--policies" target="_blank">Ch&iacute;nh s&aacute;ch tr&#7921;c tuy&#7871;n c&#7911;a UL Solutions</a> v&agrave; truyền th&ocirc;ng tin c&aacute; nh&acirc;n c&#7911;a t&ocirc;i qua bi&ecirc;n gi&#7899;i &dstrok;&#7871;n Hoa K&#7923;.'
        }
      }
    break;

    // Chinese, Simp:
    case 'zh-hans':
      mssg = {
        soi: '通过提交此表格，我同意 <a href="https://www.ul.com/zh-hans/resources/online-policies" class="link--policies" target="_blank">UL Solutions 的在线政策</a>并同意定期接收 UL Solutions 的相关电子邮件，并了解这些电子邮件中内含有关 UL Solutions 产品和服务的最佳实践教育、行业研究、新闻、信息更新和促销方法。我了解我可以通过访问我们的<a href="https://www.ul.com/zh-hans/preference-center" class="link--prefcntr" target="_blank">“偏好中心”</a>取消订阅。',
        doi: {
          pre: '通过提交此表格，我同意 <a href="https://www.ul.com/zh-hans/resources/online-policies" class="link--policies" target="_blank">UL Solutions 的在线政策</a>。',
          txt: '我想定期接收 UL Solutions 的相关电子邮件，并了解这些电子邮件中内含有关 UL Solutions 产品和服务的最佳实践教育、行业研究、新闻、信息更新和促销方法。我了解我可以通过访问我们的<a href="https://www.ul.com/zh-hans/preference-center" class="link--prefcntr" target="_blank">“偏好中心”</a>取消订阅。',
        },
        pipl: {
          txt: '通过单击此复选框，我同意 <a href="https://www.ul.com/zh-hans/resources/online-policies" class="link--policies" target="_blank">UL Solutions 的在线政策</a>并同意定期接收 UL Solutions 的相关电子邮件，并了解这些电子邮件中内含有关 UL Solutions 产品和服务的最佳实践教育、行业研究、新闻、信息更新和促销方法。我了解我可以通过访问我们的<a href="https://www.ul.com/zh-hans/preference-center" class="link--prefcntr" target="_blank">“偏好中心”</a>取消订阅。',
          btm: '单击此复选框，即表示我同意 UL Solutions 可以根据 <a href="https://www.ul.com/zh-hans/resources/online-policies" class="link--policies" target="_blank">UL Solutions 的在线政策</a>处理我的个人信息，并将我的个人信息跨境传输到美国。'
        }
      }
    break;

    // Chinese, Trad:
    case 'zh-hant':
      mssg = {
        soi: '提交此表格，即表示本人同意 <a href="https://www.ul.com/zh-hant/resources/online-policies" class="link--policies" target="_blank">UL Solutions 之線上政策</a>，並同意定期接收來自 UL Solutions 的電子郵件。這些電子郵件內含有關 UL Solutions 產品和服務的最佳實務、教育、產業研究、新聞、更新和宣傳。我了解，我可以隨時瀏覽<a href="https://www.ul.com/zh-hant/preference-center" class="link--prefcntr" target="_blank">喜好設定中心</a>以取消訂閱。',
        doi: {
          pre: '提交此表格，即表示本人同意 <a href="https://www.ul.com/zh-hant/resources/online-policies" class="link--policies" target="_blank">UL Solutions 之線上政策</a>。',
          txt: '本人想定期接收來自 UL Solutions 的電子郵件，這些電子郵件內含有關 UL Solutions 產品和服務的最佳實務、教育、產業研究、新聞、更新和宣傳。我了解，我可以隨時瀏覽<a href="https://www.ul.com/zh-hant/preference-center" class="link--prefcntr" target="_blank">喜好設定中心</a>以取消訂閱。',
        },
        pipl: {
          txt: '按一下此核取方塊，即表示本人同意 <a href="https://www.ul.com/zh-hant/resources/online-policies" class="link--policies" target="_blank">UL Solutions 之線上政策</a>，並同意定期接收來自 UL Solutions 的電子郵件。這些電子郵件內含有關 UL Solutions 產品和服務的最佳實務、教育、產業研究、新聞、新和宣傳。我了解，我可以隨時瀏覽<a href="https://www.ul.com/zh-hant/preference-center" class="link--prefcntr" target="_blank">喜好設定中心</a>以取消訂閱。',
          btm: '通過單擊此複選框，我同意UL解決方案可以根據<a href="https://www.ul.com/zh-hant/resources/online-policies" class="link--policies" target="_blank">UL Solutions 的在線政策</a>處理我的個人信息，並將我的個人信息跨境轉移到美國。'
        }
      }
    break;

    // English (default):
    default:
      mssg = {
        soi: 'By submitting this form, I agree to <a href="https://www.ul.com/resources/online-policies" class="link--policies" target="_blank">UL Solutions&rsquo; Online Policies</a> and agree to receive periodic emails from UL Solutions containing best practices, education, industry research, news, updates and promotions related to UL Solutions&rsquo; products and services. I understand that I can unsubscribe at any time by visiting our <a href="https://www.ul.com/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>.',
        doi: {
          pre: 'By submitting this form, I agree to <a href="https://www.ul.com/resources/online-policies" class="link--policies" target="_blank">UL Solutions&rsquo; Online Policies</a>.',
          txt: 'I would like to receive periodic emails from UL Solutions containing best practices, education, industry research, news, updates and promotions related to UL Solutions&rsquo; products and services. I understand that I can unsubscribe at any time by visiting our <a href="https://www.ul.com/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>.',
        },
        pipl: {
          txt: 'By clicking this checkbox, I agree to <a href="https://www.ul.com/resources/online-policies" class="link--policies" target="_blank">UL Solutions&rsquo; Online Policies</a> and agree to receive periodic emails from UL Solutions containing best practices, education, industry research, news, updates and promotions related to UL Solutions&rsquo; products and services. I understand that I can unsubscribe at any time by visiting our <a href="https://www.ul.com/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>.',
          btm: 'By clicking this checkbox, I agree UL Solutions may process my personal information in accordance with the <a href="https://www.ul.com/resources/online-policies" class="link--policies" target="_blank">UL Solutions&rsquo; Online Policies</a> and to the cross-border transfer of my personal information to the United States.',
        }
      }
    break;

  }

  return mssg;
}
