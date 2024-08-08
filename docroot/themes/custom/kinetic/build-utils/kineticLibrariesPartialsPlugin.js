const write_yaml = require('write-yaml');
const _ = require("lodash");
const fs = require("fs");
const pluginName = 'generatePartialsPlugin';
const YAML = require('yaml')
const kineticLibraries = require('./kineticLibraries');

class kineticLibrariesPartialsPlugin {
  apply(compiler) {
    compiler.hooks.compilation.tap(
      pluginName,
      (compilation) => {
        compilation.hooks.afterOptimizeChunkIds.tap(pluginName, () => {
          const libraries = {};
          for (const chunk of compilation.chunks) {
            // If the chunk has a filenameTemplate, it is a split module.
            if (chunk.filenameTemplate) {
              // Generate the library definition.
              let library = this.generateLibraryFromChunk(chunk);
              let libraryName = Object.keys(library)[0];
              libraries[libraryName] = _.merge(libraries[libraryName], library[libraryName]);
              if (chunk.runtime instanceof Set) {
                chunk.runtime.forEach(runtimePath => {
                  let library = kineticLibraries.getLibraryFromEntryPoint(runtimePath);
                  library.addDependency('kinetic/' + libraryName);
                })
              }
              // todo: confirm if this will always be string for single item.
              else {
                let library = kineticLibraries.getLibraryFromEntryPoint(chunk.runtime );
                library.addDependency('kinetic/' + libraryName);
              }
            }
          }
          // Create the partials.yml file for all split chunks.
          this.createLibrariesYaml(libraries);
          // For each kineticLibraries with dependencies attachPartialDependencies
          for (const kineticLibraryName in kineticLibraries.getLibraries()) {
              this.attachPartialDependencies(kineticLibraries.getLibraries()[kineticLibraryName]);
          };
        });
      },
    );
  }

  /**
   * Get the chunk name.
   *
   * todo: find a better way to do this.
   *
   * @param chunk
   * @returns {*}
   */
  getChunkFilePath(chunk) {
    const name = chunk.name || chunk.id;
    if (typeof chunk.filenameTemplate === 'function') {
      return chunk.filenameTemplate(name);
    }
    return chunk.filenameTemplate.replace(/\[name\]/g, name);
  }

  /**
   * Generate the library definition from a chunk.
   *
   * @param chunk
   * @returns {{}}
   */
  generateLibraryFromChunk(chunk) {
    const definition = {};
    const filePath = this.getChunkFilePath(chunk);
    const pathArr = filePath.split('/');
    const fileName = pathArr.slice(-1)[0];
    const entry = 'partials.' + fileName.replace(/^\d+-/ig, '');
    definition[entry] = {};

    let ext = fileName.split('.').pop();
    // todo: come back to css support.
    if (ext === 'js') {
      _.set(definition[entry], `js['${filePath}']`, {'preprocess': false, minified: true});
    }
    return definition;
  }

  /**
   * Create the partials.yml file.
   */
  createLibrariesYaml(libraries) {
    // Write out file.
    write_yaml('partials.yml', libraries, (err) => {
      console.log('Generating partials.yml');
      if (err) {
        console.error('ERROR: Could not generate partials.yml');
      }
    });
  }

  /**
   * Create the componentDependencies.yml file.
   *
   * @param {object} ComponentDependencies
   */
  attachPartialDependencies(library) {
    try {
      const file = fs.readFileSync(library.libraryPath, 'utf8')
      let Metadata = YAML.parse(file)
      let dependencies = _.get(Metadata, library.getDependencyIndex(), []) ?? [];
      let updatedDependencies = dependencies;
      if (dependencies.length !== 0) {
        console.log(`Checking dependencies in ${library.libraryPath}`);
        updatedDependencies = updatedDependencies.filter((item) => {
          return !item.startsWith('kinetic/partials.');
        });
      }
      updatedDependencies = updatedDependencies.concat(library.getDependencies());
      if (!_.isEqual(dependencies, updatedDependencies)) {
        _.set(Metadata, library.getDependencyIndex(), updatedDependencies);
        write_yaml(library.libraryPath, Metadata, (err) => {
          console.log(`Successfully added partials dependencies for ${library.libraryPath}`);
          if (err) {
            console.error(`ERROR: Could not add partials dependencies for ${library.libraryPath}`);
          }
        });
      }
    } catch(err) {
      console.error(err);
    }
  }
}

module.exports = kineticLibrariesPartialsPlugin;
