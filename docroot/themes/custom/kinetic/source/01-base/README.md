# 01-Base
This directory is a place global styles and javascript can be added.
## core
Contains all the CSS and images required from the starterkit base theme

 helpers
See readme in that directory.

## js
Contains global JS that will be loaded on EVERY page. Be mindful of what is added here.

## scss
### components
Global styles that affect the site at a more specific level. If a new file
is created here, be sure to import it into index.scss.

### general
Global styles that affect the site at a theme level. Files in here have rules
that will affect every page. If a new file is created, be sure to import it into
index.scss.

### shame.scss
Last minute hotfixes or global SCSS styles that don't have a place anywhere else. Please do not use this file unless you really have to!

### utilities.scss
Import bootstrap utilties ONLY! This file will also run through
the PurgeCSS webpack plugin. This file has its own entry point in webpack.

### wysiwyg.scss
WYSIWYG specific styles.

### layout-builder.scss
Site specific overrides for layout builder pages. If the theme is causing style issues, you can fix them here. This file is ONLY loaded on layout builder pages

### index.scss
The file that contians all imports of other scss files in the `scss` folder. This will be used as the
webpack entry point for compiling global css.

## Purge CSS
Kinetic uses the [webpack PurgeCSS plugin](https://purgecss.com/plugins/webpack.html) to remove unused style rules. This plugin
looks for twig and txt files and cheks for class names that have it's rules defined in
the index.scss file. If the class is not used, it will be removed from the built CSS file.


