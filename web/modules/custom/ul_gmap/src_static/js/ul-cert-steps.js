//Set up function that will take the data and display
function filterChange(json,countryList,regList){
    //Reset the rowContainer to have the new JSON data
    var rowContainer = document.querySelector('.ul-cert-rows-container'),
        card_index = 1, //set index to change modal on arrow click
        firstCountry = '', //keep track of current country
        rowUpdate = '', //update the html of the rows
        countryFilter = '', //keep track of current country for filter
        regulateFilter = ''; //keep track of current regulation for filter

        rowContainer.innerHTML = ''; //reset the rows to empty then update
    if(countryList.length < 1 || countryList == undefined) { //if all countries
        
        // Display the prepared json object
        for(let i=0; i<json.length; i++){
            //Check to see if the current country matches
            if(json[i].region_country[0] !== firstCountry){
                //Set array to loop through cards
                var currentCountries = [];
                firstCountry = json[i].region_country[0]; //set the current country
                var count = 0;

                //Loop to get amount of json objects for a particular country and save that data in the currentcountries array
                for(let j = 0; j<json.length; j++){
                    if(firstCountry == json[j].region_country[0]){
                        if(json[j].requirements[0] !== undefined){
                            currentCountries.push(json[j]);
                            if(regList.length < 1 || regList == undefined) {
                                count++;
                            }
                            else if(regList.some(item => json[j].requirements.includes(item))) {
                                count++;
                            }
                        }
                    }
                }

                if(count >= 1){

                    //Set up the intial row and pass country as title and count
                    rowUpdate += `
                    <div class='ul-cert-row'>
                    <div class='ul-cert-title-wrapper'>
                    <h2 class='ul-cert-row-title fs-3'>${json[i].region_country[0]}</h2>
                    <p class='ul-cert-row-amount fs-3'>(${count})</p>
                    </div>
                    <!-- One more for loop for each card present in the row -->
                    <div class='ul-cert-card-container'>
                    `;
                    
                    //Loop through all the cards for the country
                    currentCountries.sort((a, b) => a.requirements[0].localeCompare(b.requirements[0]));

                    for(let j=0; j < currentCountries.length; j++){
                        
                        if(regList.length < 1 || regList == undefined) {
                            
                            rowUpdate += `
                            <div class='ul-cert-card' data-custom-open="modal-1" data-index="${card_index}" data-title="${currentCountries[j].name_of_mark}" data-country="${currentCountries[j].region_country[0]}" data-require="${currentCountries[j].requirements}" data-validperiod="${currentCountries[j].validity_period}" data-mark="${currentCountries[j].mark_required}" data-icon="${currentCountries[j].profile_mark}">
                            <div class='ul-cert-card-type'><p class='ul-cert-card-type-text fs--2'>${currentCountries[j].requirements.join(', ')}</p></div>
                            <div class='ul-cert-card-icon-wrapper'>
                            <img class='ul-cert-card-icon' src='${currentCountries[j].profile_mark}' alt='Card Icon'>
                            </div>
                            <p class='ul-cert-card-text fs--1'>${currentCountries[j].name_of_mark}</p>
                            </div>
                            `; 

                            card_index++;
                        }
                        else if (regList.some(item => currentCountries[j].requirements.includes(item))){
                            
                            rowUpdate += `
                            <div class='ul-cert-card' data-custom-open="modal-1" data-index="${card_index}" data-title="${currentCountries[j].name_of_mark}" data-country="${currentCountries[j].region_country[0]}" data-require="${currentCountries[j].requirements}" data-validperiod="${currentCountries[j].validity_period}" data-mark="${currentCountries[j].mark_required}" data-icon="${currentCountries[j].profile_mark}">
                            <div class='ul-cert-card-type'><p class='ul-cert-card-type-text fs--2'>${currentCountries[j].requirements.join(', ')}</p></div>
                            <div class='ul-cert-card-icon-wrapper'>
                            <img class='ul-cert-card-icon' src='${currentCountries[j].profile_mark}' alt='Card Icon'>
                            </div>
                            <p class='ul-cert-card-text fs--1'>${currentCountries[j].name_of_mark}</p>
                            </div>
                            `; 

                            card_index++;
                            // https://picsum.photos/100/100
                            // /../src/assets/cert_images/${currentCountries[j].name_of_mark}.png
                            // regList.includes(currentCountries[j].requirements[0])
                            
                        }
                        else {
                        }
                    }
                    //Close the row container
                    rowUpdate += `
                    </div>
                    </div>
                    `;  
                }
            }

        }
    }
    else {
        // Display the prepared json object
        for(let i=0; i<json.length; i++){
            //Check to see if the current country matches
            if(json[i].region_country[0] !== firstCountry && countryList.includes(json[i].region_country[0])){ //check the array of countries selected
                //Set array to loop through cards
                var currentCountries = [];
                firstCountry = json[i].region_country[0]; //set the current country
                var count = 0;

                //Loop to get amount of json objects for a particular country and save that data in the currentcountries array
                for(let j = 0; j<json.length; j++){
                    if(firstCountry == json[j].region_country[0]){

                        if(json[j].requirements[0] !== undefined){
                            currentCountries.push(json[j]);
                            if(regList.length < 1 || regList == undefined) {
                                count++;
                            }
                            else if(regList.some(item => json[j].requirements.includes(item))) {
                                count++;
                            }
                        }
                    }
                }

                if(count >= 1){

                    //Set up the intial row and pass country as title and count
                    rowUpdate += `
                    <div class='ul-cert-row'>
                    <div class='ul-cert-title-wrapper'>
                    <h2 class='ul-cert-row-title fs-3'>${json[i].region_country[0]}</h2>
                    <p class='ul-cert-row-amount fs-3'>(${count})</p>
                    </div>
                    <!-- One more for loop for each card present in the row -->
                    <div class='ul-cert-card-container'>
                    `;
                    
                    //Loop through all the cards for the country
                    currentCountries.sort((a, b) => a.requirements[0].localeCompare(b.requirements[0]));

                    for(let j=0; j < currentCountries.length; j++){
                        
                        if(regList.length < 1 || regList == undefined) {
                            
                            rowUpdate += `
                            <div class='ul-cert-card' data-custom-open="modal-1" data-index="${card_index}" data-title="${currentCountries[j].name_of_mark}" data-country="${currentCountries[j].region_country[0]}" data-require="${currentCountries[j].requirements}" data-validperiod="${currentCountries[j].validity_period}" data-mark="${currentCountries[j].mark_required}" data-icon="${currentCountries[j].profile_mark}">
                            <div class='ul-cert-card-type'><p class='ul-cert-card-type-text fs--2'>${currentCountries[j].requirements.join(', ')}</p></div>
                            <div class='ul-cert-card-icon-wrapper'>
                            <img class='ul-cert-card-icon' src='${currentCountries[j].profile_mark}' alt='Card Icon'>
                            </div>
                            <p class='ul-cert-card-text fs--1'>${currentCountries[j].name_of_mark}</p>
                            </div>
                            `; 

                            card_index++;
                        }
                        else if (regList.some(item => currentCountries[j].requirements.includes(item))){
                            
                            rowUpdate += `
                            <div class='ul-cert-card' data-custom-open="modal-1" data-index="${card_index}" data-title="${currentCountries[j].name_of_mark}" data-country="${currentCountries[j].region_country[0]}" data-require="${currentCountries[j].requirements}" data-validperiod="${currentCountries[j].validity_period}" data-mark="${currentCountries[j].mark_required}" data-icon="${currentCountries[j].profile_mark}">
                            <div class='ul-cert-card-type'><p class='ul-cert-card-type-text fs--2'>${currentCountries[j].requirements.join(', ')}</p></div>
                            <div class='ul-cert-card-icon-wrapper'>
                            <img class='ul-cert-card-icon' src='${currentCountries[j].profile_mark}' alt='Card Icon'>
                            </div>
                            <p class='ul-cert-card-text fs--1'>${currentCountries[j].name_of_mark}</p>
                            </div>
                            `; 

                            card_index++;
                            // https://picsum.photos/100/100
                            // /../src/assets/cert_images/${currentCountries[j].name_of_mark}.png
                            // regList.includes(currentCountries[j].requirements[0])
                            
                        }
                        else {
                        }
                    }
                    //Close the row container
                    rowUpdate += `
                    </div>
                    </div>
                    `;  
                }
            }

        }
    }

    // No results message:
    if(rowUpdate == '') {
        // Get the link to the marketo form page from the contact us button.
        var contactUrl = document.querySelectorAll('.js-contact-page')[0];
        rowUpdate = `
            <div class='ul-no-result-container'>
                <h2 class='ul-no-result-title fs-6'>No results</h2>
                <p class='ul-no-result-clear fs-2'>We couldn&rsquo;t find an exact match for your search. Please select different search filters and <a style='color:#00518A;text-decoration:underline;' href='/market-access-portal/steps'>try again</a>. Need assistance? <a style='color:#00518A;text-decoration:underline;' href='${contactUrl.dataset.contact}'>Contact us</a>.</p>
            </div>
        `;
    }

    rowContainer.innerHTML += rowUpdate;
    json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));
}


//Check all the data and hide the text of missing data, only reveal modal field if it is available
function modalUpdateContent(cardHeader, cardBody, item){
    //title
    if(item.dataset.title !== ''){
        cardHeader.querySelector('.js-modal-title').style.display = 'block';
        cardHeader.querySelector('.js-modal-title').innerHTML = `${item.dataset.title}`;
    }
    else {
        cardHeader.querySelector('.js-modal-title').style.display = 'none';
    }

    //country
    if(item.dataset.country !== ''){
        cardBody.querySelector('.js-modal-country').style.display = 'block';
        cardBody.querySelector('.js-modal-country span').innerHTML = `${item.dataset.country}`;
    }
    else {
        cardBody.querySelector('.js-modal-country').style.display = 'none';
    }

    //requirements
    if(item.dataset.require !== ''){
        var split_reqs = item.dataset.require.split(',').join(', ');
        cardBody.querySelector('.js-modal-requirement').style.display = 'block';
        cardBody.querySelector('.js-modal-requirement span').innerHTML = `${split_reqs}`;
    }
    else {
        cardBody.querySelector('.js-modal-requirement').style.display = 'none';
    }

    //valididity period
    if(item.dataset.validperiod !== ''){
        cardBody.querySelector('.js-modal-valid').style.display = 'block';
        cardBody.querySelector('.js-modal-valid span').innerHTML = `${item.dataset.validperiod}`;
    }
    else {
        cardBody.querySelector('.js-modal-valid').style.display = 'none';
    }

    //mark
    if(item.dataset.mark !== ''){
        cardBody.querySelector('.js-modal-mark').style.display = 'block';
        cardBody.querySelector('.js-modal-mark span').innerHTML = `${item.dataset.mark}`;
    }
    else {
        cardBody.querySelector('.js-modal-mark').style.display = 'none';
    }

    //Icon 
    if(item.dataset.icon !== ''){
        cardHeader.querySelector('.js-modal-image').src = `${item.dataset.icon}`;
    }
    else {
        cardHeader.querySelector('.js-modal-image').style.display = 'none';
    }    
}


function modalTrigger() {
    //Set up the micro modal for the cards, as well as the update for information on the modal
    var allCards = document.querySelectorAll('.ul-cert-card');
    
    //Get all cards and set micromodal events to show card information
    MicroModal.init({
        onShow: modal => console.info(`${modal.id} is shown`), // [1]
        onClose: modal => console.info(`${modal.id} is hidden`), // [2]
        openTrigger: 'data-custom-open', // [3]
        closeTrigger: 'data-custom-close', // [4]
        openClass: 'is-open', // [5]
        disableScroll: true, // [6]
        disableFocus: false, // [7]
        awaitOpenAnimation: true, // [8]
        awaitCloseAnimation: true, // [9]
        debugMode: true // [10]
    });
    
    allCards.forEach(item => {
        item.addEventListener('click', function() {
            
            //onclick of the card, update the modal to have the correct info
            var cardHeader = document.querySelector('#modal-1 .modal__header'),
            cardBody = document.querySelector('#modal-1 .modal__content'),
            prev_button = document.querySelector('#modal-1 .modal__footer .modal__button__left'),
            next_button = document.querySelector('#modal-1 .modal__footer .modal__button__right'),
            prev_index = parseInt(item.dataset.index) - 1,
            next_index = parseInt(item.dataset.index) + 1;
            
            modalUpdateContent(cardHeader, cardBody, item); //check data and update modal
            
            
            prev_button.addEventListener('click', function() {
                //move to previous card if available
                if(prev_index >= 1){
                    // var prev_index = item.dataset.index - 1;
                    var prev_item = document.querySelector(`.ul-cert-card[data-index='${prev_index}']`);

                    if(prev_item !== null){

                        modalUpdateContent(cardHeader, cardBody, prev_item);
    
                        //keep subtracting from prev_index everytime its clicked on the same modal
                        prev_index--;
                        next_index--;
                    }
                }
                
            });

            next_button.addEventListener('click', function() {
                //move to previous card if available
                var next_item = document.querySelector(`.ul-cert-card[data-index='${next_index}']`);

                if(next_item !== null){

                    modalUpdateContent(cardHeader, cardBody, next_item);

                    //keep subtracting from prev_index everytime its clicked on the same modal
                    next_index++;
                    prev_index++;
                }
            });
            
        });
    });
}

function initializeParams() {
    //When start over is clicked pass the variables through the url and update the step one page to what was selected before
    var selectedCountries = document.querySelectorAll('.js-selected-countries .ul-glossary-button'),
        selectedRegulations = document.querySelectorAll('.js-selected-regulations .ul-glossary-button'),
        countryParams = [],
        regulationParams = [];

    for(let i=0; i< selectedCountries.length; i++){
        countryParams[i] = selectedCountries[i].innerHTML;

    }
    for(let i=0; i< selectedRegulations.length; i++){
        regulationParams[i] = selectedRegulations[i].innerHTML;
    }

    //Pass the parameters of Regulation Types and Countries to the URL
    let newURL = new URL(window.location.href);

    //Strip the url before appending new values
    newURL.searchParams.delete('reg_types');
    newURL.searchParams.delete('countries');

    if(regulationParams.length >= 1){
        newURL.searchParams.append('reg_types',regulationParams);
    }

    if(countryParams.length >= 1){
        newURL.searchParams.append('countries',countryParams);
    }
    
    // Copy the url to clipboard
    var clipURL = newURL.href;

    const elem = document.createElement('textarea');
    elem.value = clipURL;
    document.body.appendChild(elem);
    elem.select();
    document.execCommand('copy');
    document.body.removeChild(elem);

    // Force URL to new
    window.location.href = newURL;
}

//Set event listeners to the im not sure checkboxes to set redirect to contact us page
function checkEvents() {
    //Set up onlclick for the checkbox to reveal button
    var stepCheckOne = document.querySelector('#not-sure-one');
    var stepCheckTwo = document.querySelector('#not-sure-two');
    var regulateChecks = document.querySelectorAll('.js-regulate-option');

    stepCheckOne.addEventListener('change', (event) => {
        //Trigger the overlay, AND disable the country filter (At least hide the display and dont pass over data when checked)
        var buttonOverlay = document.querySelector('.ul-step-continue-overlay');
        var filters = document.querySelector('.ul-glossary-filters');
        var country = jQuery('#select-country').selectize();
        var continueStep = document.querySelector('.ul-step-continue');

        if (event.currentTarget.checked) {

            if(continueStep.dataset.step == '1'){
                filters.style.display = 'none';

                country[0].selectize.clear();
            }
            //On change remove the layer above continue button
            if(!buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.add('off');
            }
            

        } else {

            //Remove overlay if not checked
            if(buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.remove('off');
            }

            //If unchecked make sure to reveal the correct step
            if(continueStep.dataset.step == '1'){
                filters.style.display = 'flex';
            }
        }
    });

    stepCheckTwo.addEventListener('change', (event) => {
        //Trigger the overlay, AND disable the country filter (At least hide the display and dont pass over data when checked)
        var buttonOverlay = document.querySelector('.ul-step-continue-overlay');
        var regulateChecks = document.querySelectorAll('.js-regulate-option');
        var checkRows = document.querySelector('.ul-step-regulate-filter');
        var optionFlag = false;

        for(let i=0; i< regulateChecks.length; i++){
                    
            if(regulateChecks[i].checked){
                optionFlag = true;
            }
        }

        // var filterDropdown = document.querySelector('#select-country').selectize;
        if (event.currentTarget.checked) {

            //On change remove the layer above continue button
            if(!buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.add('off');
            }

            //Deselect requirements if check two is clicked
            for(let i=0; i< regulateChecks.length; i++){  
                regulateChecks[i].checked = false;
            }
            checkRows.style.display = 'none';

        } else {

            //add overlay if not checked and if there are not selected items
            if(buttonOverlay.classList.contains('off') && optionFlag == false) {
                    buttonOverlay.classList.remove('off');
            }
            checkRows.style.display = 'flex';
        }
    });
    

    regulateChecks.forEach(item => {

        item.addEventListener('change', (event) => {
            //handle click
            var buttonOverlay = document.querySelector('.ul-step-continue-overlay'),
                optionFlag = false;
            
            if (event.currentTarget.checked) {

                if(!buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.add('off');
                }
            }
            else {
                //Check if at least one option is checked, if not disable continue again

                for(let i=0; i< regulateChecks.length; i++){
                    
                    if(regulateChecks[i].checked){
                        optionFlag = true;
                    }
                }

                if(optionFlag == false){
                    if(buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.remove('off');
                    }
                }
            }
        });
    });    
}

function stepProgression(json, not_sure_url, extra_data) {
    //Set up onclick for the continue button to move onto next step
    var continueStep = document.querySelector('.ul-step-continue');
    var backStep = document.querySelector('.ul-step-back');

    continueStep.addEventListener("click", function(){
        //Trigger Continue //Select regulatory type
        //max-width: 532px;
        var stepCount = document.querySelector('.ul-step-count'),
            stepTitle = document.querySelector('.ul-step-title'),
            stepCheck = document.querySelector('.ul-step-checkbox'),
            filters = document.querySelector('.ul-glossary-filters'),
            regulateFilter = document.querySelector('.ul-step-regulate-filter'), 
            stepSection = document.querySelector('.ul-step-container'),
            buttonOverlay = document.querySelector('.ul-step-continue-overlay'),
            countryDisplay = document.querySelector('.ul-glossary-list'),
            backButton = document.querySelector('.ul-step-back'),
            stepCheckOne = document.querySelector('.not-sure-one'),
            stepCheckTwo = document.querySelector('.not-sure-two'),
            contact_text = document.querySelector('.ul-glossary-help-container'),
            buttonsContainer = document.querySelector('.ul-step-buttons'),
            inputOne = stepCheckOne.querySelector('#not-sure-one'),
            inputTwo = stepCheckTwo.querySelector('#not-sure-two'),
            regulateChecks = document.querySelectorAll('.js-regulate-option');
        
        if(continueStep.dataset.step == '1'){
            //Set up the next step and save the settings from this current step.
            stepCount.innerHTML = `${extra_data.next_step}`;
            stepTitle.innerHTML = `${extra_data.next_title}`;
            filters.style.display = 'none';
            contact_text.style.display = 'none';
            buttonsContainer.style.marginTop = '44px';

            
            //set the step counter to next step
            continueStep.dataset.step = '2';

            //Reveal the regulations list 
            if(regulateFilter.classList.contains('not-active')){
                regulateFilter.classList.remove('not-active');
            }

            //Resize the section
            stepSection.style.maxWidth = '532px';

            //Reset button overlay so user selects options
            if(buttonOverlay.classList.contains('off')) {
                buttonOverlay.classList.remove('off');
            }

            //Hide the selected countries list 
            countryDisplay.style.display = 'none';

            //Reveal back button after first step
            if(backButton.classList.contains('disabled')){
                backButton.classList.remove('disabled');
            }

            //Check if the im not sure is clicked to remove the regulation list and disable all check boxes
            if(inputTwo.checked) {

                //filters.style.display = 'none'; // Keep this as display none while the im not sure is checked
                regulateFilter.style.display = 'none';
                //Deselect requirements if check two is clicked
                for(let i=0; i< regulateChecks.length; i++){  
                    regulateChecks[i].checked = false;
                }
                //Reset button overlay so user selects options
                if(!buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.add('off');
                }
            }
            else {
                //If any of the filters are selected then continue
                for(let i=0; i< regulateChecks.length; i++){  
                    regulateChecks[i].checked = false;
                }
            }

            //Hide the first checkbox and replace with second, IF either buttons are checked then send user to contact page
            stepCheckOne.style.setProperty('display', 'none', 'important');
            stepCheckTwo.style.setProperty('display', 'flex', 'important');

            //Keep view at the top of the screen when changing to step 2
            window.scrollTo({ top: 0, behavior: 'smooth' });

        }
        else if(continueStep.dataset.step == '2'){
            
            //If any of the of the not sure buttons are clicked redirect to the contact us page
            if(inputOne.checked || inputTwo.checked){
                window.location.href = `${not_sure_url}`; //relative to domain
            }
            else {
                
                //This step will be the final step to show the results of the choices
                var checkedBoxes = document.querySelectorAll('.js-regulate-option:checked'),
                selectedCountries = document.querySelectorAll('.js-selected-countries .item'),
                certReveal = document.querySelector('.js-hidden-cert'),
                stepsHide = document.querySelector('.js-steps-container'),
                updateCountries = document.querySelector('.js-new-countries'),
                updateRegulations = document.querySelector('.js-new-regulations'),
                regulations = [],
                countries = [];
                
                for(let i=0; i< checkedBoxes.length; i++){
                    regulations[i] = checkedBoxes[i].name;
                }
                
                for(let i=0; i < selectedCountries.length; i++){
                    countries[i] = selectedCountries[i].dataset.value;
                }
                
                //Reveal the Your Certifications section and display the filtered options
                // stepsHide.style.display = 'none';
                // certReveal.style.display = 'block';
                
                //call the filter change function and display the new changes
                filterChange(json,countries,regulations);
                
                //Call the modal trigger and update the modal card with proper information
                modalTrigger();

                //Update the new countries and filters with that was chosen
                for(let i=0; i< regulations.length; i++){
                    updateRegulations.innerHTML += `<button class="ul-glossary-button no-close">${regulations[i]}</button>`;
                }
                
                for(let i=0; i < countries.length; i++){
                    updateCountries.innerHTML += `<button class="ul-glossary-button no-close">${countries[i]}</button>`;
                    
                }

                //Update the url to have the regulatory types and countries on Refresh
                initializeParams();
            }
        }
    });

    backStep.addEventListener("click", function(){
        //Trigger Continue //Select regulatory type
        //max-width: 532px;
        var stepCount = document.querySelector('.ul-step-count'),
            stepTitle = document.querySelector('.ul-step-title'),
            stepCheck = document.querySelector('.ul-step-checkbox'),
            filters = document.querySelector('.ul-glossary-filters'), 
            regulateFilter = document.querySelector('.ul-step-regulate-filter'),
            stepSection = document.querySelector('.ul-step-container'),
            buttonOverlay = document.querySelector('.ul-step-continue-overlay'),
            countryDisplay = document.querySelector('.ul-glossary-list'),
            stepCheckOne = document.querySelector('.not-sure-one'),
            stepCheckTwo = document.querySelector('.not-sure-two'),
            contact_text = document.querySelector('.ul-glossary-help-container'),
            inputOne = stepCheckOne.querySelector('#not-sure-one'),
            inputTwo = stepCheckTwo.querySelector('#not-sure-two');

        
        if(continueStep.dataset.step == '2'){
            //Set up the next step and save the settings from this current step.
            stepCount.innerHTML = `${extra_data.prev_step}`;
            stepTitle.innerHTML = `${extra_data.prev_title}`;
            filters.style.display = 'flex';
            contact_text.style.display = 'block';

            //set the step counter to next step
            continueStep.dataset.step = '1';

            
            //Reveal the regulations list 
            if(!regulateFilter.classList.contains('not-active')){
                regulateFilter.classList.add('not-active');
            }
            
            //Resize the section
            stepSection.style.maxWidth = '720px';
            
            //Hide the selected countries list 
            countryDisplay.style.display = 'flex';
            
            //Reveal back button after first step
            if(!backStep.classList.contains('disabled')){
                backStep.classList.add('disabled');
            }
            var country = jQuery('#select-country').selectize();
            country[0].selectize.clear();


            //If the 1st Im not Sure checkbox is checked then keep overlay disabled 
            if(inputOne.checked) {

                filters.style.display = 'none'; // Keep this as display none while the im not sure is checked

                //Reset button overlay so user selects options
                if(!buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.add('off');
                }
            }

            //Hide the second checkbox and replace with first again, IF either buttons are checked then send user to contact page
            stepCheckOne.style.setProperty('display', 'flex', 'important');
            stepCheckTwo.style.setProperty('display', 'none', 'important');

            //Keep view at the top of the screen when changing to back to step 1
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
}

function shareClearFilters(json) {
    var clear_filters = document.querySelector('.js-clear-filters');
    var shareButton = document.querySelector('.js-slug-filters');
    clear_filters.addEventListener('click', function() {
        var country = jQuery('#select-country').selectize();
        country[0].selectize.clear();
    });

    shareButton.addEventListener("click", function(){
        //When share is clicked pass the variables through the url and update the step one page to what was selected before
        initializeParams();
    });
}

function stepsGetAllCountries(json) {
    //Fix all the ,country and ,regulations. check for any region_country values with an array first
    for(let i=0; i<json.length; i++){

        //Loop through the json object
        if(Array.isArray(json[i].region_country)){

            
            if(json[i].region_country.length >= 2){ 
                
                for(var x = 1; x < json[i].region_country.length; x++){
                    var newCountry = [],
                        newRequirement = [];
                    newCountry[0] = json[i].region_country[x];
                    // newRequirement[0] = json[i].requirements;
                    
                    for(let j=0; j < json[i].requirements.length; j++){
                        newRequirement[j] = json[i].requirements[j];
                    }
                    
                    
                    if(newCountry[0] !== undefined){
                        json.push({
                            "profile_mark": json[i].profile_mark,
                            "name_of_mark": json[i].name_of_mark,
                            "region_country": newCountry,
                            "requirements": newRequirement,
                            "validity_period": json[i].validity_period,
                            "mark_required": json[i].mark_required
                        });
                    }
                }
                
            }
            
            
        }
    }
}

export function startCertificationSteps(not_sure_url, extra_data) {

    //Retrieve Json object to loop  through the data
    jQuery.getJSON("/global-market-access-portal/global-market-access-profiles.json", function(json) {

        //Before sorting make sure all countries are split to display all the cards for each region/country
        stepsGetAllCountries(json);
        
        //Sort the Data By Country then by requirement tags
        json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));

        //Reset the rowContainer to have the new JSON data
        var filterCountry = document.querySelector('#select-country'),
            firstCountry = '', //keep track of current country
            rowUpdate = '', //update the html of the rows
            countryFilter = '', //keep track of current country for filter
            regulateFilter = ''; //keep track of current regulation for filter

        //Fix all the ,country and ,regulations. check for any region_country values with an array first
        for(let i=0; i<json.length; i++){
            //Set the filter lists for Country and Regulation
            if(countryFilter !== json[i].region_country[0]){ //prevent duplicates
                countryFilter = json[i].region_country[0];

                if(countryFilter !== undefined){
                    filterCountry.innerHTML += `<option value="${countryFilter}">${countryFilter}</option>`;
                }
            }         

            // if(Array.isArray(json[i].region_country)){

            //     var newCountry = [],
            //         newRequirement = [];
            //     newCountry[0] = json[i].region_country[1];
            //     // newRequirement[0] = json[i].requirements;

            //     for(let j=0; j < json[i].requirements.length; j++){
            //         newRequirement[j] = json[i].requirements[j];
            //     }

                
            //     if(newCountry[0] !== undefined){
            //         json.push({
            //             "profile_mark": json[i].profile_mark,
            //             "name_of_mark": json[i].name_of_mark,
            //             "region_country": newCountry,
            //             "requirements": newRequirement,
            //             "validity_period": json[i].validity_period,
            //             "mark_required": json[i].mark_required
            //         });
            //     }

            // }
        }

        //Sort the json object with the new countries and regulations split
        json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));

        //Set Plugin function to stop select from opening on removal.
        Selectize.define('silent_remove', function(options){
            var self = this;

            // defang the internal search method when remove has been clicked
            this.on('item_remove', function(){
                this.plugin_silent_remove_in_remove = true;
            });

            this.search = (function() {
                var original = self.search;
                return function() {
                    if (typeof(this.plugin_silent_remove_in_remove) != "undefined") {
                        // re-enable normal searching
                        delete this.plugin_silent_remove_in_remove;
                        return {
                                items: {},
                                query: [],
                                tokens: []
                            };
                    }
                    else {
                        return original.apply(this, arguments);
                    }
                };
            })();
        });

        //Set the country filter
        var initFilter = false; //flag for ondropdown open onclick event
        jQuery('#select-country').selectize({
            sortField: 'text',
            hideSelected: false,
            plugins: ['silent_remove'],
            onChange: function () {
                // On Change update the filter list for countries
                var country_filter = document.querySelector('.js-selected-countries'),
                    selected_items = document.querySelectorAll('.js-country-filter .selectize-input.items .item'),
                    filtered_items = document.querySelectorAll('.js-selected-countries .item'),
                    placeholder = document.querySelector('#select-country-selectized'),
                    select = jQuery('#select-country').selectize();

                if(selected_items.length >= 1){

                    country_filter.innerHTML = "";
                    for(let i=0; i < selected_items.length; i++){
                        country_filter.innerHTML += `${selected_items[i].outerHTML}`;
                    }

                    filtered_items = document.querySelectorAll('.js-selected-countries .item');
                }
                else {
                    country_filter.innerHTML = "<button class='ul-glossary-button no-close'>All Markets</button>";
                }

                // Set the onclicks to remove the selected options from the dropdown
                if(filtered_items.length >= 1){

                    filtered_items.forEach(item => {
                        item.addEventListener('click', function() {
                            // 1. Get the value
                            var selectedValue = item.dataset.value;
                            // 2. Remove the selected option 
                            select[0].selectize.removeItem(selectedValue);

                            // select[0].selectize.refreshItems();
                            // select[0].selectize.refreshOptions();
                        });
                    });
                }
                

                //On change remove the layer above continue button
                var buttonOverlay = document.querySelector('.ul-step-continue-overlay');

                var activeItems = document.querySelectorAll('.js-selected-countries .item');

                if(activeItems.length >= 1 && activeItems !== undefined){

                    if(!buttonOverlay.classList.contains('off')) {
                        buttonOverlay.classList.add('off');
                    }
                }

                //Reset the placeholder text since selectize removes in on update
                placeholder.setAttribute('placeholder',`Filter Markets:`);

            },
            onItemRemove(value) {
                //If All Items are removed reset the overlay
                var buttonOverlay = document.querySelector('.ul-step-continue-overlay'),
                    activeItems = document.querySelectorAll('.js-selected-countries .item');

                if(activeItems.length < 1 || activeItems == undefined){

                    if(buttonOverlay.classList.contains('off')) {
                        buttonOverlay.classList.remove('off');
                    }
                }

                //Remove selected when clicked 
                var all_items = document.querySelectorAll(`.js-country-filter .selectize-dropdown-content .option`);

                for(let i=0; i < all_items.length; i++){

                    if(all_items[i].dataset.value == value){
                        if(all_items[i].classList.contains('selected')) {
                            all_items[i].classList.remove('selected');
                        }
                    }
                }

            },
            onClear() {

                var all_items = document.querySelectorAll(".js-country-filter .selectize-dropdown-content .option");

                for(let i=0; i < all_items.length; i++){
                    if(all_items[i].classList.contains('selected')) {
                        all_items[i].classList.remove('selected');
                    }
                }

                //On change remove the layer above continue button
                var buttonOverlay = document.querySelector('.ul-step-continue-overlay');

                if(buttonOverlay.classList.contains('off')) {
                    buttonOverlay.classList.remove('off');
                }
            },
            onDropdownClose: function($dropdown){
                // remove the selected classes from the 
                // $($dropdown).find('.selected').not('.active').removeClass('selected'); 
            },
            onDropdownOpen($dropdown) {

                if(initFilter == false){

                    initFilter = true;//Only set this once using flag
                    //On load set a click event to the options to remove them if they are checked
                    var selectizeFilter = document.querySelector(`.js-country-filter .selectize-dropdown-content`);
                    var filterOptions = selectizeFilter.querySelectorAll('.option');

                    // filterOptions.forEach(item => {
                    //     item.addEventListener('click', function() {
                            
                    //         //onclick of the card, update the modal to have the correct info
                    //         var select = $(`#select-country`).selectize();

                    //         item.classList.remove('selected');

                    //         //Remove the selected option 
                    //         select[0].selectize.removeItem(item.dataset.value);

                    //         select[0].selectize.refreshItems();
                    //         select[0].selectize.refreshOptions();
                    //     });
                    // });
                }

            }
        });

        //clear the country filters and set up share button
        shareClearFilters(json);

        //Set up onlclick for the im not sure checkboxes to reveal button
        checkEvents();

        //Set up onclick for the continue button to move onto next step and back to move back a step
        stepProgression(json, not_sure_url, extra_data);
        
    });
}


export function loadStepThree(countryList, regulationList) {
    jQuery.getJSON("/global-market-access-portal/global-market-access-profiles.json", function(json) {


        //Reset the JSON object to have all the appended countries
        stepsGetAllCountries(json);
        //Make sure the json object is sorted
        json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));

        //Run this function when there are country and regulation parameters in the url
        var checkedBoxes = document.querySelectorAll('.js-regulate-option:checked'),
        selectedCountries = document.querySelectorAll('.js-selected-countries .item'),
        certReveal = document.querySelector('.js-hidden-cert'),
        stepsHide = document.querySelector('.js-steps-container'),
        updateCountries = document.querySelector('.js-new-countries'),
        updateRegulations = document.querySelector('.js-new-regulations'),
        regulations = regulationList.split(","),
        countries = countryList.split(","),
        bottomBar = document.querySelector('.ul-bottom-cert-bar'),
        shareButton = document.querySelector('.js-slug-filters');

        //Reveal the Your Certifications section and display the filtered options
        stepsHide.style.display = 'none';
        certReveal.style.display = 'block';
        bottomBar.classList.add('glossary-bar'); //set bottom bar to fixed


        //Reset the JSON array object to get the other countries in arrays. 
        //JSON array is losing the values when reloading the page.

        //call the filter change function and display the new changes
        filterChange(json,countries,regulations);
        
        //Intialize the modal and set the onclicks of the cards to update the content on modal
        modalTrigger();
        
        //Update the new countries and filters with that was chosen
        for(let i=0; i< regulations.length; i++){
            updateRegulations.innerHTML += `<button class="ul-glossary-button no-close">${regulations[i]}</button>`;
        }
        
        for(let i=0; i < countries.length; i++){
            updateCountries.innerHTML += `<button class="ul-glossary-button no-close">${countries[i]}</button>`;
            
        }

        //Set Screen to the top during the load
        window.scrollTo({ top: 0, behavior: 'smooth' });

        shareButton.addEventListener("click", function(){
            //Set Page URL to clipboard and display visual feedback
            let newURL = new URL(window.location.href);

            // Copy the url to clipboard
            var clipURL = newURL.href,
            clipAlert = document.querySelector('.clipboard-alert');

            const elem = document.createElement('textarea');
            elem.value = clipURL;
            document.body.appendChild(elem);
            elem.select();
            document.execCommand('copy');
            document.body.removeChild(elem);

            //Display visual for copied to clipboard!
            clipAlert.style.opacity = 1;

            //Add Copied State Class when clicked and update button state
            if(!shareButton.classList.contains('copied-state')) {
                var button_text = shareButton.querySelector('.ul-share-text');

                button_text.innerHTML = 'Copied';
                shareButton.classList.add('copied-state');
            }

            setTimeout(() => {
                clipAlert.style.opacity = 0;
            }, "2000");
        });


        // Contact us button link to url
        //Set the url to contact page with any countries or reg types
        var contactButtons = document.querySelectorAll('.js-contact-page');
        contactButtons.forEach(item => {
            item.addEventListener('click', event => {
                //When contact us is clicked pass the variables through the url and update the step one page to what was selected before
                var selectedCountries = document.querySelectorAll('.js-selected-countries .ul-glossary-button'),
                    selectedRegulations = document.querySelectorAll('.js-selected-regulations .ul-glossary-button'),
                    countryParams = [],
                    regulationParams = [];

                //Set the arrays of data to pass into the url for regulations and countries
                for(let i=0; i< selectedCountries.length; i++){
                    countryParams[i] = selectedCountries[i].innerHTML;

                }
                for(let i=0; i< selectedRegulations.length; i++){
                    regulationParams[i] = selectedRegulations[i].innerHTML;

                }
                
                //Pass the parameters of Regulation Types and Countries to the URL
                var tempURL = window.location.origin + item.dataset.contact;
                let newURL = new URL(tempURL);

                if(regulationParams.length >= 1){
                    newURL.searchParams.append('reg_types',regulationParams);
                }
            
                if(countryParams.length >= 1){
                    newURL.searchParams.append('countries',countryParams);
                }

                // Force URL to new
                window.location.href = newURL;
            });
        });
    });
}