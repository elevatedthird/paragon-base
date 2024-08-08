import Tab from 'bootstrap/js/dist/tab';

Drupal.behaviors.kinecticTabs = {
  attach(context) {
    const tabs = once('kinetic-tabs', 'kinetic-tabs', context);
    tabs.forEach((element) => {
      // Check if this component needs to add its own events.
      if (!element.hasAttribute('data-behavior-name')) {
        new Tab(element);
        return;
      }
      const { behaviorName } = element.dataset;
      if (behaviorName in Drupal.behaviors) {
        if (typeof Drupal.behaviors[behaviorName].init === 'function') {
          const tab = new Tab(el);
          Drupal.behaviors[behaviorName].init(tab, element);
        } else {
          console.error("kineticTabs: Expected Drupal.behaviors.%s.init, but it's undefined. Please add an init function to your JS file.", behaviorName);
        }
      } else {
        console.error("kineticTabs: Expected Drupal.behaviors.%s, but it's undefined.", behaviorName);
      }
    });
  },
};
