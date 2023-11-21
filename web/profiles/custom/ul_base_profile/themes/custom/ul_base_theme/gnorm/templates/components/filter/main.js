'use strict';

var $ = require('jquery');

module.exports = class ComponentsFilter{
  constructor(el){
    this.$el = $(el);
    this.$componentsContainer = this.$el.siblings('.components');
    this.$component = this.$componentsContainer.find('.components__item');
    this.$componentFilters = this.$el.find('.components-filter__category');
    this.$sortClearButton = this.$el.find('[data-filter-category="clear"]');
    this.$sortTypeButton = this.$el.find('[data-filter-category="type"]');
    this.$sortAscendingButton = this.$el.find('.ascending');
    this.$sortDescendingButton = this.$el.find('.descending');
    this.activeFilterClass = 'active-filter';
    this.hideClass = 'js-hide-component';
    this.heightClass = 'no-height';
    this.animationTime = 200;

    // load the components and filter JSON in through the same object
    var componentArray = <%= filterOutput %>;

    // clear all filters
    this.$sortClearButton.on('click', (e)=>{
      this.clearSort();
    });

    // type sort on change
    this.$sortTypeButton.on('change', (e)=>{
      this.$componentFilters.removeClass(this.activeFilterClass);
      $(e.currentTarget).addClass(this.activeFilterClass);
      this.onSelectChange($(e.currentTarget));
    });

    this.$sortAscendingButton.on('click', (e)=>{
      this.buttonActiveControl(e, this.ascendingSort);
    });
    this.$sortDescendingButton.on('click', (e)=>{
      this.buttonActiveControl(e, this.descendingSort);
    });
  }
  // sort the components from A - Z based on the title
  ascendingSort(thisModule){
    let ascendingDivs = thisModule.$component.sort(function(a,b){
      return $(a).find('h3').text() > $(b).find('h3').text();
    });
    thisModule.linearSort(ascendingDivs);
  }
  buttonActiveControl(e, filterSort){
    if(!$(e.currentTarget).hasClass(this.activeFilterClass)) {
      this.$componentFilters.removeClass(this.activeFilterClass);
      $(e.currentTarget).addClass(this.activeFilterClass);
      filterSort(this);
    }
  }
  // sort the components from Z - A based on the title
  descendingSort(thisModule){
    let descendingDivs = thisModule.$component.sort(function(a,b){
      return $(a).find('h3').text() < $(b).find('h3').text();
    });
    thisModule.linearSort(descendingDivs);
  }
  // clear any sort filters to return the components list to its original state
  clearSort(){
    // reorder to original order
    let divs = this.$component.sort(function(a,b){
      return $(a).data('filter') > $(b).data('filter');
    });
    this.$componentFilters.removeClass(this.activeFilterClass);
    this.linearSort(divs);
    this.$sortTypeButton.find(':selected').prop('selected', false);
    this.$sortTypeButton.find('option[value="all"]').prop('selected', true);
  }
  linearSort(object){
    this.$component.addClass(this.hideClass);
    this.$component.removeClass(this.heightClass);
    window.setTimeout(() => {
      this.$componentsContainer.html(object);
      this.$component.removeClass(this.hideClass);
    }, this.animationTime);
  }
  // select sort
  // @param $element  the changed select box
  onSelectChange($element){
    let selectedOption = $element.find(':selected').val();
    this.$component.addClass(this.hideClass);
    if(selectedOption === 'all'){
      this.$component.removeClass(this.hideClass);
      this.$component.removeClass(this.heightClass);
    } else {
      this.$component.addClass(this.hideClass);
      this.$component.addClass(this.heightClass);
      this.$componentsContainer.find('[data-type="' + selectedOption + '"]').removeClass(this.hideClass).removeClass(this.heightClass);
    }
  }
};
