Drupal.behaviors.paragonGinLayoutBuilder = {
  insertTooltip(el, content, x = null, y = null) {
    const tooltip = document.createElement('div');
    tooltip.classList.add('lb-tooltip');
    tooltip.innerHTML = `<div> ${content.replaceAll('_', ' ')} </div>`;
    if (x) {
      tooltip.style.left = x;
    }
    if (y) {
      tooltip.style.top = y;
    }
    el.appendChild(tooltip);
    el.setAttribute('data-tooltip-added', 'true');
  },
  attach() {
    // Layout builder improvements.
    const layoutBuilder = document.getElementById('layout-builder');
    if (layoutBuilder) {
      const blocks = layoutBuilder.querySelectorAll('.js-layout-builder-block.layout-builder-block');
      blocks.forEach((el) => {
        // Add a tooltip for block name.
        const { paragonGinPluginId } = el.dataset;
        if (paragonGinPluginId !== undefined && !el.hasAttribute('data-tooltip-added')) {
          this.insertTooltip(el, paragonGinPluginId, '0', '0');
        }
      });
      // Add a tooltip for section configuration.
      const addSections = layoutBuilder.querySelectorAll('.layout-builder__section > a.layout-builder__link');
      addSections.forEach((el) => {
        if (!el.hasAttribute('data-tooltip-added')) {
          const linkText = el.textContent.trim();
          this.insertTooltip(el, linkText, '0', '-50px');
        }
      });
    }
  }
}
