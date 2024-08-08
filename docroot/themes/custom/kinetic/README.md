# Kinetic Theme
Kinetic is a Single Directory Component (SDC) driven theme and is intended to maximize reusability, accessibility, performance and efficiency. Kinetic is a perfect starting point for theming your site.

## How to use
This theme should be directly in your themes/custom directory. See notes below for more information on how to use the theme.

## Single Directory Components
Please read about SDCs here:https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/about-single-directory-components

## Creating a new component
1. install https://www.drupal.org/project/kinetic_extras (Right now we are leaving this as optional)
2. Run `drush generate kinetic:sdc` and follow the prompts. 
3. See the information about the design system below to understand the folder structure.
4. See Tips while building components below for more information on how to build components.

## Javascript
1. For all components inside /02-components/ you can add a .es6.js file to add javascript to the component. It will get compiled into a .js file in the same directory as the component.
2. See @kinetic/source/01-base/helpers and read the README.md for more information on how to add helper functions to your theme.
3. Note that all imports will be chunked into multiple files. These will get libraritized and added as dependencies for components and the base theme library as approapriate to ensure that the smallest amount of JS is loaded on each page.

## Font Preloading
1. See the kinetic.info.yml under preload-fonts. You can add fonts to preload here. This will add a link tag to the head of the document to preload the font.
2. Note the php that drives this lives in /kinetic/src/FontPreloader.php

### Breakpoints
The breakpoints are set in 00-config/_breakpoints.scss.
Also see 01-base/global/scss/general/_breakpoints.scss. This is where css vars are printed to the root of the document.
See 01-base/global/js/breakpoints.js. This is where the breakpoints are set in JS and provides a way to interact with them in JS.

## CSS
1. The theme uses SCSS for styling. You can add a .scss file to add styles to the component. It will get compiled into a .css file in the same directory as the component.

### SCSS Architecture/Helpers/Mindsets

#### Design System
The folder structure and methodology behind the folder structure in 02-components
is meant to encourage a design system approach to building out the theme.
The idea is to have a folder for each component type, and then have a folder for each component.
This allows for easy reuse of components and encourages a design system approach to building out the theme.
You should theme everything with the mindset of re-usability and scalability.
This will help you build a theme that is easy to maintain extend and keep DRY (Don't Repeat Yourself).

#### 00-config
This area is for configuration of the theme. This is where you can set up variables for colors, breakpoints, etc.
DO NOT add any css to this folder. This is for configuration only. This is imported to the top of every component scss file magically by webpack.
So if you print out css in this folder it will be printed out in every component, which would be bad.

#### 01-base
This area is for base styles. This is where you can set up global styles for the theme. This is where you can set up typography, global styles for html, body, etc.
Utilities and rules that are global and used on every page/component should go here.

#### 02-components
This area is for components. This is where you can set up components that are used throughout the theme.
Please read the breakdown of each component type.
1. **Elements**: are the smallest building blocks of a design system. They are the basic HTML elements that are used to build more complex components. Typically used to build the next level of abstraction, Composites.
2. **Composites**: are the next level of abstraction above Elements. They are typically made up of multiple Elements and are used to build more complex Components.
3. **Blocks**: are the next level of abstraction above Composites. They are typically made up of multiple Composites and are used to build more complex Components. Blocks are not to be confused with Drupal Blocks. Drupal Blocks are a CMS feature that allows content editors to place content in regions of a page. Blocks in this context are a design system concept.
4. **Formations**: are layouts of Composites and other components that can be used to create Pages. Examples include: Header, Footer, Sidebar, etc.
5. **Pages**: are the top-level components that are rendered by the application. They are composed of Formations and other components. These are typically full on structured pages. Examples include: Blog, Post, Article, etc. Anything that isn't layout builder.

#### Surfaces
The idea of surface is to provide a way to toggle the colors of a UI based on a wrapper class.
Previously you maybe wrote specific css in each component to handle the changing of colors for typography, buttons, etc.
But now we have basic surfaces that, once set up, you can control typography, buttons, and the background color with easy extension. with just a class name on a wrapper.

##### How to use
Start configuration in settings/_surfaces.scss.
Then See button and typography for examples of how to extend the surface classes. It is recommended to pick and add a default either light or dark (or other if you configure more) as a default for your sections like so: ```<div class="layout-section surface--light">```
```html
<!-- 
  Here you could have a white bg with black text, and a black button for instance.
  One goal of surfaces is to try not have extra modifiers on nested elements like btn. It should react to the surface class. 
 -->
<div class="layout-section surface--light p-3">
  <div class="eyebrow">Eyebrow</div>
  <h2>Heading</h2>
  <p>Esse dolorum posuere eaque, ullamco voluptate dictumst magna! Sed? Rem porta scelerisque, torquent netus quas, lacinia culpa imperdiet, debitis feugiat! Doloribus! Cras convallis ridiculus, nulla.</p>
  <a href="/" class="btn">Button</a>
</div>

<!-- here the text and buttons are the same as above but the extra mod changes the background color to a configured color that goes with the scheme -->
<div class="layout-section surface--light-secondary p-3">
  <div class="eyebrow">Eyebrow</div>
  <h2>Heading</h2>
  <p>Esse dolorum posuere eaque, ullamco voluptate dictumst magna! Sed? Rem porta scelerisque, torquent netus quas, lacinia culpa imperdiet, debitis feugiat! Doloribus! Cras convallis ridiculus, nulla.</p>
  <a href="/" class="btn">Button</a>
</div>

<!-- Here an example would be a black bg with white text. -->
<div class="layout-section surface--dark p-3">
  <div class="eyebrow">Eyebrow</div>
  <h2>Heading</h2>
  <p>Esse dolorum posuere eaque, ullamco voluptate dictumst magna! Sed? Rem porta scelerisque, torquent netus quas, lacinia culpa imperdiet, debitis feugiat! Doloribus! Cras convallis ridiculus, nulla.</p>
  <a href="/" class="btn">Button</a>
</div>
```

## Tips while building components
1. While you are building your component keep in mind that you want to make it as reusable as possible.
2. Try to use utility classes to style the component, but don't be afraid to add custom styles to the component. Use your best judgement to keep your code DRY and maintainable.
3. If you are repeating yourself that's a good sign to take that piece of code and make it a utility class or a component.

## Utilizing a Bootstrap 5 Component
1. Go to https://getbootstrap.com/docs/5.0/components/ and find the component you want to use.
2. Copy the HTML and paste it into your component.
3. In the SCSS @import the bootstrap component you want to use.
4. Above that you can configure the bootstrap variables to change the look of the component. See ~bootstrap/scss/_variables.scss for all the variables you can change.
5. After that you can add your own custom styles to the component below the import.
```scss
// change bootstrap variables
$dropdown-color: #000;
$dropdown-bg: #fff;
// import bootstrap component
@import "~bootstrap/scss/dropdown";
// add custom styles
.dropdown {
  background-color: #000;
}
```
6. For JS components you can add the JS to the .es6.js file and it will be compiled into a .js file with the same name as the component directory.
```js
import { Dropdown } from 'bootstrap';
// attach the behavior
Drupal.behaviors.dropdown = {
  attach(context, settings) {
    // ensure you use Once to prevent attaching the behavior multiple times, and avoid using jQuery
    // https://www.drupal.org/docs/drupal-apis/javascript-api/javascript-api-overview
    const dropbuttons = once('dropbutton', '.dropdown', context);
    dropbuttons.forEach((dropbutton) => {
      new Dropdown(dropbutton);
    });
  }
};
```
7. Try to use utility classes to style the component, but don't be afraid to add custom styles to the component. Use your best judgement to keep your code DRY and maintainable.

### Breakpoints
The breakpoints are set in 00-config/_breakpoints.scss.
Also see 01-base/global/scss/general/_breakpoints.scss. This is where css vars are printed to the root of the document.
See 01-base/global/js/breakpoints.js. This is where the breakpoints are set in JS and provides a way to interact with them in JS.

### Storybook
We have a storybook set up to help you build out components. You can run `npm run storybook` to start the storybook server.

Please install and see instructions for some setup here: https://www.drupal.org/project/storybook

1. Setup you development.services.yml to have the correct settings for storybook.
2. Ensure User permissions.
3. IMPORTANT you will need these patches (at least when I added this):
   4. ```json
            "drupal/storybook": {
                "Allow the server url to be set as a global parameter": "https://git.drupalcode.org/project/storybook/-/merge_requests/6.diff",
                "The story json generation should skip symlinks.": "https://git.drupalcode.org/project/storybook/-/merge_requests/8.diff"
            },
            "e0ipso/twig-storybook": {
                "Feature allow global server url": "https://github.com/e0ipso/twig-storybook/pull/11.patch"
            }
4. Run `drush storybook:generate-all-stories --omit-server-url --force` to generate the .json stories.
5. You can watch for changes with `watch drush storybook:generate-all-stories --omit-server-url --force` to generate the .json stories. Note you need to install watch with brew or something.
6. Run `export STORYBOOK_SERVER_URL=https://your-local.test/storybook/stories/render; npm run storybook` to start the storybook server. You could add scripts to your project to make this easier.
7. You can build a public facing storybook like this `export STORYBOOK_SERVER_URL=https://your-local.test/storybook/stories/render; npm run build-storybook`
