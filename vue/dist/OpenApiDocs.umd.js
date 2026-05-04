(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["OpenApiDocs"] = factory(require("CoreHome"), require("vue"));
	else
		root["OpenApiDocs"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/OpenApiDocs/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "027d":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerUiPanel_vue_vue_type_style_index_0_id_9b7514a6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("47f8");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerUiPanel_vue_vue_type_style_index_0_id_9b7514a6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerUiPanel_vue_vue_type_style_index_0_id_9b7514a6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "47f8":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "541e":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerPage_vue_vue_type_style_index_0_id_d027320c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("7832");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerPage_vue_vue_type_style_index_0_id_d027320c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerPage_vue_vue_type_style_index_0_id_d027320c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "7832":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "SwaggerPage", function() { return /* reexport */ SwaggerPage; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=template&id=d027320c&scoped=true

const _withScopeId = n => (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["pushScopeId"])("data-v-d027320c"), n = n(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["popScopeId"])(), n);
const _hoisted_1 = {
  class: "page"
};
const _hoisted_2 = {
  key: 1
};
const _hoisted_3 = {
  key: 2
};
const _hoisted_4 = {
  class: "searchBar"
};
const _hoisted_5 = /*#__PURE__*/_withScopeId(() => /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "searchIcon icon-search"
}, null, -1));
const _hoisted_6 = ["placeholder"];
const _hoisted_7 = {
  key: 0,
  class: "emptyText"
};
const _hoisted_8 = {
  key: 1,
  class: "pluginList"
};
const _hoisted_9 = ["onMouseenter"];
const _hoisted_10 = ["aria-expanded", "onFocus", "onClick"];
const _hoisted_11 = {
  class: "pluginHeader"
};
const _hoisted_12 = {
  class: "pluginName"
};
const _hoisted_13 = {
  class: "card-content pluginBody"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_SwaggerUiPanel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SwaggerUiPanel");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPageTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPageDescription')), 1)])), [[_directive_content_intro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isLoading
    }, null, 8, ["loading"]), _ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
      key: 0,
      severity: "danger"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadError), 1)]),
      _: 1
    })) : !_ctx.isLoading && _ctx.plugins.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPagePluginEmpty')), 1)) : !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.searchTerm = $event),
      type: "text",
      class: "searchInput browser-default",
      placeholder: _ctx.translate('OpenApiDocs_SwaggerPageSearchPlaceholder')
    }, null, 8, _hoisted_6), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm]])]), _ctx.filteredPlugins.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPageSearchNoResults')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filteredPlugins, plugin => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        key: plugin,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['card', 'pluginCard', {
          'pluginCard--expanded': _ctx.expandedPluginName === plugin
        }]),
        onMouseenter: $event => _ctx.prefetchPluginSpec(plugin)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "pluginToggle",
        "aria-expanded": _ctx.expandedPluginName === plugin ? 'true' : 'false',
        onFocus: $event => _ctx.prefetchPluginSpec(plugin),
        onClick: $event => _ctx.togglePlugin(plugin)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['pluginChevron', _ctx.expandedPluginName === plugin ? 'icon-chevron-down' : 'icon-chevron-right'])
      }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin), 1)])], 40, _hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
        name: "pluginBodyTransition",
        onBeforeEnter: _ctx.onPluginBodyBeforeEnter,
        onEnter: _ctx.onPluginBodyEnter,
        onBeforeLeave: _ctx.onPluginBodyBeforeLeave,
        onLeave: _ctx.onPluginBodyLeave,
        onAfterEnter: _ctx.resetPluginBodyTransitionStyles,
        onAfterLeave: _ctx.resetPluginBodyTransitionStyles
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SwaggerUiPanel, {
          plugin: plugin,
          spec: _ctx.getPluginSpecState(plugin).spec,
          "is-loading": _ctx.getPluginSpecState(plugin).status === 'loading',
          "spec-load-error": _ctx.getPluginSpecState(plugin).loadError
        }, null, 8, ["plugin", "spec", "is-loading", "spec-load-error"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.expandedPluginName === plugin]])]),
        _: 2
      }, 1032, ["onBeforeEnter", "onEnter", "onBeforeLeave", "onLeave", "onAfterEnter", "onAfterLeave"])], 42, _hoisted_9);
    }), 128))]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })]);
}
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=template&id=d027320c&scoped=true

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=template&id=9b7514a6&scoped=true

const SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_withScopeId = n => (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["pushScopeId"])("data-v-9b7514a6"), n = n(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["popScopeId"])(), n);
const SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_hoisted_1 = {
  key: 0,
  class: "swaggerLoader"
};
const SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_hoisted_2 = ["id"];
function SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [_ctx.isLoading && !_ctx.spec && !_ctx.displayError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: true
  })])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.displayError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.displayError), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    id: _ctx.swaggerContainerId,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['swaggerMount', {
      'swaggerMount--ready': _ctx.isReady
    }])
  }, null, 10, SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=template&id=9b7514a6&scoped=true

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=script&lang=ts


const activeCopySuccessStateKey = '__matomoActiveCopySuccessState';
const summaryPathClickHandlerAttachedKey = '__matomoSummaryPathClickHandlerAttached';
const summaryPrefix = '/index.php?module=API&method=';
const interactiveSwaggerSelector = '.opblock-tag, .opblock-summary, .expand-operation, .opblock-summary-control';
const copyIconMarkup = '<span class="icon-content-copy" aria-hidden="true"></span>';
const copySuccessIconMarkup = '<i class="icon-ok matomo-copy-success-icon" aria-hidden="true"></i>';
const disableAuthorizePlugin = () => ({
  wrapComponents: {
    authorizeBtn: () => () => null
  }
});
/* harmony default export */ var SwaggerUiPanelvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"]
  },
  props: {
    plugin: {
      type: String,
      required: true
    },
    spec: {
      type: Object,
      default: null
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    specLoadError: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      isReady: false,
      loadError: null
    };
  },
  computed: {
    displayError() {
      return this.specLoadError || this.loadError;
    },
    swaggerContainerId() {
      return `swagger-ui-${this.plugin}`;
    }
  },
  watch: {
    spec: {
      immediate: true,
      handler(spec) {
        if (spec) {
          this.renderSwaggerUi();
          return;
        }
        this.resetSwaggerUi();
      }
    }
  },
  beforeUnmount() {
    const container = this.getSwaggerRoot();
    if (!container) {
      return;
    }
    this.clearCopySuccessState(container);
  },
  methods: {
    getSwaggerRoot() {
      return document.getElementById(this.swaggerContainerId);
    },
    shortenSummaryPaths(swaggerRoot) {
      const summaryPaths = swaggerRoot.querySelectorAll('.opblock-summary-path');
      summaryPaths.forEach(element => {
        const fullPath = element.getAttribute('data-path');
        if (!fullPath || !fullPath.startsWith(summaryPrefix)) {
          return;
        }
        element.textContent = fullPath.substring(summaryPrefix.length);
        element.setAttribute('title', fullPath);
      });
    },
    updateFlatSingleTag(swaggerRoot) {
      const tagSections = swaggerRoot.querySelectorAll('.opblock-tag-section');
      tagSections.forEach(tagSection => {
        tagSection.classList.remove('matomo-flat-tag');
      });
      if (tagSections.length !== 1) {
        return;
      }
      const tagSection = tagSections[0];
      if (tagSection.querySelector(':scope > .opblock-tag')) {
        tagSection.classList.add('matomo-flat-tag');
      }
    },
    applyMatomoCopyIcons(swaggerRoot) {
      const copyControls = swaggerRoot.querySelectorAll('.opblock-summary .view-line-link.copy-to-clipboard');
      copyControls.forEach(element => {
        if (element.classList.contains('matomo-copy-success')) {
          return;
        }
        element.innerHTML = copyIconMarkup;
      });
    },
    normalizeSwaggerUi(swaggerRoot) {
      this.shortenSummaryPaths(swaggerRoot);
      this.updateFlatSingleTag(swaggerRoot);
      this.applyMatomoCopyIcons(swaggerRoot);
    },
    getSummaryPathCopyControl(target) {
      return target === null || target === void 0 ? void 0 : target.closest('.opblock-summary .view-line-link.copy-to-clipboard');
    },
    getFlatTagHeader(target) {
      return target === null || target === void 0 ? void 0 : target.closest('.opblock-tag-section.matomo-flat-tag > .opblock-tag');
    },
    clearCopySuccessState(swaggerRoot) {
      const state = swaggerRoot[activeCopySuccessStateKey];
      if (!state) {
        return;
      }
      const {
        element
      } = state;
      window.clearTimeout(state.resetTimeoutId);
      element.innerHTML = copyIconMarkup;
      element.classList.remove('matomo-copy-success');
      element.classList.remove('matomo-copy-reset');
      window.requestAnimationFrame(() => {
        element.classList.add('matomo-copy-reset');
        window.setTimeout(() => {
          element.classList.remove('matomo-copy-reset');
        }, 300);
      });
      swaggerRoot[activeCopySuccessStateKey] = null;
    },
    showCopySuccessState(swaggerRoot, control) {
      this.clearCopySuccessState(swaggerRoot);
      control.innerHTML = copySuccessIconMarkup;
      control.classList.remove('matomo-copy-reset');
      control.classList.add('matomo-copy-success');
      swaggerRoot[activeCopySuccessStateKey] = {
        element: control,
        resetTimeoutId: window.setTimeout(() => {
          var _swaggerRoot$activeCo;
          if (((_swaggerRoot$activeCo = swaggerRoot[activeCopySuccessStateKey]) === null || _swaggerRoot$activeCo === void 0 ? void 0 : _swaggerRoot$activeCo.element) === control) {
            this.clearCopySuccessState(swaggerRoot);
          }
        }, 3000)
      };
    },
    attachSwaggerInteractionHandlers(swaggerRoot) {
      if (!swaggerRoot || swaggerRoot[summaryPathClickHandlerAttachedKey]) {
        return;
      }
      swaggerRoot[summaryPathClickHandlerAttachedKey] = true;
      swaggerRoot.addEventListener('click', event => {
        const target = event.target;
        const flatTagHeader = this.getFlatTagHeader(target);
        if (flatTagHeader) {
          if (!(target !== null && target !== void 0 && target.closest('a'))) {
            event.preventDefault();
          }
          event.stopPropagation();
          return;
        }
        const summaryPathCopyControl = this.getSummaryPathCopyControl(target);
        if (summaryPathCopyControl) {
          window.setTimeout(() => {
            if (summaryPathCopyControl.isConnected) {
              this.showCopySuccessState(swaggerRoot, summaryPathCopyControl);
            }
          }, 0);
        }
        if (!(target !== null && target !== void 0 && target.closest(interactiveSwaggerSelector))) {
          return;
        }
        window.setTimeout(() => {
          this.normalizeSwaggerUi(swaggerRoot);
        }, 0);
      }, true);
    },
    resetSwaggerUi() {
      const container = this.getSwaggerRoot();
      this.isReady = false;
      this.loadError = null;
      if (!container) {
        return;
      }
      this.clearCopySuccessState(container);
      container.innerHTML = '';
    },
    renderSwaggerUi() {
      var _swaggerUiBundle$pres;
      const swaggerUiBundle = window.SwaggerUIBundle;
      const container = this.getSwaggerRoot();
      this.isReady = false;
      this.loadError = null;
      if (!swaggerUiBundle || !container || !this.spec) {
        if (!this.spec) {
          return;
        }
        this.isReady = true;
        this.loadError = Object(external_CoreHome_["translate"])('OpenApiDocs_SwaggerPageSpecLoadFailed');
        return;
      }
      container.innerHTML = '';
      swaggerUiBundle({
        dom_id: `#${this.swaggerContainerId}`,
        spec: this.spec,
        deepLinking: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: -1,
        layout: 'BaseLayout',
        tagsSorter: 'alpha',
        presets: (_swaggerUiBundle$pres = swaggerUiBundle.presets) !== null && _swaggerUiBundle$pres !== void 0 && _swaggerUiBundle$pres.apis ? [swaggerUiBundle.presets.apis] : [],
        plugins: [disableAuthorizePlugin],
        onComplete: () => {
          window.setTimeout(() => {
            this.normalizeSwaggerUi(container);
            this.isReady = true;
          }, 0);
          this.attachSwaggerInteractionHandlers(container);
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=style&index=0&id=9b7514a6&scoped=true&lang=css
var SwaggerUiPanelvue_type_style_index_0_id_9b7514a6_scoped_true_lang_css = __webpack_require__("027d");

// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue





SwaggerUiPanelvue_type_script_lang_ts.render = SwaggerUiPanelvue_type_template_id_9b7514a6_scoped_true_render
SwaggerUiPanelvue_type_script_lang_ts.__scopeId = "data-v-9b7514a6"

/* harmony default export */ var SwaggerUiPanel = (SwaggerUiPanelvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=script&lang=ts



/* harmony default export */ var SwaggerPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    SwaggerUiPanel: SwaggerUiPanel
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  computed: {
    filteredPlugins() {
      const searchTerm = this.searchTerm.trim().toLowerCase();
      if (!searchTerm) {
        return this.plugins;
      }
      return this.plugins.filter(plugin => plugin.toLowerCase().includes(searchTerm));
    }
  },
  data() {
    return {
      expandedPluginName: null,
      isLoading: false,
      loadError: null,
      plugins: [],
      pluginSpecs: {},
      searchTerm: ''
    };
  },
  created() {
    this.fetchPlugins();
  },
  watch: {
    searchTerm(value) {
      if (this.expandedPluginName && !this.matchesSearch(this.expandedPluginName, value)) {
        this.expandedPluginName = null;
      }
    }
  },
  methods: {
    matchesSearch(plugin, searchTerm) {
      const normalizedSearchTerm = (searchTerm !== null && searchTerm !== void 0 ? searchTerm : this.searchTerm).trim().toLowerCase();
      return plugin.toLowerCase().includes(normalizedSearchTerm);
    },
    forceReflow(element) {
      element.getBoundingClientRect();
    },
    getPluginBodyTransitionDuration(height) {
      return Math.min(400, Math.max(180, Math.round(height / 4)));
    },
    resetPluginBodyTransitionStyles(element) {
      const htmlElement = element;
      htmlElement.style.height = '';
      htmlElement.style.transitionDuration = '';
      htmlElement.style.overflow = '';
    },
    setPluginBodyTransitionState(element, height) {
      element.style.height = height;
      element.style.overflow = 'hidden';
    },
    transitionPluginBody(element, startHeight, endHeight) {
      this.setPluginBodyTransitionState(element, startHeight);
      element.style.transitionDuration = `${this.getPluginBodyTransitionDuration(element.scrollHeight)}ms`;
      this.forceReflow(element);
      element.style.height = endHeight;
    },
    onPluginBodyBeforeEnter(element) {
      this.setPluginBodyTransitionState(element, '0');
    },
    onPluginBodyEnter(element) {
      const htmlElement = element;
      this.transitionPluginBody(htmlElement, '0', `${htmlElement.scrollHeight}px`);
    },
    onPluginBodyBeforeLeave(element) {
      const htmlElement = element;
      this.setPluginBodyTransitionState(htmlElement, `${htmlElement.scrollHeight}px`);
    },
    onPluginBodyLeave(element) {
      const htmlElement = element;
      this.transitionPluginBody(htmlElement, `${htmlElement.scrollHeight}px`, '0');
    },
    async fetchPlugins() {
      this.expandedPluginName = null;
      this.isLoading = true;
      this.loadError = null;
      this.plugins = [];
      this.pluginSpecs = {};
      this.searchTerm = '';
      try {
        const plugins = await external_CoreHome_["AjaxHelper"].fetch({
          method: 'OpenApiDocs.getPluginWhitelist'
        }, {
          createErrorNotification: false
        });
        this.plugins = [...plugins].sort((left, right) => left.localeCompare(right));
      } catch (_unused) {
        this.loadError = Object(external_CoreHome_["translate"])('OpenApiDocs_SwaggerPageRequestFailed');
      } finally {
        this.isLoading = false;
      }
    },
    createPluginSpecState() {
      return {
        loadError: null,
        request: null,
        spec: null,
        status: 'idle'
      };
    },
    getPluginSpecState(plugin) {
      if (!this.pluginSpecs[plugin]) {
        this.pluginSpecs[plugin] = this.createPluginSpecState();
      }
      return this.pluginSpecs[plugin];
    },
    async prefetchPluginSpec(plugin, forceReload = false) {
      const state = this.getPluginSpecState(plugin);
      if (!forceReload) {
        if (state.status === 'loaded') {
          return state.spec;
        }
        if (state.request) {
          return state.request;
        }
      }
      state.status = 'loading';
      state.loadError = null;
      state.request = (async () => {
        try {
          const spec = await external_CoreHome_["AjaxHelper"].fetch({
            method: 'OpenApiDocs.getOpenApiSpec',
            pluginName: plugin,
            format: 'json'
          }, {
            createErrorNotification: false
          });
          state.spec = spec;
          state.status = 'loaded';
          return spec;
        } catch (_unused2) {
          state.spec = null;
          state.status = 'error';
          state.loadError = Object(external_CoreHome_["translate"])('OpenApiDocs_SwaggerPageSpecLoadFailed');
          return null;
        } finally {
          state.request = null;
        }
      })();
      return state.request;
    },
    togglePlugin(plugin) {
      if (this.expandedPluginName === plugin) {
        this.expandedPluginName = null;
        return;
      }
      this.expandedPluginName = plugin;
      const state = this.getPluginSpecState(plugin);
      if (state.status !== 'loaded') {
        this.prefetchPluginSpec(plugin, state.status === 'error');
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=style&index=0&id=d027320c&scoped=true&lang=css
var SwaggerPagevue_type_style_index_0_id_d027320c_scoped_true_lang_css = __webpack_require__("541e");

// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue





SwaggerPagevue_type_script_lang_ts.render = render
SwaggerPagevue_type_script_lang_ts.__scopeId = "data-v-d027320c"

/* harmony default export */ var SwaggerPage = (SwaggerPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=OpenApiDocs.umd.js.map