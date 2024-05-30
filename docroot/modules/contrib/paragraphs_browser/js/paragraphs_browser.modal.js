/**
 * @file paragraphs_browser.modal.js
 *
 */

(function ($, Drupal, drupalSettings, once) {

  'use strict';

  Drupal.AjaxCommands.prototype.paragraphs_browser_add_paragraph = function (ajax, response, status) {
    $('select[data-uuid="' + response.uuid + '"]').val(response.paragraph_type);
    $('input[data-uuid="' + response.uuid + '"]').trigger('mousedown');
  };

   Drupal.behaviors.PBModalFilter = {
    attach: function (context) {
      var $context = $(context);
      var $wrapper = $context.find('.paragraphs-browser-wrapper');
      var $filter = $wrapper.find('input[data-drupal-selector="edit-pb-modal-text"]');
      var $selectFilter = $wrapper.find('select[data-drupal-selector="edit-filters"]');

      if ($filter.length) {
        $filter.keyup(function () {
          var value = $(this).val().toUpperCase();

          if (value) {
            $('div[data-drupal-selector]', $wrapper).each(function() {
              var notEmpty = false;

              $('fieldset', this).each(function() {
                if ($(this).text().toUpperCase().search(value) > -1) {
                  notEmpty = true;
                  $(this).show();
                }
                else {
                  $(this).hide();
                }
              });
              if (!notEmpty) {
                $(this).hide();
              }
              else {
                select_visibility_element($(this), $selectFilter.val());
              }
            });
          }
          else {
            $('div[data-drupal-selector]', $wrapper).each(function() {
              $('fieldset', this).each(function() {
                $(this).show();
              });
              $selectFilter.trigger('change');
            });
          }
        });
      }

      if ($selectFilter.length) {
        $selectFilter.change(function () {
          var value = $(this).val();
          $('div[data-drupal-selector]', $wrapper).each(function() {
            select_visibility_element($(this), value);
          });

          if ($filter.val()) {
            $filter.val('').trigger('keyup');
          }

        });
      }

      // Manage visibility of elements.
      function select_visibility_element($element, value) {
        if (value === 'all') {
          $element.show();
        }
        else if ($element.hasClass(value)) {
          $element.show();
        }
        else {
          $element.hide();
        }
      }

    }
  };

  Drupal.behaviors.PBStyling = {
    attach: function (context) {
      const elements = once('fieldsetclick', '.paragraphs-browser-wrapper', context);
      elements.forEach(el => {
        el.addEventListener('click', function (e) {
          const element = e.target.closest('.paragraphs-browser-paragraph-type');
          if (element) {
            e.preventDefault();
            e.stopPropagation();
            const inputElement = element.querySelector('input');
            if (inputElement) {
              inputElement.dispatchEvent(new Event('mousedown'));
            }
          }
        });
      });
    }
  };

}(jQuery, Drupal, drupalSettings, once));
