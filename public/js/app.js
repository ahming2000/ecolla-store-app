(self["webpackChunk"] = self["webpackChunk"] || []).push([["/js/app"],{

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");

__webpack_require__(/*! ./notification */ "./resources/js/notification.js");

/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

window._ = __webpack_require__(/*! lodash */ "./node_modules/lodash/lodash.js");
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
// import Echo from 'laravel-echo';
// window.Pusher = require('pusher-js');
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

try {
  window.$ = window.jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
  window.bootstrap = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
} catch (error) {
  console.error(error);
}

/***/ }),

/***/ "./resources/js/notification.js":
/*!**************************************!*\
  !*** ./resources/js/notification.js ***!
  \**************************************/
/***/ (() => {

var notificationTemplate = function notificationTemplate(title, message) {
  return "\n    <div class=\"toast\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\" data-bs-delay=\"5000\">\n        <div class=\"toast-header\">\n            <strong class=\"me-auto\">".concat(title, "</strong>\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>\n        </div>\n        <div class=\"toast-body\">\n            ").concat(message, "\n        </div>\n    </div>\n    ");
};

var actionNotificationTemplate = function actionNotificationTemplate(title, message, actionButton) {
  return "\n    <div class=\"toast\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\" data-bs-delay=\"5000\">\n        <div class=\"toast-header\">\n            <strong class=\"me-auto\">".concat(title, "</strong>\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>\n        </div>\n        <div class=\"toast-body\">\n            ").concat(message, "\n            <div class=\"d-flex justify-content-center mt-2 pt-2 border-top\">\n                ").concat(actionButton, "\n            </div>\n        </div>\n    </div>\n    ");
};

var actionButtonTemplate = function actionButtonTemplate(buttonText, redirectTo) {
  return "<a class=\"btn btn-primary btn-sm mx-1\" href=\"".concat(redirectTo, "\">").concat(buttonText, "</a>");
};

window.addNotification = function (title, message) {
  var action = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
  var toastContainer = $('.toast-container');

  if (action.length === 0) {
    toastContainer.append(notificationTemplate(title, message));
    var toast = new bootstrap.Toast($('.toast').last());
    toast.show();
  } else {
    var actionButtons = '';

    for (var i = 0; i < action.length; i++) {
      actionButtons = actionButtons + actionButtonTemplate(action['buttonText'], action['redirectTo']);
    }

    toastContainer.append(actionNotificationTemplate(title, message, actionButtons));

    var _toast = new bootstrap.Toast($('.toast').last());

    _toast.show();
  }
};

/***/ }),

/***/ "./resources/css/app.css":
/*!*******************************!*\
  !*** ./resources/css/app.css ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["css/app","/js/vendor"], () => (__webpack_exec__("./resources/js/app.js"), __webpack_exec__("./resources/css/app.css")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);