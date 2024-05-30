(function ($, Drupal, once) {

  'use strict';

  Drupal.behaviors.LayoutBuilderLock = {
    attach: function () {

      $(once('layout-builder-lock', 'fieldset.layout-builder-lock-section-settings')).each(function () {

        let lockFieldset = $(this);

        // Explicitly set display block on the form items. In some circumstances
        // the checkboxes are aligned left, so fix it here since this file
        // is loaded anyway.
        $('.form-item', lockFieldset).css('display', 'block');

        // Default value for the toggle all checkbox.
        let defaultChecked = $('.form-checkboxes .form-checkbox:checked', lockFieldset).length === $('.form-checkboxes .form-checkbox', lockFieldset).length;

        // Prepend the toggle all checkbox.
        let checkbox = '<div class="form-type-checkbox form-item">';
        checkbox += '<input type="checkbox" class="layout-builder-lock-toggle-all form-checkbox" id="layout-builder-lock-toggle-all" /> ';
        checkbox += '<label class="option" for="layout-builder-lock-toggle-all">' + Drupal.t('Toggle all') + '</label>';
        checkbox += '</div>';
        $('.form-checkboxes', lockFieldset).prepend(checkbox);

        let $toggleAll = $('.layout-builder-lock-toggle-all', lockFieldset);

        // Set default value.
        $toggleAll.prop('checked', defaultChecked);

        // Listen on change.
        $toggleAll.on('change', function () {
          let checked = FALSE;
          if ($(this).prop('checked')) {
            checked = TRUE;
          }
          $('.form-checkboxes .form-checkbox', lockFieldset).prop('checked', checked);
        });
      });
    }
  };

})(jQuery, Drupal, once);
