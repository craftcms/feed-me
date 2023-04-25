/* jshint esversion: 6 */
/* globals module, require */
const {getConfig} = require('@craftcms/webpack');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = getConfig({
  context: __dirname,
  config: {
    entry: {
      FeedMe: './FeedMe.js',
    },
    plugins: [
      new CopyWebpackPlugin({
        patterns: [
          {
            from: './img/**/*',
            to: '.',
          },
        ],
      }),
    ],
  },
});
