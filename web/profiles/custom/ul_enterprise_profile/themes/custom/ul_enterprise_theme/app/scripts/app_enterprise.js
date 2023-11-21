// https://webpack.js.org/configuration/output/#output-publicpath,
// https://github.com/webpack/webpack/issues/2776#issuecomment-233208623
/* eslint-disable no-undef */
if (typeof asset_path !== "undefined") {
  __webpack_public_path__ = asset_path;
}
import moduleRegistry from './modules/';

moduleRegistry.init();
