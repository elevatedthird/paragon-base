'use strict';

Drupal.behaviors.paragonGinLbb = {
  toggleView() {
    // Remove active class from buttons.
    Array.from(this.parentElement.children).forEach(button => {
      button.classList.remove('active');
    });
    this.classList.add('active');
    // Get the view from the button.
    const { view } = this.dataset;
    // Save selected option into localStorage.
    window.localStorage.setItem('paragon_gin_view', view);
    document.querySelectorAll('.paragon-gin-browser-wrapper').forEach(el => {
      el.setAttribute('data-view', view);
    });
  },
  attach(context, settings) {
    const [iconView] = once('paragon-gin', '#paragon-gin-icon-view', context);
    const [listView] = once('paragon-gin', '#paragon-gin-list-view', context);
    if (iconView === undefined && listView === undefined) { 
      return;
    }
    // Add click event to buttons.
    iconView.addEventListener('click', this.toggleView);
    listView.addEventListener('click', this.toggleView);

    // Set default view.
    const { default_view } = settings.paragon_gin;
    const defaultView = window.localStorage.getItem('paragon_gin_view') || default_view;
    if (defaultView === 'icon') {
      iconView.click();
    }
    else if (defaultView === 'list') {
      listView.click();
    }
  },
};

