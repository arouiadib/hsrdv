const path = require('path');
const webpack = require('webpack');
const keepLicense = require('uglify-save-license');

const psRootDir = path.resolve(process.env.PWD, '../../../');
const psJsDir = path.resolve(psRootDir, 'admin-dev/themes/new-theme/js');
const psAppDir = path.resolve(psJsDir, 'app');
const psComponentsDir = path.resolve(psJsDir, 'components');

const config = {
  entry: {
    grid: [
      './js/grid',
    ],
    form: [
      './js/form',
    ]
  },
  output: {
    path: path.resolve(__dirname, 'public'),
    filename: '[name].bundle.js'
  },
  //devtool: 'source-map', // uncomment me to build source maps (really slow)
  resolve: {
    extensions: ['.js', '.ts'],
    alias: {
      '@app': psAppDir,
      '@components': psComponentsDir,
    },
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        include: path.resolve(__dirname, '../js'),
        use: [{
          loader: 'babel-loader',
          options: {
            presets: [
              ['es2015', { modules: false }]
            ]
          }
        }]
      },
      {
        test: /\.ts?$/,
        include: path.resolve(__dirname, '../../../admin-dev/themes/new-theme/js'),
        loader: 'esbuild-loader',
        options: {
          loader: 'ts',
          target: 'es2015',
        },
        exclude: /node_modules/,
      },
    ]
  },
  plugins: []
};

if (process.env.NODE_ENV === 'production') {
  config.plugins.push(
    new webpack.optimize.UglifyJsPlugin({
      sourceMap: false,
      compress: {
        sequences: true,
        conditionals: true,
        booleans: true,
        if_return: true,
        join_vars: true,
        drop_console: true
      },
      output: {
        comments: keepLicense
      }
    })
  );
} else {
  config.plugins.push(new webpack.HotModuleReplacementPlugin());
}

module.exports = config;
