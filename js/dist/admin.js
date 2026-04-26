/******/ (() => { // webpackBootstrap
/******/ 	// runtime can't be in strict mode because a global variable is assign and maybe created.
/******/ 	var __webpack_modules__ = ({

/***/ "./src/admin/components/MybbToFlarumPage.js"
/*!**************************************************!*\
  !*** ./src/admin/components/MybbToFlarumPage.js ***!
  \**************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ MybbToFlarumPage)
/* harmony export */ });
/* harmony import */ var flarum_admin_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/admin/app */ "flarum/admin/app");
/* harmony import */ var flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_admin_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/admin/components/ExtensionPage */ "flarum/admin/components/ExtensionPage");
/* harmony import */ var flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/components/Switch */ "flarum/common/components/Switch");
/* harmony import */ var flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/common/components/FieldSet */ "flarum/common/components/FieldSet");
/* harmony import */ var flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/utils/Stream */ "flarum/common/utils/Stream");
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var flarum_admin_utils_saveSettings__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! flarum/admin/utils/saveSettings */ "flarum/admin/utils/saveSettings");
/* harmony import */ var flarum_admin_utils_saveSettings__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(flarum_admin_utils_saveSettings__WEBPACK_IMPORTED_MODULE_6__);







class MybbToFlarumPage extends (flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_1___default()) {
  oninit(vnode) {
    super.oninit(vnode);
    this.migrateAvatars = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(false);
    this.migrateSoftThreads = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(false);
    this.migrateSoftPosts = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(false);
    this.migrateAttachments = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(false);
    this.migrateThreadsPosts = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(true);
    this.migrateUsers = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(true);
    this.migrateCategories = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(true);
    this.migrateUserGroups = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_5___default()(true);
  }
  content() {
    const needsPath = this.migrateAvatars() || this.migrateAttachments();
    return m("div", {
      className: "mybbtoflarumPage"
    }, m("div", {
      className: "mybbtoflarumPage-header"
    }, m("div", {
      className: "container"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.description_text'))), m("div", {
      className: "mybbtoflarumPage-content"
    }, m("div", {
      className: "container"
    }, m("form", {
      onsubmit: this.onsubmit.bind(this)
    }, m((flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default()), {
      label: flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.title')
    }, m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateUsers(),
      onchange: value => {
        this.migrateUsers(value);
        if (!value) {
          this.migrateAvatars(false);
        }
      }
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_users_label')), m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateThreadsPosts(),
      onchange: value => {
        this.migrateThreadsPosts(value);
        if (!value) {
          this.migrateAttachments(false);
          this.migrateSoftPosts(false);
          this.migrateSoftThreads(false);
        }
      }
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_threadsPosts_label')), m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateUserGroups(),
      onchange: this.migrateUserGroups
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_userGroups_label')), m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateCategories(),
      onchange: this.migrateCategories
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_categories_label'))), m((flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default()), {
      label: flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.title')
    }, m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateAvatars(),
      onchange: value => {
        this.migrateAvatars(value);
        if (value) {
          this.migrateUsers(true);
        }
      }
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_avatars_label')), m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateAttachments(),
      onchange: value => {
        this.migrateAttachments(value);
        if (value) {
          this.migrateThreadsPosts(true);
        }
      }
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_attachments_label')), m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateSoftThreads(),
      onchange: this.migrateSoftThreads
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_soft_threads_label')), m((flarum_common_components_Switch__WEBPACK_IMPORTED_MODULE_2___default()), {
      state: this.migrateSoftPosts(),
      onchange: this.migrateSoftPosts
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_soft_posts_label'))), m((flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default()), {
      label: flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.title')
    }, m("div", {
      className: "helpText"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.host_label')), m("input", {
      className: "FormControl",
      type: "text",
      bidi: this.setting('mybb_host', '127.0.0.1')
    }), m("div", {
      className: "helpText"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.user_label')), m("input", {
      className: "FormControl",
      type: "text",
      bidi: this.setting('mybb_user')
    }), m("div", {
      className: "helpText"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.password_label')), m("input", {
      className: "FormControl",
      type: "password",
      bidi: this.setting('mybb_password')
    }), m("div", {
      className: "helpText"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.db_label')), m("input", {
      className: "FormControl",
      type: "text",
      bidi: this.setting('mybb_db')
    }), m("div", {
      className: "helpText"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.prefix_label')), m("input", {
      className: "FormControl",
      type: "text",
      bidi: this.setting('mybb_prefix', 'mybb_')
    }), m("div", {
      className: "helpText"
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.path_label')), m("input", {
      className: "FormControl",
      type: "text",
      bidi: this.setting('mybb_path'),
      placeholder: "/path/to/mybb",
      disabled: !needsPath
    })), m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--danger",
      icon: "fas fa-exchange-alt",
      type: "submit",
      loading: this.loading
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('michaelbelgium-mybb-to-flarum.admin.content.convert_button'))))));
  }
  onsubmit(e) {
    e.preventDefault();
    this.loading = true;
    m.redraw();
    const host = this.setting('mybb_host', '127.0.0.1')();
    const user = this.setting('mybb_user')();
    const db = this.setting('mybb_db')();
    const path = this.setting('mybb_path')();
    let fail = false;
    if (this.migrateAvatars() && path === '') {
      alert('When migrating avatars, the mybb path cannot be empty. You need an existing mybb installation.');
      fail = true;
    }
    if (!host || !user || !db) {
      alert('MyBB host, user, and database name cannot be empty.');
      fail = true;
    }
    if (fail) {
      this.loading = false;
      m.redraw();
      return;
    }
    flarum_admin_utils_saveSettings__WEBPACK_IMPORTED_MODULE_6___default()({
      'mybb_host': host,
      'mybb_user': user,
      'mybb_password': this.setting('mybb_password')(),
      'mybb_db': db,
      'mybb_prefix': this.setting('mybb_prefix', 'mybb_')(),
      'mybb_path': path
    }).then(() => {
      console.log('Settings saved successfully, starting migration...');
      flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().request({
        method: 'POST',
        url: flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().forum.attribute('apiUrl') + '/mybb-to-flarum',
        body: {
          avatars: this.migrateAvatars(),
          softposts: this.migrateSoftPosts(),
          softthreads: this.migrateSoftThreads(),
          attachments: this.migrateAttachments(),
          doUsers: this.migrateUsers(),
          doThreadsPosts: this.migrateThreadsPosts(),
          doGroups: this.migrateUserGroups(),
          doCategories: this.migrateCategories()
        }
      }).then(data => {
        alert(data.message);
        this.loading = false;
        m.redraw();
      }).catch(error => {
        alert(error.response?.errors?.[0]?.detail ?? 'An error occurred during migration.');
        this.loading = false;
        m.redraw();
      });
    }).catch(error => {
      alert('Failed to save settings: ' + (error.message ?? 'Unknown error'));
      this.loading = false;
      m.redraw();
    });
  }
}
flarum.reg.add('michaelbelgium-mybb-to-flarum', 'admin/components/MybbToFlarumPage', MybbToFlarumPage);

/***/ },

/***/ "./src/admin/extend.js"
/*!*****************************!*\
  !*** ./src/admin/extend.js ***!
  \*****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/common/extenders */ "flarum/common/extenders");
/* harmony import */ var flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_MybbToFlarumPage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/MybbToFlarumPage */ "./src/admin/components/MybbToFlarumPage.js");


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ([new (flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0___default().Admin)().page(_components_MybbToFlarumPage__WEBPACK_IMPORTED_MODULE_1__["default"])]);

/***/ },

/***/ "./src/admin/index.js"
/*!****************************!*\
  !*** ./src/admin/index.js ***!
  \****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   extend: () => (/* reexport safe */ _extend__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _extend__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./extend */ "./src/admin/extend.js");


/***/ },

/***/ "flarum/admin/app"
/*!******************************************************!*\
  !*** external "flarum.reg.get('core', 'admin/app')" ***!
  \******************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'admin/app');

/***/ },

/***/ "flarum/admin/components/ExtensionPage"
/*!***************************************************************************!*\
  !*** external "flarum.reg.get('core', 'admin/components/ExtensionPage')" ***!
  \***************************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'admin/components/ExtensionPage');

/***/ },

/***/ "flarum/admin/utils/saveSettings"
/*!*********************************************************************!*\
  !*** external "flarum.reg.get('core', 'admin/utils/saveSettings')" ***!
  \*********************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'admin/utils/saveSettings');

/***/ },

/***/ "flarum/common/components/Button"
/*!*********************************************************************!*\
  !*** external "flarum.reg.get('core', 'common/components/Button')" ***!
  \*********************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'common/components/Button');

/***/ },

/***/ "flarum/common/components/FieldSet"
/*!***********************************************************************!*\
  !*** external "flarum.reg.get('core', 'common/components/FieldSet')" ***!
  \***********************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'common/components/FieldSet');

/***/ },

/***/ "flarum/common/components/Switch"
/*!*********************************************************************!*\
  !*** external "flarum.reg.get('core', 'common/components/Switch')" ***!
  \*********************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'common/components/Switch');

/***/ },

/***/ "flarum/common/extenders"
/*!*************************************************************!*\
  !*** external "flarum.reg.get('core', 'common/extenders')" ***!
  \*************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'common/extenders');

/***/ },

/***/ "flarum/common/utils/Stream"
/*!****************************************************************!*\
  !*** external "flarum.reg.get('core', 'common/utils/Stream')" ***!
  \****************************************************************/
(module) {

"use strict";
module.exports = flarum.reg.get('core', 'common/utils/Stream');

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		flarum.reg._webpack_runtimes["michaelbelgium-mybb-to-flarum"] ||= __webpack_require__;// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!******************!*\
  !*** ./admin.js ***!
  \******************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   extend: () => (/* reexport safe */ _src_admin__WEBPACK_IMPORTED_MODULE_0__.extend)
/* harmony export */ });
/* harmony import */ var _src_admin__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/admin */ "./src/admin/index.js");

})();

module.exports = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=admin.js.map