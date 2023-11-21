// Webpack uses this to work with directories
const path = require('path');

const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CompressionPlugin = require("compression-webpack-plugin");

module.exports = {

  mode: 'production',
  
  // https://webpack.js.org/concepts/#entry
  entry: './src_static/js/ul-certification.js',  
  
  // https://webpack.js.org/concepts/output/
  output: {
    publicPath: '',
    path: path.resolve(__dirname, './dist'),
    filename: './dist.ul-certification.js',
    assetModuleFilename: 'images/[hash][ext][query]',
    libraryTarget: 'var',
    library: 'ULCertEntry'
  },

  // https://webpack.js.org/concepts/modules/
  module: {
    rules: [
      {
        // Apply rule for .js
        test: /\.js$/,
        exclude: /(node_modules)/,
        // Set loaders to transform files.
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.css$/i,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },    
          {
            loader: "css-loader",
          },
          
          { 
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  require('postcss-import'),
                  require('tailwindcss'),
                  require('postcss-nested'),
                  require('autoprefixer'),
                ],
              },
            }, 
          }
        ]
      },
      {
        test: /\.(png|jpe?g|gif|svg|eot|ttf|woff|woff2)$/i,
        // More information here https://webpack.js.org/guides/asset-modules/
        type: 'asset/resource',
        use: [
          {
            loader: 'image-webpack-loader',
            options: {
              mozjpeg: {
                progressive: true,
                quality: 65
              },
              optipng: {
                enabled: false
              },
              pngquant: {
                quality: [0.65, 0.90],
                speed: 4
              },
              gifsicle: {
                interlaced: false
              },
              webp: {
                quality: 75
              }
            }
          }
        ]
      }
    ]
  },

  optimization: {
    minimize: true,
  },

  // https://webpack.js.org/concepts/plugins/
  plugins: [
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: "dist.ul-certification.css"
    }),
    new CompressionPlugin()
  ]
};