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
    module: {
      // Remove font rule once https://github.com/craftcms/cms/pull/12923 is merged
      rules: [
        {
          test: /fonts\/[a-zA-Z0-9\-\_]*\.(ttf|woff|woff2|svg|eot)$/,
          type: 'asset/resource',
          generator: {
            filename: 'fonts/[name][ext][query]',
          },
        },
      ]
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
