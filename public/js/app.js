(self["webpackChunk"] = self["webpackChunk"] || []).push([["/js/app"],{

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");

__webpack_require__(/*! ./notification */ "./resources/js/notification.js");

__webpack_require__(/*! ./quantity-control */ "./resources/js/quantity-control.js");

__webpack_require__(/*! ./item-description */ "./resources/js/item-description.js");

__webpack_require__(/*! ./cart */ "./resources/js/cart.js");

/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var tiny_slider_src_tiny_slider__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tiny-slider/src/tiny-slider */ "./node_modules/tiny-slider/src/tiny-slider.js");
window._ = __webpack_require__(/*! lodash */ "./node_modules/lodash/lodash.js");
window.axios = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

try {
  window.$ = window.jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
  window.bootstrap = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
} catch (error) {
  console.error(error);
}


window.tinySlider = tiny_slider_src_tiny_slider__WEBPACK_IMPORTED_MODULE_0__.tns;

/***/ }),

/***/ "./resources/js/cart.js":
/*!******************************!*\
  !*** ./resources/js/cart.js ***!
  \******************************/
/***/ (() => {

window.updateCartCount = function () {
  var cartCountNode = $('#cart-count');
  axios.get('/api/cart/count').then(function (res) {
    cartCountNode.html(res.data.count);
  })["catch"](function (error) {
    console.error(error);
  });
};

/***/ }),

/***/ "./resources/js/item-description.js":
/*!******************************************!*\
  !*** ./resources/js/item-description.js ***!
  \******************************************/
/***/ (() => {

$(document).ready(function () {
  var itemDescriptionNode = $('#item-description');

  if (itemDescriptionNode.length) {
    var displayNode = itemDescriptionNode.closest('div').find('p');
    displayNode.html(itemDescriptionNode.val().split('\n').join('<br />'));
  }
});

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
      actionButtons = actionButtons + actionButtonTemplate(action[i].buttonText, action[i].redirectTo);
    }

    toastContainer.append(actionNotificationTemplate(title, message, actionButtons));

    var _toast = new bootstrap.Toast($('.toast').last());

    _toast.show();
  }
};

/***/ }),

/***/ "./resources/js/quantity-control.js":
/*!******************************************!*\
  !*** ./resources/js/quantity-control.js ***!
  \******************************************/
/***/ (() => {

var getAllQuantityControl = function getAllQuantityControl() {
  return $('.quantity-control');
};

var getQuantityNode = function getQuantityNode(quantityControl) {
  return quantityControl.find('.quantity');
};

var getQuantity = function getQuantity(quantityControl) {
  return parseInt(quantityControl.find('.quantity').val());
};

var getMaxQuantity = function getMaxQuantity(quantityControl) {
  return parseInt(quantityControl.find('.quantity-max').val());
};

var getMinQuantity = function getMinQuantity(quantityControl) {
  return parseInt(quantityControl.find('.quantity-min').val());
};

var getIncreaseButton = function getIncreaseButton(quantityControl) {
  return quantityControl.find('.quantity-increase');
};

var getDecreaseButton = function getDecreaseButton(quantityControl) {
  return quantityControl.find('.quantity-decrease');
};

var toggleButtonDisabled = function toggleButtonDisabled(quantityControl) {
  var currentValue = getQuantity(quantityControl);
  var maxValue = getMaxQuantity(quantityControl);
  var minValue = getMinQuantity(quantityControl);
  var increaseButton = getIncreaseButton(quantityControl);
  var decreaseButton = getDecreaseButton(quantityControl);
  increaseButton.attr('disabled', false);
  decreaseButton.attr('disabled', false);

  if (currentValue === minValue) {
    decreaseButton.attr('disabled', true);
  }

  if (currentValue === maxValue) {
    increaseButton.attr('disabled', true);
  }
};

var increaseQuantity = function increaseQuantity(event) {
  var quantityControl = $(event.target).closest('.quantity-control');
  var current = getQuantityNode(quantityControl);
  var currentValue = getQuantity(quantityControl);
  var maxValue = getMaxQuantity(quantityControl);
  var minValue = getMinQuantity(quantityControl);

  if (minValue <= currentValue && currentValue < maxValue) {
    current.val(currentValue + 1);
  }

  toggleButtonDisabled(quantityControl);
};

var decreaseQuantity = function decreaseQuantity(event) {
  var quantityControl = $(event.target).closest('.quantity-control');
  var current = getQuantityNode(quantityControl);
  var currentValue = getQuantity(quantityControl);
  var maxValue = getMaxQuantity(quantityControl);
  var minValue = getMinQuantity(quantityControl);

  if (minValue < currentValue && currentValue <= maxValue) {
    current.val(currentValue - 1);
  }

  toggleButtonDisabled(quantityControl);
};

var updateQuantity = function updateQuantity(event) {
  var quantityControl = $(event.target).closest('.quantity-control');
  var current = getQuantityNode(quantityControl);
  var currentValue = getQuantity(quantityControl);
  var maxValue = getMaxQuantity(quantityControl);
  var minValue = getMinQuantity(quantityControl);

  if (currentValue >= maxValue) {
    current.val(maxValue);
  } else if (currentValue <= minValue) {
    current.val(minValue);
  }

  toggleButtonDisabled(quantityControl);
};

window.useQuantityControl = function () {
  var updateValue = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
  var quantityControls = getAllQuantityControl();

  for (var i = 0; i < quantityControls.length; i++) {
    var quantityControl = quantityControls.eq(i);
    var increaseButton = getIncreaseButton(quantityControl);
    var decreaseButton = getDecreaseButton(quantityControl);
    var quantityNode = getQuantityNode(quantityControl);
    increaseButton.click(increaseQuantity);
    decreaseButton.click(decreaseQuantity);
    quantityNode.change(updateQuantity);
    toggleButtonDisabled(quantityControl);
  }

  if (updateValue) {// TODO update value in cart page
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