'use strict';

import $ from 'jquery'

export default class ComponentsFilter{
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
    var componentArray = [{"index":0,"componentType":"1_core","fileName":"1-column.twig","filePath":"1_core"},{"index":1,"componentType":"1_core","fileName":"2-column.twig","filePath":"1_core"},{"index":2,"componentType":"1_core","fileName":"components.twig","filePath":"1_core"},{"index":3,"componentType":"1_core","fileName":"global-scripts.twig","filePath":"1_core"},{"index":4,"componentType":"1_core","fileName":"grid.twig","filePath":"1_core"},{"index":5,"componentType":"1_core","fileName":"head.twig","filePath":"1_core"},{"index":6,"componentType":"1_core","fileName":"layout.twig","filePath":"1_core"},{"index":7,"componentType":"1_core","fileName":"page.twig","filePath":"1_core"},{"index":8,"componentType":"1_core","fileName":"style-guide.twig","filePath":"1_core"},{"index":9,"componentType":"1_core","fileName":"wysiwyg-sample--short.twig","filePath":"1_core"},{"index":10,"componentType":"1_core","fileName":"wysiwyg-sample.twig","filePath":"1_core"},{"index":11,"componentType":"2_pieces","fileName":"block-date.twig","filePath":"2_pieces"},{"index":12,"componentType":"2_pieces","fileName":"button.twig","filePath":"2_pieces"},{"index":13,"componentType":"2_pieces","fileName":"card--aside-default.twig","filePath":"2_pieces/card"},{"index":14,"componentType":"2_pieces","fileName":"card--aside-download.twig","filePath":"2_pieces/card"},{"index":15,"componentType":"2_pieces","fileName":"card--default.twig","filePath":"2_pieces/card"},{"index":16,"componentType":"2_pieces","fileName":"card--events-highlight.twig","filePath":"2_pieces/card"},{"index":17,"componentType":"2_pieces","fileName":"card--events.twig","filePath":"2_pieces/card"},{"index":18,"componentType":"2_pieces","fileName":"card--banner.twig","filePath":"2_pieces/card"},{"index":19,"componentType":"2_pieces","fileName":"card.twig","filePath":"2_pieces/card"},{"index":20,"componentType":"2_pieces","fileName":"filter-bar--filter.twig","filePath":"2_pieces/filter-bar"},{"index":21,"componentType":"2_pieces","fileName":"filter-bar--pagination.twig","filePath":"2_pieces/filter-bar"},{"index":22,"componentType":"2_pieces","fileName":"filter-bar--results.twig","filePath":"2_pieces/filter-bar"},{"index":23,"componentType":"2_pieces","fileName":"filter-bar--search.twig","filePath":"2_pieces/filter-bar"},{"index":24,"componentType":"2_pieces","fileName":"filter-bar--select.twig","filePath":"2_pieces/filter-bar"},{"index":25,"componentType":"2_pieces","fileName":"footer--copyright.twig","filePath":"2_pieces"},{"index":26,"componentType":"2_pieces","fileName":"footer--logo.twig","filePath":"2_pieces"},{"index":27,"componentType":"2_pieces","fileName":"footer-choose-region.twig","filePath":"2_pieces"},{"index":28,"componentType":"2_pieces","fileName":"form-item--select.twig","filePath":"2_pieces"},{"index":29,"componentType":"2_pieces","fileName":"listing-item--default.twig","filePath":"2_pieces/listing-item"},{"index":30,"componentType":"2_pieces","fileName":"listing-item.twig","filePath":"2_pieces/listing-item"},{"index":31,"componentType":"2_pieces","fileName":"nav--primary__item.twig","filePath":"2_pieces"},{"index":32,"componentType":"2_pieces","fileName":"nav--primary__mega-menu.twig","filePath":"2_pieces"},{"index":33,"componentType":"2_pieces","fileName":"nav--secondary__item.twig","filePath":"2_pieces"},{"index":34,"componentType":"2_pieces","fileName":"nav--secondary__submenu.twig","filePath":"2_pieces"},{"index":35,"componentType":"2_pieces","fileName":"overlay.twig","filePath":"2_pieces"},{"index":36,"componentType":"2_pieces","fileName":"pagination.twig","filePath":"2_pieces"},{"index":37,"componentType":"2_pieces","fileName":"search-bar--group.twig","filePath":"2_pieces"},{"index":38,"componentType":"2_pieces","fileName":"section--references__citation.twig","filePath":"2_pieces"},{"index":39,"componentType":"2_pieces","fileName":"skip-to-content.twig","filePath":"2_pieces"},{"index":40,"componentType":"2_pieces","fileName":"ul-logo.twig","filePath":"2_pieces"},{"index":41,"componentType":"2_pieces","fileName":"widget--card-tags.twig","filePath":"2_pieces/widget"},{"index":42,"componentType":"2_pieces","fileName":"widget--card-tags__item.twig","filePath":"2_pieces/widget"},{"index":43,"componentType":"2_pieces","fileName":"widget--date-time.twig","filePath":"2_pieces/widget"},{"index":44,"componentType":"2_pieces","fileName":"widget--default.twig","filePath":"2_pieces/widget"},{"index":45,"componentType":"2_pieces","fileName":"widget--topics.twig","filePath":"2_pieces/widget"},{"index":46,"componentType":"2_pieces","fileName":"widget--topics__item.twig","filePath":"2_pieces/widget"},{"index":47,"componentType":"2_pieces","fileName":"widget.twig","filePath":"2_pieces/widget"},{"index":48,"componentType":"3_components","fileName":"aside--router.twig","filePath":"3_components"},{"index":49,"componentType":"3_components","fileName":"aside.twig","filePath":"3_components"},{"index":50,"componentType":"3_components","fileName":"card--router.twig","filePath":"3_components"},{"index":51,"componentType":"3_components","fileName":"footer--bottom.twig","filePath":"3_components"},{"index":52,"componentType":"3_components","fileName":"footer--top.twig","filePath":"3_components"},{"index":53,"componentType":"3_components","fileName":"footer-nav.twig","filePath":"3_components"},{"index":54,"componentType":"3_components","fileName":"global-header__button-wrapper.twig","filePath":"3_components"},{"index":55,"componentType":"3_components","fileName":"global-header__logo.twig","filePath":"3_components"},{"index":56,"componentType":"3_components","fileName":"global-header__search.twig","filePath":"3_components"},{"index":57,"componentType":"3_components","fileName":"hero--default-slide.twig","filePath":"3_components"},{"index":58,"componentType":"3_components","fileName":"hero--events__slide.twig","filePath":"3_components"},{"index":59,"componentType":"3_components","fileName":"hero__aside-item.twig","filePath":"3_components"},{"index":60,"componentType":"3_components","fileName":"listing-item--router.twig","filePath":"3_components"},{"index":61,"componentType":"3_components","fileName":"modal.twig","filePath":"3_components"},{"index":62,"componentType":"3_components","fileName":"nav--primary.twig","filePath":"3_components"},{"index":63,"componentType":"3_components","fileName":"nav--secondary.twig","filePath":"3_components"},{"index":64,"componentType":"3_components","fileName":"nav--side__menu.twig","filePath":"3_components"},{"index":65,"componentType":"3_components","fileName":"nav--side__submenu.twig","filePath":"3_components"},{"index":66,"componentType":"3_components","fileName":"slide--hero.twig","filePath":"3_components"},{"index":67,"componentType":"3_components","fileName":"slide.twig","filePath":"3_components"},{"index":68,"componentType":"3_components","fileName":"social-nav.twig","filePath":"3_components"},{"index":69,"componentType":"3_components","fileName":"title-bar--aside.twig","filePath":"3_components"},{"index":70,"componentType":"3_components","fileName":"title-bar--basic.twig","filePath":"3_components"},{"index":71,"componentType":"3_components","fileName":"title-bar--default.twig","filePath":"3_components"},{"index":72,"componentType":"3_components","fileName":"title-bar.twig","filePath":"3_components"},{"index":73,"componentType":"4_regions","fileName":"footer.twig","filePath":"4_regions"},{"index":74,"componentType":"4_regions","fileName":"header.twig","filePath":"4_regions"},{"index":75,"componentType":"4_regions","fileName":"hero--banner.twig","filePath":"4_regions"},{"index":76,"componentType":"4_regions","fileName":"hero--default.twig","filePath":"4_regions"},{"index":77,"componentType":"4_regions","fileName":"hero--events.twig","filePath":"4_regions"},{"index":78,"componentType":"4_regions","fileName":"hero--news.twig","filePath":"4_regions"},{"index":79,"componentType":"4_regions","fileName":"hero.twig","filePath":"4_regions"},{"index":80,"componentType":"4_regions","fileName":"nav--side.twig","filePath":"4_regions"},{"index":81,"componentType":"4_regions","fileName":"section--author.twig","filePath":"4_regions"},{"index":82,"componentType":"4_regions","fileName":"section--banner.twig","filePath":"4_regions"},{"index":83,"componentType":"4_regions","fileName":"section--cards--default.twig","filePath":"4_regions"},{"index":84,"componentType":"4_regions","fileName":"section--cards--events.twig","filePath":"4_regions"},{"index":85,"componentType":"4_regions","fileName":"section--cards.twig","filePath":"4_regions"},{"index":86,"componentType":"4_regions","fileName":"section--filter-bar.twig","filePath":"4_regions"},{"index":87,"componentType":"4_regions","fileName":"section--gate.twig","filePath":"4_regions"},{"index":88,"componentType":"4_regions","fileName":"section--listing-container.twig","filePath":"4_regions"},{"index":89,"componentType":"4_regions","fileName":"section--published.twig","filePath":"4_regions"},{"index":90,"componentType":"4_regions","fileName":"section--references.twig","filePath":"4_regions"},{"index":91,"componentType":"4_regions","fileName":"section--tags.twig","filePath":"4_regions"},{"index":92,"componentType":"4_regions","fileName":"section--title.twig","filePath":"4_regions"},{"index":93,"componentType":"4_regions","fileName":"section--topics.twig","filePath":"4_regions"},{"index":94,"componentType":"4_regions","fileName":"section--wysiwyg.twig","filePath":"4_regions"},{"index":95,"componentType":"4_regions","fileName":"section.twig","filePath":"4_regions"},{"index":96,"componentType":"5_layouts","fileName":"t03-basic-detail.twig","filePath":"5_layouts"},{"index":97,"componentType":"5_layouts","fileName":"t04-news-detail.twig","filePath":"5_layouts"},{"index":98,"componentType":"5_layouts","fileName":"t05-events-detail.twig","filePath":"5_layouts"},{"index":99,"componentType":"5_layouts","fileName":"t06-basic-listing.twig","filePath":"5_layouts"},{"index":100,"componentType":"5_layouts","fileName":"t07-news-listing.twig","filePath":"5_layouts"},{"index":101,"componentType":"field","fileName":"field--text-long.html.twig","filePath":"field"},{"index":102,"componentType":"field","fileName":"field--text-with-summary.html.twig","filePath":"field"},{"index":103,"componentType":"field","fileName":"field.html.twig","filePath":"field"}];

    // clear all filters
    this.$sortClearButton.on('click', (e) => {
      this.clearSort();
    });

    // type sort on change
    this.$sortTypeButton.on('change', (e) => {
      this.$componentFilters.removeClass(this.activeFilterClass);
      $(e.currentTarget).addClass(this.activeFilterClass);
      this.onSelectChange($(e.currentTarget));
    });

    this.$sortAscendingButton.on('click', (e) => {
      this.buttonActiveControl(e, this.ascendingSort);
    });
    this.$sortDescendingButton.on('click', (e) => {
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
}
