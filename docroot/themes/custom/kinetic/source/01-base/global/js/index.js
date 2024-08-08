/**
 * theme.js
 * Entry point for all theme related js.
 */
// eslint-disable-next-line unused-imports/no-unused-vars
import { createPopper } from '@popperjs/core';
import "bootstrap/js/dist/collapse";

Drupal.behaviors.skipLink = {
  attach(context) {
    if (context !== document) {
      return;
    }

    // Skip Link for accessibility.
    const skipLink = document.querySelector('.skip-to-content-link');
    if (skipLink) {
      skipLink.addEventListener('click', (e) => {
        e.preventDefault();

        const target = document.querySelector(e.target.getAttribute('href'));

        target.setAttribute('tabindex', '-1');
        target.focus();
        target.addEventListener('blur focusout', (event) => {
          event.target.removeAttribute('tabindex');
        });
      });
    }

    // using this as its smaller, but can implement the sr link icon script if need.
    for (let links = document.links, i = 0, a; a = links[i]; i++) {
      if (a.host !== window.location.host || a.href.includes('sites/default/files')) {
        a.target = '_blank';
        a.setAttribute('rel', 'nofollow');
      }
    }

    // Let the document know when the mouse is being used
    document.body.addEventListener('mousedown', () => {
      document.body.classList.add('using-mouse');
      document.body.classList.remove('using-tab');
    });

    // Re-enable focus styling when Tab is pressed
    document.body.addEventListener('keydown', (event) => {
      if (event.code === 'Tab') {
        document.body.classList.remove('using-mouse');
        document.body.classList.add('using-tab');
      }
    });
  }
}
