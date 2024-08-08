const path = require('path');
/** @type { import('@storybook/server-webpack5').StorybookConfig } */
const config = {
  stories: ["../source/**/*.stories.@(json|yaml|yml)"],
  addons: [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
  ],
  framework: {
    name: "@storybook/server-webpack5",
    options: {},
  },
  docs: {
    autodocs: "tag",
  },
};
export default config;
