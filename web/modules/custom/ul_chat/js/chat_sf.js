/**
 * UL Chat JS:
 * @last_updated: May 18, 2023
 * @version: 1.0.0
 */

(function($){

	// Create an object:
	let ul_chat = {};

	// JS to load SF Chat API:
	ul_chat.chat_JS_URL = drupalSettings.chat_settings.script_url;

	// The SF Chat Services URL:
	ul_chat.service = drupalSettings.chat_settings.sf_script_url;

	// Function to load the offline message:
	ul_chat.loadOfflineMssg = function (){
		$('body').append('<div id="offline_target" class="hidden">Loading....</div>');
		$('#offline_target').html('<section id="modal--offline"><div id="modal--top"> <button id="button-modal-close" title="Close">' + drupalSettings.chat_settings.chat_offlile_modal_title + '</button></div><div id="modal--content"><div id="team-offline"><img src = "/sites/g/files/qbfpbp251/themes/site/ul_com_theme/img/chat/team-offline.svg" alt="Team offline" /></div><h1>' + drupalSettings.chat_settings.chat_offlile_title + '</h1><p>' + drupalSettings.chat_settings.chat_offlile_message + '</p></div></section>')
	}

	// Chat Embedded Service Instantiation:
	ul_chat.initESW = function(gslbBaseURL) {
		// Show Chat button:
		embedded_svc.settings.displayHelpButton = true;

		// Language (ie "en" or "en-US"):
		embedded_svc.settings.language = '';

		// Auto-populate the pre-chat form fields:
		embedded_svc.settings.prepopulatedPrechatFields = {
			WebCountry__c: 'United States'
		};

		// Look for existing customer:
		embedded_svc.settings.extraPrechatInfo = [{
			"entityFieldMaps": [{
				"doCreate": true,
				"doFind": false,
				"fieldName": "LastName",
				"isExactMatch": false,
				"label": "Last Name"
			}, {
				"doCreate": true,
				"doFind": false,
				"fieldName": "FirstName",
				"isExactMatch": false,
				"label": "First Name"
			}, {
				"doCreate": true,
				"doFind": true,
				"fieldName": "Email",
				"isExactMatch": true,
				"label": "Email"
			}],
			"entityName": "Contact"
		}];

		// Live Agent (not bot).
		embedded_svc.settings.enabledFeatures = ['LiveAgent'];
		embedded_svc.settings.entryFeature = 'LiveAgent';

		embedded_svc.init(
			drupalSettings.chat_settings.embedded_svc,
			drupalSettings.chat_settings.sf_community_url,
			gslbBaseURL,
      drupalSettings.chat_settings.gslbBaseURL,
      drupalSettings.chat_settings.sf_chat_poc,
			{
        baseLiveAgentContentURL: drupalSettings.chat_settings.baseLiveAgentContentURL,
        deploymentId: drupalSettings.chat_settings.deploymentId,
        buttonId: drupalSettings.chat_settings.buttonId,
        baseLiveAgentURL: drupalSettings.chat_settings.baseLiveAgentURL,
        eswLiveAgentDevName: drupalSettings.chat_settings.eswLiveAgentDevName,
				isOfflineSupportEnabled: false
			}
		);
	};

	// Function to wait for an element to exist:
	ul_chat.waitForElem = function(selector) {
		return new Promise(resolve => {
			if (document.querySelector(selector)) {
				return resolve(document.querySelector(selector));
			}

			const observer = new MutationObserver(mutations => {
				if (document.querySelector(selector)) {
					resolve(document.querySelector(selector));
					observer.disconnect();
				}
			});

			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		});
	}

	// Load the offline Message:
	ul_chat.loadOfflineMssg();

	// If `embedded_svc` JS object does not exist,
	// then we need to include `chat_JS_URL` script.
	if (!window.embedded_svc) {
		var s = document.createElement('script');
		s.setAttribute('src', ul_chat.chat_JS_URL);
		s.onload = function() {
			ul_chat.initESW(null);
		};
		document.body.appendChild(s);
	}
	// Else, we do not need the JS, instantiate chat:
	else {
		ul_chat.initESW(ul_chat.service);
	}

	// If modal open/close button is clicked:
	$(document).on('click', 'button', function(){
		// Show Modal:
		if ($(this).hasClass('helpButtonDisabled')) {
			$('#offline_target').removeClass('hidden');
		}
		// Hide Modal:
		else if ($(this).attr('id') == 'button-modal-close') {
			$('#offline_target').addClass('hidden');
		}
		// Start Chat button:
		else if ($(this).hasClass('helpButtonEnabled')) {
			ul_chat.waitForElem('.embeddedServiceSidebar').then((elm) => {
				setTimeout(function(){
					$('.sidebarBody').attr('id', 'sidebarBody');
					$('#sidebarBody').focus();
					$('#sidebarBody').scrollTop(0);
				}, 3000);
			});
		}
	});

	// Place Accessibe UNDER Chat:
	setTimeout(function(){
		if($('.acsb-trigger').length) {
			$('.acsb-trigger').css('z-index', '999');
		}
	}, 2000);

})(jQuery);
