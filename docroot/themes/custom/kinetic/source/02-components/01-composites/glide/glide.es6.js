import Glide from '@glidejs/glide';

Drupal.behaviors.kineticGlide = {
  attach(context) {
    const sliders = once('kinetic-glide', '.glide', context);
    sliders.forEach((element) => {
      const glide = new Glide(element);
      // Attach global events that affect all sliders.
      glide.on('mount.after', () => element.classList.add('mounted'));
      // Check if this component implements glide itself.
      if (!element.hasAttribute('data-behavior-name')) {
        glide.mount();
        return;
      }
      const { behaviorName } = element.dataset;
      if (behaviorName in Drupal.behaviors) {
        if ('options' in Drupal.behaviors[behaviorName]) {
          glide.update(Drupal.behaviors[behaviorName].options);
        } else {
          console.error("kineticGlide: Expected Drupal.behaviors.%s.options, but it's undefined. Please add an options key containing Glide.js options");
        }
        if (typeof Drupal.behaviors[behaviorName].init === 'function') {
          Drupal.behaviors[behaviorName].init(glide, element);
        } else {
          console.error("kineticGlide: Expected Drupal.behaviors.%s.init, but it's undefined. Please add an init function to your JS file.");
        }
      } else {
        console.error("kineticGlide: Expected Drupal.behaviors.%s, but it's undefined.", behaviorName);
      }
    });
  },
};
