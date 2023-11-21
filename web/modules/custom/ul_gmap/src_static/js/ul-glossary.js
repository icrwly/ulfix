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
                        //Do not add objects with no regulation types
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

    //set no results if string comes back empty
    if(rowUpdate == '') {
        rowUpdate = `
            <div class='ul-no-result-container'>
                <h2 class='ul-no-result-title fs-6'>No results</h2>
                <p class='ul-no-result-clear fs-2 js-no-result-clear'>Refine your search or <a class='ul-glossary-filters-clear'>clear all filters</a></p>
            </div>
        `;

        rowContainer.innerHTML += rowUpdate;

        //Add the clear functionality to the this new clear button
        var noResultClear = document.querySelector('.js-no-result-clear');
        noResultClear.addEventListener('click', function() {
            var country = jQuery('#select-country').selectize(),
                regulation = jQuery('#select-regulation').selectize();

            country[0].selectize.clear();
            regulation[0].selectize.clear();
        });
    }
    else {
        rowContainer.innerHTML += rowUpdate;
    }
    
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
    //When share is clicked pass the variables through the url and update the step one page to what was selected before
    var selectedCountries = document.querySelectorAll('.js-selected-countries .item'),
        selectedRegulations = document.querySelectorAll('.js-selected-regulations .item'),
        clipAlert = document.querySelector('.clipboard-alert'),
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

    //Display visual for copied to clipboard!
    clipAlert.style.opacity = 1;

    setTimeout(() => {
        clipAlert.style.opacity = 0;
    }, "2000")

    // Force URL to new
    // window.location.href = newURL;
}

function initializeGlossaryDisplay(json, req_array){

    var newJSON = [];
    //Reset the rowContainer to have the new JSON data
    var rowContainer = document.querySelector('.ul-cert-rows-container'),
        filterCountry = document.querySelector('#select-country'),
        filterRegulate = document.querySelector('#select-regulation'),
        card_index = 1,
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

        //Set regulation filter one the first loop through using the taxonomy array passed into the function
        if(i == 0){
            for(let x = 0; x < req_array.length; x++){
                filterRegulate.innerHTML += `<option value="${req_array[x]}">${req_array[x]}</option>`;
            }
        }

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

    //Sort the json object with the new countries and regulations split
    json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));

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

                    //Do not add objects with no regulation types
                    if(json[j].requirements[0] !== undefined){
                        count++;
                        currentCountries.push(json[j]);
                    }
                }
            }
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
                rowUpdate += `
                    <div class='ul-cert-card' data-custom-open="modal-1" data-index="${card_index}" data-title="${currentCountries[j].name_of_mark}" data-country="${currentCountries[j].region_country[0]}" data-require="${currentCountries[j].requirements}" data-validperiod="${currentCountries[j].validity_period}" data-mark="${currentCountries[j].mark_required}" data-icon="${currentCountries[j].profile_mark}">
                    <div class='ul-cert-card-type'><p class='ul-cert-card-type-text fs--2'>${currentCountries[j].requirements.join(', ')}</p></div>
                    <div class='ul-cert-card-icon-wrapper'>
                    <img class='ul-cert-card-icon' src='${currentCountries[j].profile_mark}' alt='Card Icon'>
                    </div>
                    <p class='ul-cert-card-text fs--1'>${currentCountries[j].name_of_mark}</p>
                    </div>
                `; 
                card_index++; //advance index all cards
                // https://picsum.photos/100/100
                // /../src/assets/cert_images/${currentCountries[j].name_of_mark}.png
            }
            rowUpdate += `
                    </div>
                </div>
                `;  
        }
        else {

        }

    }
    //Update the Container with all the countries and regulatory types
    rowContainer.innerHTML += rowUpdate;
}


function glossaryGetAllCountries(json) {
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

function setFilters(json,filterName,filterPlural){
        //Set the country filter
        var initFilter = false; //flag for ondropdown open onclick event
        jQuery(`#select-${filterName}`).selectize({
            sortField: 'text',
            hideSelected: false,
            plugins: ['silent_remove'],
            onChange: function () {

                //Using temp name in spots where regulation needs to be regulate
                if(filterName == 'country'){
                    var tempName = 'country',
                        placeholder = document.querySelector('#select-country-selectized'),
                        upperName = 'Markets'; //filterPlural.charAt(0).toUpperCase() + filterPlural.slice(1);
                }
                else if(filterName == 'regulation'){
                    var tempName = 'regulate',
                        placeholder = document.querySelector('#select-regulation-selectized'),
                        upperName = 'Requirement Types';
                }

                // On Change update the filter list for countries
                var filter = document.querySelector(`.js-selected-${filterPlural}`),
                    selected_items = document.querySelectorAll(`.js-${tempName}-filter .selectize-input.items .item`),
                    filtered_items = document.querySelectorAll(`.js-selected-${filterPlural} .item`),
                    select = jQuery(`#select-${filterName}`).selectize();

                if(selected_items.length >= 1){

                    filter.innerHTML = "";
                    for(let i=0; i < selected_items.length; i++){
                        filter.innerHTML += `${selected_items[i].outerHTML}`;
                    }

                    filtered_items = document.querySelectorAll(`.js-selected-${filterPlural} .item`);
                }
                else {
                    //Set Default text when nothing is selected for both filters
                    if(filterName == 'regulation'){
                        var defaultText = 'All Types';
                    }
                    else if(filterName == 'country'){
                        var defaultText = 'All Countries';
                    }
                    filter.innerHTML = `<button class='ul-glossary-button no-close'>${defaultText}</button>`;
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

                //Update the cards on the selected value
                var selectedCountries = document.querySelectorAll('#select-country option'),
                    selectedRegs = document.querySelectorAll('#select-regulation option'),
                    countryArray = [],
                    regArray = [];

                for(let i=0; i < selectedCountries.length; i++){
                    countryArray[i] = selectedCountries[i].value;
                }
                for(let i=0; i < selectedRegs.length; i++){
                    regArray[i] = selectedRegs[i].value;
                }
                
                filterChange(json,countryArray,regArray);

                //Update the modal with the current cards filtered
                modalTrigger();


                //Reset the placeholder text since selectize removes in on update
                placeholder.setAttribute('placeholder',`Filter ${upperName}:`);
                // .setAttribute('placeholder','$');

                // Reset the share button if its been copied
                var share_button = document.querySelector('.js-glossary-share');
                //Add Copied State Class when clicked and update button state
                if(share_button.classList.contains('copied-state')) {
                    var button_text = share_button.querySelector('.ul-share-text');

                    button_text.innerHTML = 'Share';
                    share_button.classList.remove('copied-state');
                }
                
            },
            onItemRemove(value) {
                //Remove the selected check mark from dropdowns using selected value
                if(filterName == 'country'){
                    var all_items = document.querySelectorAll(`.js-${filterName}-filter .selectize-dropdown-content .option`);
                }
                else if(filterName == 'regulation'){
                    var tempName = 'regulate';
                    var all_items = document.querySelectorAll(`.js-${tempName}-filter .selectize-dropdown-content .option`);
                }

                for(let i=0; i < all_items.length; i++){

                    if(all_items[i].dataset.value == value){
                        if(all_items[i].classList.contains('selected')) {
                            all_items[i].classList.remove('selected');
                        }
                    }
                }
            },
            onClear() {
                //The filter name selection changes depending on the type, needs to use tempname for regulation
                if(filterName == 'country'){
                    var all_items = document.querySelectorAll(`.js-${filterName}-filter .selectize-dropdown-content .option`);
                }
                else if(filterName == 'regulation'){
                    var tempName = 'regulate';
                    var all_items = document.querySelectorAll(`.js-${tempName}-filter .selectize-dropdown-content .option`);
                }

                for(let i=0; i < all_items.length; i++){
                    if(all_items[i].classList.contains('selected')) {
                        all_items[i].classList.remove('selected');
                    }
                }


            },
            onDropdownClose: function($dropdown){
                // remove the checkmark when item is removed
                // $($dropdown).find('.selected').not('.active').removeClass('selected'); 
            },
            onDropdownOpen($dropdown) {

                if(initFilter == false){

                    initFilter = true;
                    //get proper class name for filter selctor
                    if(filterName == 'country'){
                        var tempName = 'country';
                    }
                    else if(filterName == 'regulation'){
                        var tempName = 'regulate';
                    }
                    //On load set a click event to the options to remove them if they are checked
                    var selectizeFilter = document.querySelector(`.js-${tempName}-filter .selectize-dropdown-content`);
                    var filterOptions = selectizeFilter.querySelectorAll('.option');

                    // filterOptions.forEach(item => {
                    //     item.addEventListener('click', function() {
                            
                    //         //onclick of the card, update the modal to have the correct info
                    //         var select = jQuery(`#select-${filterName}`).selectize();

                    //         item.classList.remove('selected');

                    //         //Remove the selected option 
                    //         select[0].selectize.removeItem(item.dataset.value);

                    //         // select[0].selectize.refreshItems();
                    //         // select[0].selectize.refreshOptions();
                    //     });
                    // });
                }

            },
            onInitialize() {
                const urlParams = new URLSearchParams(window.location.search);

                // Get the regulatory type and countries from url
                var select = jQuery(`#select-${filterName}`).selectize();
                if(filterName == 'country'){
                    var param = 'countries';
                }
                else if(filterName == 'regulation'){
                    var param = 'reg_types';
                }
                var filter_params = urlParams.get(`${param}`);
                
                //If the url parameters are null load step 1
                if(filter_params !== null){
                    var filter_array = filter_params.split(",");
                    //Loop through all the countries in the url and update the filter
                    for(let i=0; i<filter_params.length; i++){

                        select[0].selectize.addItem(filter_array[i]);
                    }

                    
                }
            }
        });
}

function shareClearFilters(json) {
    var clear_filters = document.querySelector('.js-clear-filters');
    var share_button = document.querySelector('.js-glossary-share');

    clear_filters.addEventListener('click', function() {
        var country = jQuery('#select-country').selectize(),
            regulation = jQuery('#select-regulation').selectize();

        country[0].selectize.clear();
        regulation[0].selectize.clear();
    }); 

    share_button.addEventListener('click', function() {

        //Add Copied State Class when clicked and update button state
        if(!share_button.classList.contains('copied-state')) {
            var button_text = share_button.querySelector('.ul-share-text');

            button_text.innerHTML = 'Copied';
            share_button.classList.add('copied-state');
        }

        initializeParams();
    });
}

//Retrieve Json object to loop  through the data
export function startGlossary(req_names) {
    //Retrieve Json object to loop  through the data
    jQuery.getJSON("/global-market-access-portal/global-market-access-profiles.json", function(json) {

        //Sort the Data By Country then by requirement tags
        json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));

        //Set up filters and display all the cards
        var req_array = req_names.split(",");
        initializeGlossaryDisplay(json, req_array);

        //Set the modals for the set cards
        modalTrigger();

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

        //Set the filters for both country and regulations
        setFilters(json, 'country', 'countries');
        setFilters(json, 'regulation', 'regulations');

        //Clear filters when clicked, and set onclick functionality for share button
        shareClearFilters(json);


        //Set the url to contact page with any countries or reg types
        var contactButtons = document.querySelectorAll('.js-contact-page');
        contactButtons.forEach(item => {
            item.addEventListener('click', event => {
                //When contact us is clicked pass the variables through the url and update the step one page to what was selected before
                var selectedCountries = document.querySelectorAll('.js-selected-countries .item'),
                    selectedRegulations = document.querySelectorAll('.js-selected-regulations .item'),
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


export function saveGlossaryFilters(countryList, regulationList) {
    //On Refresh and on share save the selected items to the url and update page with url
    jQuery.getJSON("/global-market-access-portal/global-market-access-profiles.json", function(json) {
        //Reset the json array to have all the correct countries
        glossaryGetAllCountries(json);
        //Make sure the json object is sorted
        json.sort((a,b)=> (a.region_country[0].localeCompare(b.region_country[0]) || a.requirements - b.requirements));

        //Run this function when there are country and regulation parameters in the url
        //Check if the parameters for country and regulations contain anything, if not set as empty array
        if(countryList !== null){
            var countries = countryList.split(",");
        }
        else {
            var countries = [];
        }

        if(regulationList !== null){
            var regulations = regulationList.split(",");
        }
        else {
            var regulations = [];
        }

        //call the filter change function and display the new changes
        filterChange(json,countries,regulations);
        
        //Intialize the modal and set the onclicks of the cards to update the content on modal
        modalTrigger();
        
    });

}