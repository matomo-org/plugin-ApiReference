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

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "321e":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "374f":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerPage_vue_vue_type_style_index_0_id_b5bf21e2_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("321e");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerPage_vue_vue_type_style_index_0_id_b5bf21e2_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerPage_vue_vue_type_style_index_0_id_b5bf21e2_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "dfa1":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "f16d":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerUiPanel_vue_vue_type_style_index_0_id_4ab9953f_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("dfa1");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerUiPanel_vue_vue_type_style_index_0_id_4ab9953f_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_SwaggerUiPanel_vue_vue_type_style_index_0_id_4ab9953f_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=template&id=b5bf21e2&scoped=true

const _withScopeId = n => (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["pushScopeId"])("data-v-b5bf21e2"), n = n(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["popScopeId"])(), n);
const _hoisted_1 = {
  class: "page"
};
const _hoisted_2 = {
  key: 0,
  class: "loadingText"
};
const _hoisted_3 = {
  key: 2
};
const _hoisted_4 = {
  key: 3
};
const _hoisted_5 = {
  class: "searchBar"
};
const _hoisted_6 = /*#__PURE__*/_withScopeId(() => /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "searchIcon icon-search"
}, null, -1));
const _hoisted_7 = ["value", "placeholder"];
const _hoisted_8 = {
  key: 0,
  class: "emptyText"
};
const _hoisted_9 = {
  key: 1,
  class: "pluginList"
};
const _hoisted_10 = ["aria-expanded", "onClick"];
const _hoisted_11 = {
  class: "pluginHeader"
};
const _hoisted_12 = {
  class: "pluginName"
};
const _hoisted_13 = {
  key: 0,
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
    }, null, 8, ["loading"]), _ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPageLoading')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
      key: 1,
      severity: "danger"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadError), 1)]),
      _: 1
    })) : !_ctx.isLoading && _ctx.plugins.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPagePluginEmpty')), 1)) : !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      value: _ctx.searchTerm,
      type: "text",
      class: "searchInput browser-default",
      placeholder: _ctx.translate('OpenApiDocs_SwaggerPageSearchPlaceholder'),
      onInput: _cache[0] || (_cache[0] = (...args) => _ctx.onSearchInput && _ctx.onSearchInput(...args))
    }, null, 40, _hoisted_7)]), _ctx.filteredPlugins.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('OpenApiDocs_SwaggerPageSearchNoResults')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_9, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filteredPlugins, plugin => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        key: plugin,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['card', 'pluginCard', {
          'pluginCard--expanded': _ctx.expandedPluginName === plugin
        }])
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "pluginToggle",
        "aria-expanded": _ctx.expandedPluginName === plugin ? 'true' : 'false',
        onClick: $event => _ctx.togglePlugin(plugin)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['pluginChevron', _ctx.expandedPluginName === plugin ? 'icon-chevron-down' : 'icon-chevron-right'])
      }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin), 1)])], 8, _hoisted_10), _ctx.expandedPluginName === plugin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SwaggerUiPanel, {
        plugin: plugin
      }, null, 8, ["plugin"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
    }), 128))]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })]);
}
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=template&id=b5bf21e2&scoped=true

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=template&id=4ab9953f&scoped=true

const SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_withScopeId = n => (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["pushScopeId"])("data-v-4ab9953f"), n = n(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["popScopeId"])(), n);
const SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_hoisted_1 = {
  key: 0,
  class: "swaggerLoader"
};
const SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_hoisted_2 = ["id"];
function SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [!_ctx.isReady && !_ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: true
  })])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadError), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    id: _ctx.swaggerContainerId,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['swaggerMount', {
      'swaggerMount--ready': _ctx.isReady
    }])
  }, null, 10, SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=template&id=4ab9953f&scoped=true

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=script&lang=ts


const activeCopySuccessStateKey = '__matomoActiveCopySuccessState';
const summaryPathClickHandlerAttachedKey = '__matomoSummaryPathClickHandlerAttached';
/* harmony default export */ var SwaggerUiPanelvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"]
  },
  props: {
    plugin: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      isReady: false,
      loadError: null
    };
  },
  computed: {
    swaggerContainerId() {
      return `swagger-ui-${this.plugin}`;
    }
  },
  mounted() {
    this.renderSwaggerUi();
  },
  beforeUnmount() {
    const container = document.getElementById(this.swaggerContainerId);
    if (!container) {
      return;
    }
    this.clearCopySuccessState(container);
  },
  methods: {
    getSwaggerSpecUrl() {
      const params = new URLSearchParams({
        module: 'API',
        method: 'OpenApiDocs.getGeneratedOpenApiSpec',
        plugin: this.plugin,
        format: 'JSON'
      });
      return `index.php?${params.toString()}`;
    },
    shortenSummaryPaths(swaggerRoot) {
      const summaryPrefix = '/index.php?module=API&method=';
      const summaryPaths = swaggerRoot.querySelectorAll('.opblock-summary-path');
      Array.prototype.forEach.call(summaryPaths, element => {
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
      Array.prototype.forEach.call(tagSections, tagSection => {
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
      Array.prototype.forEach.call(copyControls, element => {
        if (element.classList.contains('matomo-copy-success')) {
          return;
        }

        element.innerHTML = '<span class="icon-content-copy" aria-hidden="true"></span>';
      });
    },
    normalizeSwaggerUi(swaggerRoot) {
      if (!swaggerRoot) {
        return;
      }
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
    getOriginalCopyControlMarkup(swaggerRoot, control) {
      const state = swaggerRoot[activeCopySuccessStateKey];
      if (state && state.element === control) {
        return state.originalInnerHTML;
      }
      return control.innerHTML;
    },
    clearCopySuccessState(swaggerRoot) {
      const state = swaggerRoot[activeCopySuccessStateKey];
      if (!state) {
        return;
      }
      window.clearTimeout(state.resetTimeoutId);
      state.element.innerHTML = state.originalInnerHTML;
      state.element.classList.remove('matomo-copy-success');
      state.element.classList.remove('matomo-copy-reset');
      window.requestAnimationFrame(() => {
        state.element.classList.add('matomo-copy-reset');
      });
      swaggerRoot[activeCopySuccessStateKey] = null;
    },
    showCopySuccessState(swaggerRoot, control, originalInnerHTML) {
      this.clearCopySuccessState(swaggerRoot);
      control.innerHTML = '<i class="icon-ok matomo-copy-success-icon" aria-hidden="true"></i>';
      control.classList.remove('matomo-copy-reset');
      control.classList.add('matomo-copy-success');
      swaggerRoot[activeCopySuccessStateKey] = {
        element: control,
        originalInnerHTML,
        resetTimeoutId: window.setTimeout(() => {
          var _swaggerRoot$activeCo;
          if (((_swaggerRoot$activeCo = swaggerRoot[activeCopySuccessStateKey]) === null || _swaggerRoot$activeCo === void 0 ? void 0 : _swaggerRoot$activeCo.element) === control) {
            this.clearCopySuccessState(swaggerRoot);
          }
        }, 3000)
      };
    },
    disableAuthorizePlugin() {
      return {
        wrapComponents: {
          authorizeBtn: () => () => null
        }
      };
    },
    attachSwaggerInteractionHandlers(swaggerRoot) {
      const interactiveSwaggerSelector = '.opblock-tag, .opblock-summary, .expand-operation, .opblock-summary-control';
      if (!swaggerRoot || swaggerRoot[summaryPathClickHandlerAttachedKey]) {
        return;
      }
      swaggerRoot[summaryPathClickHandlerAttachedKey] = true;
      swaggerRoot.addEventListener('click', event => {
        const target = event.target;
        const flatTagHeader = this.getFlatTagHeader(target);
        if (flatTagHeader) {
          if (target !== null && target !== void 0 && target.closest('a')) {
            event.stopPropagation();
          } else {
            event.preventDefault();
            event.stopPropagation();
          }
          return;
        }
        const summaryPathCopyControl = this.getSummaryPathCopyControl(target);
        if (summaryPathCopyControl) {
          const originalInnerHTML = this.getOriginalCopyControlMarkup(swaggerRoot, summaryPathCopyControl);
          window.setTimeout(() => {
            if (summaryPathCopyControl.isConnected) {
              this.showCopySuccessState(swaggerRoot, summaryPathCopyControl, originalInnerHTML);
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
    renderSwaggerUi() {
      var _swaggerUiBundle$pres;
      const swaggerUiBundle = window.SwaggerUIBundle;
      const container = document.getElementById(this.swaggerContainerId);
      this.isReady = false;
      this.loadError = null;
      if (!swaggerUiBundle || !container) {
        this.isReady = true;
        this.loadError = Object(external_CoreHome_["translate"])('OpenApiDocs_SwaggerPageSpecLoadFailed');
        return;
      }
      container.innerHTML = '';
      swaggerUiBundle({
        dom_id: `#${this.swaggerContainerId}`,
        url: this.getSwaggerSpecUrl(),
        deepLinking: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: -1,
        layout: 'BaseLayout',
        tagsSorter: 'alpha',
        presets: (_swaggerUiBundle$pres = swaggerUiBundle.presets) !== null && _swaggerUiBundle$pres !== void 0 && _swaggerUiBundle$pres.apis ? [swaggerUiBundle.presets.apis] : [],
        plugins: [this.disableAuthorizePlugin],
        requestInterceptor: request => {
          if (request.loadSpec && request.url.includes('OpenApiDocs.getGeneratedOpenApiSpec')) {
            return Object.assign(Object.assign({}, request), {}, {
              url: this.getSwaggerSpecUrl()
            });
          }
          return request;
        },
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
 
// EXTERNAL MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue?vue&type=style&index=0&id=4ab9953f&scoped=true&lang=css
var SwaggerUiPanelvue_type_style_index_0_id_4ab9953f_scoped_true_lang_css = __webpack_require__("f16d");

// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerUiPanel.vue





SwaggerUiPanelvue_type_script_lang_ts.render = SwaggerUiPanelvue_type_template_id_4ab9953f_scoped_true_render
SwaggerUiPanelvue_type_script_lang_ts.__scopeId = "data-v-4ab9953f"

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
      searchTerm: ''
    };
  },
  created() {
    this.fetchPlugins();
  },
  methods: {
    fetchPlugins() {
      this.expandedPluginName = null;
      this.isLoading = true;
      this.loadError = null;
      this.plugins = [];
      this.searchTerm = '';
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'OpenApiDocs.getPluginWhitelist'
      }, {
        createErrorNotification: false
      }).then(plugins => {
        this.plugins = [...plugins].sort((left, right) => left.localeCompare(right));
      }).catch(() => {
        this.loadError = Object(external_CoreHome_["translate"])('OpenApiDocs_SwaggerPageRequestFailed');
      }).finally(() => {
        this.isLoading = false;
      });
    },
    onSearchInput(event) {
      const target = event.target;
      this.onSearchTermChange((target === null || target === void 0 ? void 0 : target.value) || '');
    },
    onSearchTermChange(value) {
      this.searchTerm = value;
      if (this.expandedPluginName && !this.filteredPlugins.includes(this.expandedPluginName)) {
        this.expandedPluginName = null;
      }
    },
    togglePlugin(plugin) {
      if (this.expandedPluginName === plugin) {
        this.expandedPluginName = null;
        return;
      }
      this.expandedPluginName = plugin;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue?vue&type=style&index=0&id=b5bf21e2&scoped=true&lang=css
var SwaggerPagevue_type_style_index_0_id_b5bf21e2_scoped_true_lang_css = __webpack_require__("374f");

// CONCATENATED MODULE: ./plugins/OpenApiDocs/vue/src/SwaggerPage/SwaggerPage.vue





SwaggerPagevue_type_script_lang_ts.render = render
SwaggerPagevue_type_script_lang_ts.__scopeId = "data-v-b5bf21e2"

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
