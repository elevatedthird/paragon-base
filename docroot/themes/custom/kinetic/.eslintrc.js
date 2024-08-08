module.exports = {
  root: true,
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module',
  },
  env: {
    browser: true,
  },
  extends: [
    'airbnb-base',
  ],
  rules: {
    'no-undef': 0,              // disallow use of undeclared variables unless mentioned in a /*global */ block
    'import/no-unresolved': 0,  // disallows ensures an imported module can be resolved to a module on the local filesystem
    'func-names': 0,            // require function expressions to have a name (off by default)
    'no-new': 0,
    'consistent-return': 0,
    'no-param-reassign': 0,
  },
  settings: {
    'import/resolver': {
      webpack: {
        config: './webpack.config.js',
      },
    },
  },
};
