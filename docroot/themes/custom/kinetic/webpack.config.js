/**
 * @file
 * Webpack file for compiling JS and CSS files.
 */

const webpack = require('webpack');
const path = require('path');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const ESLintPlugin = require('eslint-webpack-plugin');
const StylelintPlugin = require('stylelint-webpack-plugin');
const glob = require('glob');
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');
const kineticLibraries = require('./build-utils/kineticLibraries');
const kineticLibrariesPartialsPlugin = require('./build-utils/kineticLibrariesPartialsPlugin');
const entryPoints = kineticLibraries.getEntryPointsList();

const compiledEntries = {};

for (const prop in entryPoints) {
  compiledEntries[prop] = entryPoints[prop];
}

module.exports = (env, argv) => {
  const isDev = argv.mode === 'development';
  const plugins = [
    new ESLintPlugin(),
    new StylelintPlugin({
      failOnWarning: false,
      exclude: ['node_modules', 'dist'],
    }),
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({ filename: '[name].css' }),
    new kineticLibrariesPartialsPlugin(),
  ];

  if (!isDev) {
    const purgeCss = new PurgeCSSPlugin({
      paths: glob.sync(`${__dirname}/**/*.{twig,txt,theme,yml}`),
      only: ['utilities'],
    });
    plugins.push(
      purgeCss,
    );
  }

  return {
    entry: compiledEntries,
    // See https://webpack.js.org/configuration/stats/.
    stats: {
      logging: 'warn',
      loggingDebug: ['sass-loader'],
      loggingTrace: false,
    },
    output: {
      path: __dirname,
      filename: '[name].js',
      chunkFilename: 'common/[name].chunk.js',
      chunkLoadingGlobal: 'kineticChunks',
    },

    resolve: {
      extensions: ['.js', '.json'],
      alias: {
        helpers: path.resolve(__dirname, 'source/01-base/helpers'),
        assets: path.resolve(__dirname, 'source/01-base/assets'),
        components: path.resolve(__dirname, 'source/02-components'),
      },
    },
    optimization: {
      splitChunks: {
        cacheGroups: {
          vendors: {
            test: /[\\/]node_modules[\\/](?=.*\.(js|jsx|vue|json)$)/,
            chunks: 'all',
            minSize: 1000,
            filename: 'dist/js/vendors.[name].js',
            reuseExistingChunk: true,
            priority: -5,
          },
          helpers: {
            test: /[\\/]helpers[\\/](?=.*\.(js|jsx|vue|json)$)/,
            chunks: 'all',
            minSize: 1000,
            filename: 'dist/js/helpers.[name].js',
            reuseExistingChunk: true,
            priority: -10,
          },
          components: {
            test: /[\\/]02-components[\\/](?=.*\.(js|jsx|vue|json)$)/,
            chunks: 'all',
            minSize: 1000,
            filename: 'dist/js/components.[name].js',
            reuseExistingChunk: true,
            priority: -20,
          },
        },
      },
    },

    cache: {
      type: 'filesystem',
      buildDependencies: {
        config: [__filename],
      },
    },

    experiments: {
      backCompat: false,
    },

    devtool: isDev ? 'inline-source-map' : 'source-map',

    plugins,

    module: {
      rules: [
        {
          test: /\.(js)$/,
          // Must add exceptions to this exclude statement for
          // anything that needs to be transpiled by babel.
          exclude: [/node_modules\/(?!bootstrap)/],
          use: {
            loader: 'babel-loader',
            options: {
              presets: [
                ['@babel/preset-env', {
                  exclude: ['@babel/plugin-transform-classes'],
                }],
              ],
              plugins: [
                'babel-plugin-array-includes',
                '@babel/plugin-transform-optional-chaining',
              ],
            },
          },
        },
        {
          test: /\.(png|jpg|gif|woff2?|ttf|otf|eot|svg)$/,
          exclude: '/node_modules/',
          generator: {
            filename: '[name][ext]',
            outputPath: './dist/assets/',
          },
          type: 'asset/resource',
        },
        {
          test: /\.(sa|sc|c)ss$/,
          use: [{
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: (pathInfo) => {
                // Change the path if the module is an SDC. Otherwise, assets will not point
                // to correct relative path.
                return pathInfo.includes('02-components') ? '../../../dist/assets/' : '../../dist/assets/';
              },
            },
          }, {
            loader: 'css-loader',
            options: {
              importLoaders: 1,
              sourceMap: isDev,
            },
          }, {
            loader: 'postcss-loader',
            options: {
              sourceMap: isDev,
            },
          }, {
            loader: 'sass-loader',
            options: {
              additionalData:
                '@use "sass:map";\n' +
                '@use "sass:list";\n' +
                '@use "sass:meta";\n' +
                '@use "sass:math";\n' +
                '@use "sass:string";\n' +
                '@import "./scss/_index.scss";',
              sassOptions: {
                includePaths: [
                  path.resolve(__dirname, 'node_modules/bootstrap/scss'),
                  path.resolve(__dirname, 'source/00-config'),
                ],
              },
              sourceMap: isDev,
            },
          }],
        },
      ],
    },
  };
};
