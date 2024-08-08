Drupal.behaviors.carousel = {
  options: {
    perView: 1,
    rewind: false,
  },
  init(glide) {
    glide.on('mount.after', () => {
      console.log('basic carousel mounted');
    });
    glide.mount();
  },
};
