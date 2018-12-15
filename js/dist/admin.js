module.exports =
/******/ (function(modules) { // webpackBootstrap
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./admin.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./admin.js":
/*!******************!*\
  !*** ./admin.js ***!
  \******************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _src_admin__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/admin */ "./src/admin/index.js");
/* empty/unused harmony star reexport */

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _inheritsLoose; });
function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;
  subClass.__proto__ = superClass;
}

/***/ }),

/***/ "./src/admin/components/MybbToFlarumPage.js":
/*!**************************************************!*\
  !*** ./src/admin/components/MybbToFlarumPage.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return MybbToFlarumPage; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_components_Page__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/components/Page */ "flarum/components/Page");
/* harmony import */ var flarum_components_Page__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Page__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/Switch */ "flarum/components/Switch");
/* harmony import */ var flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/components/Button */ "flarum/components/Button");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_components_FieldSet__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/components/FieldSet */ "flarum/components/FieldSet");
/* harmony import */ var flarum_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_components_FieldSet__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_utils_saveSettings__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/utils/saveSettings */ "flarum/utils/saveSettings");
/* harmony import */ var flarum_utils_saveSettings__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_utils_saveSettings__WEBPACK_IMPORTED_MODULE_5__);







var MybbToFlarumPage =
/*#__PURE__*/
function (_Page) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(MybbToFlarumPage, _Page);

  function MybbToFlarumPage() {
    return _Page.apply(this, arguments) || this;
  }

  var _proto = MybbToFlarumPage.prototype;

  _proto.init = function init() {
    _Page.prototype.init.call(this);

    this.migrateAvatars = m.prop(false);
    this.migrateSoftThreads = m.prop(false);
    this.migrateSoftPosts = m.prop(false);
    this.migrateThreadsPosts = m.prop(true);
    this.migrateUsers = m.prop(true);
    this.migrateCategories = m.prop(true);
    this.migrateUserGroups = m.prop(true);
    this.mybb = {
      host: m.prop(typeof app.data.settings.mybb_host === 'undefined' ? '127.0.0.1' : app.data.settings.mybb_host),
      user: m.prop(typeof app.data.settings.mybb_user === 'undefined' ? '' : app.data.settings.mybb_user),
      db: m.prop(typeof app.data.settings.mybb_db === 'undefined' ? '' : app.data.settings.mybb_db),
      prefix: m.prop(typeof app.data.settings.mybb_prefix === 'undefined' ? 'mybb_' : app.data.settings.mybb_prefix),
      password: m.prop(typeof app.data.settings.mybb_password === 'undefined' ? '' : app.data.settings.mybb_password),
      mybbPath: m.prop(typeof app.data.settings.mybb_path === 'undefined' ? '' : app.data.settings.mybb_path)
    };
  };

  _proto.view = function view() {
    var _this = this;

    return m("div", {
      className: "mybbtoflarumPage"
    }, m("div", {
      className: "mybbtoflarumPage-header"
    }, m("div", {
      className: "container"
    }, app.translator.trans('mybbtoflarum.admin.page.text'))), m("div", {
      className: "mybbtoflarumPage-content"
    }, m("div", {
      className: "container"
    }, m("form", {
      onsubmit: this.onsubmit.bind(this)
    }, flarum_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default.a.component({
      label: app.translator.trans('mybbtoflarum.admin.page.form.general.title'),
      children: [flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateAvatars(),
        onchange: function onchange(value) {
          _this.migrateAvatars(value);

          if (value) {
            $("input[name=mybbPath]").show();
            $("#mybbPath_help").show();
          } else {
            $("#mybbPath_help").hide();
            $("input[name=mybbPath]").hide();
          }
        },
        children: app.translator.trans('mybbtoflarum.admin.page.form.general.migrateAvatars')
      }), flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateSoftThreads(),
        onchange: this.migrateSoftThreads,
        children: app.translator.trans('mybbtoflarum.admin.page.form.general.migrateSoftThreads')
      }), flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateSoftPosts(),
        onchange: this.migrateSoftPosts,
        children: app.translator.trans('mybbtoflarum.admin.page.form.general.migrateSoftPosts')
      })]
    }), flarum_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default.a.component({
      label: app.translator.trans('mybbtoflarum.admin.page.form.mybb.title'),
      children: [m("div", {
        className: "helpText"
      }, app.translator.trans('mybbtoflarum.admin.page.form.mybb.host')), m("input", {
        className: "FormControl",
        type: "text",
        bidi: this.mybb.host,
        value: this.mybb.host()
      }), m("div", {
        className: "helpText"
      }, app.translator.trans('mybbtoflarum.admin.page.form.mybb.user')), m("input", {
        className: "FormControl",
        type: "text",
        bidi: this.mybb.user
      }), m("div", {
        className: "helpText"
      }, app.translator.trans('mybbtoflarum.admin.page.form.mybb.password')), m("input", {
        className: "FormControl",
        type: "password",
        bidi: this.mybb.password
      }), m("div", {
        className: "helpText"
      }, app.translator.trans('mybbtoflarum.admin.page.form.mybb.db')), m("input", {
        className: "FormControl",
        type: "text",
        bidi: this.mybb.db
      }), m("div", {
        className: "helpText"
      }, app.translator.trans('mybbtoflarum.admin.page.form.mybb.prefix')), m("input", {
        className: "FormControl",
        type: "text",
        bidi: this.mybb.prefix,
        value: this.mybb.prefix()
      }), m("div", {
        className: "helpText",
        id: "mybbPath_help",
        style: "display: none;"
      }, app.translator.trans('mybbtoflarum.admin.page.form.mybb.mybbPath')), m("input", {
        className: "FormControl",
        type: "text",
        bidi: this.mybb.mybbPath,
        name: "mybbPath",
        style: "display: none;",
        placeholder: "/path/to/mybb"
      })]
    }), flarum_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default.a.component({
      label: app.translator.trans('mybbtoflarum.admin.page.form.options.title'),
      children: [flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateUsers(),
        onchange: this.migrateUsers,
        children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateUsers')
      }), flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateThreadsPosts(),
        onchange: this.migrateThreadsPosts,
        children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateThreadsPosts')
      }), flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateUserGroups(),
        onchange: this.migrateUserGroups,
        children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateUserGroups')
      }), flarum_components_Switch__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        state: this.migrateCategories(),
        onchange: this.migrateCategories,
        children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateCategories')
      })]
    }), flarum_components_Button__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      className: 'Button Button--danger',
      icon: 'fas fa-exchange-alt',
      type: 'submit',
      children: app.translator.trans('mybbtoflarum.admin.page.btnConvert'),
      loading: this.loading
    })))));
  };

  _proto.onsubmit = function onsubmit(e) {
    var _this2 = this;

    e.preventDefault();
    this.loading = true;
    var fail = false;

    if (this.migrateAvatars() && this.mybb.mybbPath() === '') {
      alert('When migrating avatars, the mybb path can not be empty. You need an exisitng mybb installation.');
      fail = true;
    }

    Object.keys(this.mybb).forEach(function (key) {
      if (key !== 'mybbPath' && _this2.mybb[key]() === '') {
        alert('Mybb: ' + key + ' can not be empty');
        fail = true;
      }
    });

    if (fail) {
      this.loading = false;
      return;
    }

    flarum_utils_saveSettings__WEBPACK_IMPORTED_MODULE_5___default()({
      'mybb_host': this.mybb.host(),
      'mybb_user': this.mybb.user(),
      'mybb_password': this.mybb.password(),
      'mybb_db': this.mybb.db(),
      'mybb_prefix': this.mybb.prefix(),
      'mybb_path': this.mybb.mybbPath()
    }).then(function () {
      app.request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/mybb-to-flarum',
        data: {
          avatars: _this2.migrateAvatars(),
          softposts: _this2.migrateSoftPosts(),
          softthreads: _this2.migrateSoftThreads()
        }
      }).then(function (data) {
        return console.log(data);
      });
    });
    this.loading = false;
  };

  return MybbToFlarumPage;
}(flarum_components_Page__WEBPACK_IMPORTED_MODULE_1___default.a);



/***/ }),

/***/ "./src/admin/index.js":
/*!****************************!*\
  !*** ./src/admin/index.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/extend */ "flarum/extend");
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_extend__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/app */ "flarum/app");
/* harmony import */ var flarum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_AdminNav__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/AdminNav */ "flarum/components/AdminNav");
/* harmony import */ var flarum_components_AdminNav__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_AdminNav__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_components_AdminLinkButton__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/components/AdminLinkButton */ "flarum/components/AdminLinkButton");
/* harmony import */ var flarum_components_AdminLinkButton__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_components_AdminLinkButton__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _components_MybbToFlarumPage__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/MybbToFlarumPage */ "./src/admin/components/MybbToFlarumPage.js");





flarum_app__WEBPACK_IMPORTED_MODULE_1___default.a.initializers.add('michaelbelgium-mybb-to-flarum', function () {
  flarum_app__WEBPACK_IMPORTED_MODULE_1___default.a.routes.mybbtoflarum = {
    path: '/mybb-to-flarum',
    component: _components_MybbToFlarumPage__WEBPACK_IMPORTED_MODULE_4__["default"].component()
  };
  Object(flarum_extend__WEBPACK_IMPORTED_MODULE_0__["extend"])(flarum_components_AdminNav__WEBPACK_IMPORTED_MODULE_2___default.a.prototype, 'items', function (items) {
    items.add('pages', flarum_components_AdminLinkButton__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      href: flarum_app__WEBPACK_IMPORTED_MODULE_1___default.a.route('mybbtoflarum'),
      icon: 'fas fa-exchange-alt',
      children: flarum_app__WEBPACK_IMPORTED_MODULE_1___default.a.translator.trans('mybbtoflarum.admin.nav.title'),
      description: flarum_app__WEBPACK_IMPORTED_MODULE_1___default.a.translator.trans('mybbtoflarum.admin.nav.description')
    }));
  });
});

/***/ }),

/***/ "flarum/app":
/*!********************************************!*\
  !*** external "flarum.core.compat['app']" ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['app'];

/***/ }),

/***/ "flarum/components/AdminLinkButton":
/*!*******************************************************************!*\
  !*** external "flarum.core.compat['components/AdminLinkButton']" ***!
  \*******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/AdminLinkButton'];

/***/ }),

/***/ "flarum/components/AdminNav":
/*!************************************************************!*\
  !*** external "flarum.core.compat['components/AdminNav']" ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/AdminNav'];

/***/ }),

/***/ "flarum/components/Button":
/*!**********************************************************!*\
  !*** external "flarum.core.compat['components/Button']" ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Button'];

/***/ }),

/***/ "flarum/components/FieldSet":
/*!************************************************************!*\
  !*** external "flarum.core.compat['components/FieldSet']" ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/FieldSet'];

/***/ }),

/***/ "flarum/components/Page":
/*!********************************************************!*\
  !*** external "flarum.core.compat['components/Page']" ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Page'];

/***/ }),

/***/ "flarum/components/Switch":
/*!**********************************************************!*\
  !*** external "flarum.core.compat['components/Switch']" ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Switch'];

/***/ }),

/***/ "flarum/extend":
/*!***********************************************!*\
  !*** external "flarum.core.compat['extend']" ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['extend'];

/***/ }),

/***/ "flarum/utils/saveSettings":
/*!***********************************************************!*\
  !*** external "flarum.core.compat['utils/saveSettings']" ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['utils/saveSettings'];

/***/ })

/******/ });
//# sourceMappingURL=admin.js.map