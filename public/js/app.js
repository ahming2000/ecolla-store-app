(self["webpackChunk"] = self["webpackChunk"] || []).push([["/js/app"],{

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");

__webpack_require__(/*! ./notification */ "./resources/js/notification.js");

__webpack_require__(/*! ./cart-control */ "./resources/js/cart-control.js");

__webpack_require__(/*! ./quantity-control */ "./resources/js/quantity-control.js");

__webpack_require__(/*! ./item-description */ "./resources/js/item-description.js");

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

/***/ "./resources/js/cart-control.js":
/*!**************************************!*\
  !*** ./resources/js/cart-control.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

var SELF_PICKUP = 0;
var DELIVERY = 1;

window.updateCartCount = function () {
  var cartCountNode = $('#cart-count');
  axios.get('/api/cart/count').then(function (res) {
    cartCountNode.html(res.data.count);
  })["catch"](function (error) {
    console.error(error);
  });
};

var getSubPrice = function getSubPrice(cartItemContainer) {
  var quantity = parseInt(cartItemContainer.find('.quantity-control').find('.quantity').val());
  var price = parseFloat(cartItemContainer.find('.variation-price').val());
  return price * quantity;
};

var getWeight = function getWeight(cartItemContainer) {
  var quantity = parseInt(cartItemContainer.find('.quantity-control').find('.quantity').val());
  var weight = parseFloat(cartItemContainer.find('.variation-weight').val());
  return weight * quantity;
};

var getSubtotal = function getSubtotal() {
  var cartItemContainers = $('.cart-item-container');
  var subtotal = 0.0;

  for (var i = 0; i < cartItemContainers.length; i++) {
    var price = getSubPrice(cartItemContainers.eq(i));
    subtotal += price;
  }

  return subtotal;
};

var getShippingFee = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee(subtotal) {
    var orderMode, data, _data, fee, hasDiscount, discountThreshold;

    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            orderMode = parseInt($('#order-mode-input').val());
            _context.next = 3;
            return axios.get('/api/system-config/shipping-fee-config').then(function (res) {
              data = res.data;
            })["catch"](function (error) {
              console.error(error);
            });

          case 3:
            _data = data, fee = _data.fee, hasDiscount = _data.hasDiscount, discountThreshold = _data.discountThreshold;

            if (!(orderMode === DELIVERY)) {
              _context.next = 14;
              break;
            }

            if (!hasDiscount) {
              _context.next = 13;
              break;
            }

            if (!(subtotal >= discountThreshold)) {
              _context.next = 10;
              break;
            }

            return _context.abrupt("return", 0.0);

          case 10:
            return _context.abrupt("return", fee);

          case 11:
            _context.next = 14;
            break;

          case 13:
            return _context.abrupt("return", fee);

          case 14:
            return _context.abrupt("return", 0.0);

          case 15:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function getShippingFee(_x) {
    return _ref.apply(this, arguments);
  };
}();

window.updateSummary = /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee2() {
  var subtotal, shippingFee, total, subtotalNode, shippingFeeNode, totalNode;
  return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee2$(_context2) {
    while (1) {
      switch (_context2.prev = _context2.next) {
        case 0:
          subtotal = getSubtotal();
          _context2.next = 3;
          return getShippingFee(subtotal);

        case 3:
          shippingFee = _context2.sent;
          total = subtotal + shippingFee;
          subtotalNode = $('#subtotal');
          shippingFeeNode = $('#shipping-fee');
          totalNode = $('#total');
          subtotalNode.html(subtotal.toFixed(2));
          shippingFeeNode.html(shippingFee.toFixed(2));
          totalNode.html(total.toFixed(2));

        case 11:
        case "end":
          return _context2.stop();
      }
    }
  }, _callee2);
}));

window.updateCartDisplayValue = /*#__PURE__*/function () {
  var _ref3 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee3(event) {
    var quantityControl, cartItemContainer, price, weight, weightNode, subPriceNode;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee3$(_context3) {
      while (1) {
        switch (_context3.prev = _context3.next) {
          case 0:
            quantityControl = $(event.target).closest('.quantity-control');
            cartItemContainer = quantityControl.closest('.cart-item-container');
            price = getSubPrice(cartItemContainer);
            weight = getWeight(cartItemContainer);
            weightNode = cartItemContainer.find('.cart-item-weight');
            subPriceNode = cartItemContainer.find('.cart-item-sub-price');
            weightNode.html(weight.toFixed(3) + 'kg');
            subPriceNode.html('RM' + price.toFixed(2));
            _context3.next = 10;
            return updateSummary();

          case 10:
          case "end":
            return _context3.stop();
        }
      }
    }, _callee3);
  }));

  return function (_x2) {
    return _ref3.apply(this, arguments);
  };
}();

window.updateShippingFee = function (event) {
  axios.post('/api/cart/update-order-mode', {
    orderMode: event.target.value
  }).then(function (res) {
    if (res.data.isUpdated) {
      updateSummary();
    }
  })["catch"](function (error) {
    console.error(error);
  });
};

window.resetCart = /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee5() {
  return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee5$(_context5) {
    while (1) {
      switch (_context5.prev = _context5.next) {
        case 0:
          axios.post('/api/cart/reset').then( /*#__PURE__*/function () {
            var _ref5 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee4(res) {
              var cartItemContainers, i;
              return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee4$(_context4) {
                while (1) {
                  switch (_context4.prev = _context4.next) {
                    case 0:
                      if (!res.data.isReset) {
                        _context4.next = 8;
                        break;
                      }

                      cartItemContainers = $('.cart-item-container');

                      for (i = 0; i < cartItemContainers.length; i++) {
                        cartItemContainers.eq(i).remove();
                      }

                      $('#cart-empty-icon').attr('hidden', false);
                      $('#cart-reset-button').attr('hidden', true);
                      updateCartCount();
                      _context4.next = 8;
                      return updateSummary();

                    case 8:
                    case "end":
                      return _context4.stop();
                  }
                }
              }, _callee4);
            }));

            return function (_x3) {
              return _ref5.apply(this, arguments);
            };
          }())["catch"](function (error) {
            console.error(error);
          });

        case 1:
        case "end":
          return _context5.stop();
      }
    }
  }, _callee5);
}));

window.removeCartItem = function (event) {
  var cartItemContainer = $(event.target).closest('.cart-item-container');
  axios.post('/api/cart/remove', {
    barcode: cartItemContainer.attr('id')
  }).then( /*#__PURE__*/function () {
    var _ref6 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee6(res) {
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee6$(_context6) {
        while (1) {
          switch (_context6.prev = _context6.next) {
            case 0:
              if (!res.data.isRemoved) {
                _context6.next = 6;
                break;
              }

              cartItemContainer.remove();

              if ($('.cart-item-container').length === 0) {
                $('#cart-empty-icon').attr('hidden', false);
                $('#cart-reset-button').attr('hidden', true);
              }

              updateCartCount();
              _context6.next = 6;
              return updateSummary();

            case 6:
            case "end":
              return _context6.stop();
          }
        }
      }, _callee6);
    }));

    return function (_x4) {
      return _ref6.apply(this, arguments);
    };
  }())["catch"](function (error) {
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

var saveQuantity = function saveQuantity(event) {
  var quantityControl = $(event.target).closest('.quantity-control');
  var barcode = quantityControl.attr('id');
  var currentValue = getQuantity(quantityControl);
  axios.post('/api/cart/update-quantity', {
    barcode: barcode,
    quantity: currentValue
  })["catch"](function (error) {
    console.error(error);
  });
};

window.useQuantityControl = function () {
  var isCart = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
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

    if (isCart) {
      increaseButton.click(saveQuantity);
      decreaseButton.click(saveQuantity);
      quantityNode.change(saveQuantity);
      increaseButton.click(updateCartDisplayValue);
      decreaseButton.click(updateCartDisplayValue);
      quantityNode.change(updateCartDisplayValue);
    }
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