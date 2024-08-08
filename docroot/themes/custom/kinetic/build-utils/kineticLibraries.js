const glob = require('glob');
const path = require('path');
const fs = require("fs")



class kineticLibraries {
  constructor() {
    this.libraries = {};
  }

  addLibrary(library) {
    if(this.libraries.hasOwnProperty(library.libraryPath + '|' + library.libraryName)) {
      throw new Error('Library already exists.');
    }
    this.libraries[library.libraryPath + '|' + library.libraryName] = library;
    return this;
  }

  getLibrary(libraryPath, libraryName) {
    return this.libraries[libraryPath + '|' + libraryName];
  }

  getLibraryFromEntryPoint(destination) {
    for (const libraryName in this.libraries) {
      for (const entryPointSource in this.libraries[libraryName].entryPoints) {
        // If source matches entry point source before the '.'.
        if (destination === this.libraries[libraryName].entryPoints[entryPointSource]) {
          return this.libraries[libraryName];
        }
      }
    }
    return false;
  }

  getLibraries() {
    return this.libraries;
  }

  getEntryPointsList() {
    let entryPointList = {};
    for (const libraryName in this.libraries) {
      for (const entryPointSource in this.libraries[libraryName].entryPoints) {
        // If already exists, turn into an array.
        if (!entryPointList.hasOwnProperty(this.libraries[libraryName].entryPoints[entryPointSource])) {
          entryPointList[this.libraries[libraryName].entryPoints[entryPointSource]] = [entryPointSource];
        }
        else {
          entryPointList[this.libraries[libraryName].entryPoints[entryPointSource]].push(entryPointSource);
        }
      }
    }
    return entryPointList;
  }
}

class kineticLibrary {
  entryPoints = {};
  dependencies = [];
  constructor(libraryPath, libraryName) {
    this.libraryPath = libraryPath;
    this.libraryName = libraryName;
  }
  addEntryPoint(destination, source, validate = true) {
    this.entryPoints[source] = destination;
    return this;
  }

  hasDependency(libraryName) {
    return this.dependencies.indexOf(libraryName) !== -1;
  }
  addDependency(libraryName) {
    if (!this.hasDependency(libraryName)) {
      this.dependencies.push(libraryName);
    }
    return this;
  }
  getDependencies() {
    return this.dependencies;
  }
  hasDependencies() {
    return this.dependencies.length > 0;
  }
  getDependencyIndex() {
    return [this.libraryName, 'dependencies'];
  }
}

class kineticComponentLibrary extends kineticLibrary {
  constructor(libraryPath, libraryName) {
    super(libraryPath, libraryName);
  }
  getDependencyIndex() {
    return ['libraryOverrides', 'dependencies'];
  }
}

let librariesInstance = new kineticLibraries();

librariesInstance.addLibrary(
  new kineticLibrary(
    'kinetic.libraries.yml',
    'kinetic',
  ).addEntryPoint(
    'dist/js/index',
    './source/01-base/global/js/index.js',
  ).addEntryPoint(
    'dist/js/breakpoints',
    './source/01-base/global/js/breakpoints.js',
  ).addEntryPoint(
    'dist/css/index',
    './source/01-base/global/scss/index.scss',
  ).addEntryPoint(
    'dist/css/utilities',
    './source/01-base/global/scss/utilities.scss',
  ).addEntryPoint(
    'dist/css/wysiwyg',
    './source/01-base/global/scss/wysiwyg.scss',
  ).addEntryPoint(
    'dist/css/layout-builder',
    './source/01-base/global/scss/layout-builder.scss',
  )
);

const componentDirName = '02-components';
const sdcSource = path.resolve('source', componentDirName);

// Get all the SCSS and dev.js files in the SDC directory.
glob.sync('**/*.{scss,es6.js}', {
  cwd: sdcSource,
})
.forEach((assetPath) => {
  // Entry point is the name of the SDC directory.
  const pathArr = assetPath.split('/');
  const componentName = pathArr.slice(-2)[0];
  const componentRelativePath = pathArr.slice(0, -1).join('/');
  const componentPath =  `source/${componentDirName}/${componentRelativePath}`;
  const componentYmlPath = `${componentPath}/${componentName}.component.yml`;
  // Only process entry points that are known Drupal components.
  if (fs.existsSync(componentYmlPath)) {
    let componentLibrary = librariesInstance.getLibrary(componentYmlPath, componentName);
    if (!componentLibrary) {
        componentLibrary =  new kineticComponentLibrary(
        componentYmlPath,
        componentName,
      );
      librariesInstance.addLibrary(componentLibrary);
    }
    componentLibrary.addEntryPoint(
      `${componentPath}/${componentName}`,
      `./source/${componentDirName}/${assetPath}`,
    );
  }
});

module.exports = librariesInstance;
