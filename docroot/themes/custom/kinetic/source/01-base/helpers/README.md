# Helpers
Place ES6 modules here. Files in this directory contain custom modules
that can be imported into other JS files. Files should NOT declare any
Drupal behaviors. Each file must contain only a single default export. Please
do not export more than one function, as it will increase bundle sizes!

## Webpack alias
Webpack is configured to use the `helpers` alias for resolving the helpers directory. See
example below.

Ex.
```
// helpers/MyHelper.js
function MyHelper(options) {
  this.options = options;
  method() {
    // do stuff
  }
}

export default MyHelper;

------------------------------------------
// SDC JS file
import MyHelper from 'helpers/MyHelper';
const MyHelper = new MyHelper();

```
