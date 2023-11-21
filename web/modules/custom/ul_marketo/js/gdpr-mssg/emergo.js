/**
 * NAME: Emergo GDPR (opt-in) message
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
        soi: 'Durch Absenden dieses Formulars stimme ich den <a href="/online-policies" class="link--policies" target="_blank">Online-Richtlinien von Emergo by UL</a> zu und erkl&auml;re mich damit einverstanden, regelm&auml;&szlig;ig E-Mails von Emergo by UL zu den Themen bew&auml;hrte Verfahren, Weiterbildung, industrielle Forschung, Neuigkeiten und Updates sowie zu Werbeaktionen im Zusammenhang mit Emergo by UL Produkten und Dienstleistungen zu erhalten. Ich wei&szlig;, dass ich diese Einwilligung jederzeit &uuml;ber das <a href="/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>  widerrufen kann.',
        doi: {
          pre: 'Durch Absenden dieses Formulars stimme ich den <a href="/online-policies" class="link--policies" target="_blank">Online-Richtlinien von Emergo by UL</a> zu.',
          txt: 'Ich erkl&auml;re mich damit einverstanden, regelm&auml;&szlig;ig E-Mails von Emergo by UL zu den Themen bew&auml;hrte Verfahren, Weiterbildung, industrielle Forschung, Neuigkeiten und Updates sowie zu Werbeaktionen im Zusammenhang mit Emergo by UL Produkten und Dienstleistungen zu erhalten. Ich wei&szlig;, dass ich diese Einwilligung jederzeit &uuml;ber das <a href="/preference-center" class="link--prefcntr" target="_blank">Preference Center</a> widerrufen kann.',
        },
        pipl: {
          txt: 'Durch Anklicken dieses Kontrollk&auml;stchens stimme ich den <a href="/online-policies" class="link--policies" target="_blank">Online-Richtlinien von Emergo by UL</a> zu und erkl&auml;re mich damit einverstanden, regelm&auml;&szlig;ig E-Mails von Emergo by UL zu den Themen bew&auml;hrte Verfahren, Weiterbildung, industrielle Forschung, Neuigkeiten und Updates sowie zu Werbeaktionen im Zusammenhang mit Emergo by UL Produkten und Dienstleistungen zu erhalten. Ich wei&szlig;, dass ich diese Einwilligung jederzeit &uuml;ber das <a href="/preference-center" class="link--prefcntr" target="_blank">Preference Center</a> widerrufen kann.',
          btm: 'Durch Anklicken dieses Kontrollk&auml;stchens willige ich ein, dass Emergo by UL meine personenbezogenen Daten in Übereinstimmung mit den <a target="_blank" class="link--policies" href="/de/online-policies">Online-Richtlinien von Emergo by UL</a> verarbeitet und sie grenz&uuml;berschreitend in die Vereinigten Staaten von Amerika weiterleitet.',
        }
      }
    break;

    // Japanese:
    case 'ja':
      mssg = {
        soi: 'このフォームを送信することで、<a href="/online-policies" class="link--policies" target="_blank">Emergo by ULのオンラインポリシー</a>に同意し、Emergo by ULの製品およびサービスに関連するベストプラクティス、セミナー等のご案内、業界研究、ニュース、アップデート、プロモーションに関するメールをEmergo by ULから定期的に受け取ることに同意します。メールは、Emergo by ULの<a href="/preference-center" class="link--prefcntr" target="_blank">プリファレンスセンター</a>にアクセスすれば、いつでも購読停止できることを理解しました。',
        doi: {
          pre: 'このフォームを送信することで、<a href="/online-policies" class="link--policies" target="_blank">Emergo by ULのオンラインポリシー</a>に同意します。',
          txt: 'Emergo by ULの製品およびサービスに関連するベストプラクティス、セミナー等のご案内、業界研究、ニュース、アップデート、プロモーションに関するメールをEmergo by ULから定期的に受け取ることを希望します。メールは、Emergo by ULの<a href="/preference-center" class="link--prefcntr" target="_blank">プリファレンスセンター</a>にアクセスすれば、いつでも購読停止できることを理解しました。',
        },
        pipl: {
          txt: 'このチェックボックスをクリックすることで、<a href="/online-policies" class="link--policies" target="_blank">Emergo by ULのオンラインポリシー</a>に同意し、Emergo by ULの製品およびサービスに関連するベストプラクティス、セミナー等のご案内、業界研究、ニュース、アップデート、プロモーションに関するメールをEmergo by ULから定期的に受け取ることに同意します。メールは、Emergo by ULの<a href="/preference-center" class="link--prefcntr" target="_blank">プリファレンスセンター</a>にアクセスすれば、いつでも購読停止できることを理解しました。',
          btm: 'このチェックボックスをクリックすることで、Emergo by ULが、<a target="_blank" class="link--policies" href="/ja/online-policies">Emergo by ULのオンラインポリシー</a>に従って個人情報の処理を行い、国境を越えて私の個人情報を米国に転送することに同意します。',
        }
      }
    break;

    // Korean:
    case 'ko':
      mssg = {
        soi: '이 양식을 제출함으로써 본인은 <a href="/online-policies" class="link--policies" target="_blank">Emergo by UL의 온라인 정책</a>에 동의하고, Emergo by UL의 제품 및 서비스와 관련된 모범 사례, 교육, 업계 연구, 뉴스, 업데이트 및 프로모션을 내용으로 포함하는 Emergo by UL의 정기 이메일을 수신하는 데 동의합니다. 본인은 언제든지 <a href="/preference-center" class="link--prefcntr" target="_blank">기본 설정 센터</a>에서 구독을 취소할 수 있음을 알고 있습니다.',
        doi: {
          pre: '이 양식을 제출함으로써 본인은 <a href="/online-policies" class="link--policies" target="_blank">Emergo by UL의 온라인 정책</a>에 동의합니다.',
          txt: '본인은 Emergo by UL 의 제품 및 서비스와 관련된 모범 사례, 교육, 업계 연구, 뉴스, 업데이트 및 프로모션을 내용으로 포함하는 Emergo by UL 의 정기 이메일을 수신하는 데 동의합니다. 본인은 언제든지 <a href="/preference-center" class="link--prefcntr" target="_blank">기본 설정 센터</a>에서 구독을 취소할 수 있음을 알고 있습니다.',
        },
        pipl: {
          txt: '이 확인란을 클릭함으로써 본인은 <a href="/online-policies" class="link--policies" target="_blank">Emergo by UL의 온라인 정책</a>에 동의하고 Emergo by UL의 제품 및 서비스와 관련된 모범 사례, 교육, 업계 연구, 뉴스, 업데이트 및 프로모션을 내용으로 포함하는 Emergo by UL의 정기 이메일을 수신하는 데 동의합니다. 본인은 언제든지 <a href="/preference-center" class="link--prefcntr" target="_blank">기본 설정 센터</a>에서 구독을 취소할 수 있음을 알고 있습니다.',
          btm: '이 확인란을 클릭함으로써, 본인은 Emergo by UL 가 <a target="_blank" class="link--policies" href="/ko/online-policies">Emergo by UL의 온라인 정책</a>에 따라 본인의 개인 정보를 처리하고 본인의 개인 정보를 미국으로 국외 전송하는 데 동의합니다.',
        }
      }
    break;

    // English (default):
    default:
      mssg = {
        soi: 'By submitting this form, I agree to <a target="_blank" class="link--policies" href="/online-policies">Emergo by ULs&apos; Online Policies</a> and agree to receive periodic emails from Emergo by UL  containing best practices, education, industry research, news, updates and promotions related to Emergo by UL products and services. I understand that I can unsubscribe at any time by visiting our <a href="/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>.',
        doi: {
          pre: 'By submitting this form, I agree to <a target="_blank" class="link--policies" href="/online-policies">Emergo by ULs&apos; Online Policies</a>',
          txt: 'I would like to receive periodic emails from Emergo by UL containing best practices, education, industry research, news, updates and promotions related to Emergo by UL products and services. I understand that I can unsubscribe at any time by visiting our <a href="/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>.',
        },
        pipl: {
          txt: 'By clicking this checkbox, I agree to <a target="_blank" class="link--policies" href="/online-policies">Emergo by ULs&apos; Online Policies</a> and agree to receive periodic emails from Emergo by UL containing best practices, education, industry research, news, updates and promotions related to Emergo by UL products and services. I understand that I can unsubscribe at any time by visiting our <a href="/preference-center" class="link--prefcntr" target="_blank">Preference Center</a>.',
          btm: 'By clicking this checkbox, I agree Emergo by UL may process my personal information in accordance with the <a target="_blank" class="link--policies" href="/online-policies">Emergo by ULs&apos; Online Policies</a> and to the cross-border transfer of my personal information to the United States.',
        }
      }
    break;

  }

  return mssg;
}
