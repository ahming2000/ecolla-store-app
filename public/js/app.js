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

__webpack_require__(/*! ./payment-method */ "./resources/js/payment-method.js");

__webpack_require__(/*! ./verify-image */ "./resources/js/verify-image.js");

__webpack_require__(/*! ./loading-button */ "./resources/js/loading-button.js");

__webpack_require__(/*! ./element-modal-control */ "./resources/js/element-modal-control.js");

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

  __webpack_require__(/*! ./vender/jquery.ba-throttle-debounce */ "./resources/js/vender/jquery.ba-throttle-debounce.js");

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
/***/ (() => {

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return generator._invoke = function (innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; }(innerFn, self, context), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; this._invoke = function (method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); }; } function maybeInvokeDelegate(delegate, context) { var method = delegate.iterator[context.method]; if (undefined === method) { if (context.delegate = null, "throw" === context.method) { if (delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method)) return ContinueSentinel; context.method = "throw", context.arg = new TypeError("The iterator does not provide a 'throw' method"); } return ContinueSentinel; } var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) { if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; } return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, define(Gp, "constructor", GeneratorFunctionPrototype), define(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (object) { var keys = []; for (var key in object) { keys.push(key); } return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) { "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); } }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }

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
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(subtotal) {
    var orderMode, data, _data, fee, hasDiscount, discountThreshold;

    return _regeneratorRuntime().wrap(function _callee$(_context) {
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

window.updateSummary = /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
  var subtotal, shippingFee, total, subtotalNode, shippingFeeNode, totalNode;
  return _regeneratorRuntime().wrap(function _callee2$(_context2) {
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
  var _ref3 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee3(event) {
    var quantityControl, cartItemContainer, price, weight, weightNode, subPriceNode;
    return _regeneratorRuntime().wrap(function _callee3$(_context3) {
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

window.resetCart = /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
  return _regeneratorRuntime().wrap(function _callee5$(_context5) {
    while (1) {
      switch (_context5.prev = _context5.next) {
        case 0:
          axios.post('/api/cart/reset').then( /*#__PURE__*/function () {
            var _ref5 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee4(res) {
              var cartItemContainers, i;
              return _regeneratorRuntime().wrap(function _callee4$(_context4) {
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
    var _ref6 = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee6(res) {
      return _regeneratorRuntime().wrap(function _callee6$(_context6) {
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

/***/ "./resources/js/element-modal-control.js":
/*!***********************************************!*\
  !*** ./resources/js/element-modal-control.js ***!
  \***********************************************/
/***/ (() => {

var getPillTemplate = function getPillTemplate(data) {
  return "\n            <span class=\"badge rounded-pill mb-1 me-1\" onclick=\"openEditElementModal(event)\"\n                  data-element='".concat(JSON.stringify(data), "' id=\"").concat(data.id, "\">\n                <i class=\"bi bi-pencil-square\"></i> ").concat(data.name, "</span>\n            </span>\n            ");
};

window.openCreateElementModal = function (event) {
  var type = $(event.target).closest('.element-parent').data('element-type');
  var modalNode = $('#create-element-modal'); // Set create modal title

  if (type === 'origin') {
    modalNode.find('.modal-title').html('创建新出产地');
  } else if (type === 'category') {
    modalNode.find('.modal-title').html('创建新类别');
  } else {
    console.error('Fail to open create element modal!');
    return;
  } // Set type for modal to recognize the type of the creation


  modalNode.attr('data-element-type', type);
  var modal = new bootstrap.Modal(modalNode);
  modal.show();
};

window.createItemSettingElement = function (event) {
  var button = $(event.target);
  var modalNode = button.closest('#create-element-modal');
  var modal = bootstrap.Modal.getInstance(modalNode);
  var type = modalNode.attr('data-element-type');
  var nameNode = $('#name-input-create');
  var nameEnNode = $('#name-en-input-create'); // "required" verification

  if (nameNode.val() === '' || nameEnNode.val() === '') {
    if (nameNode.val() === '') {
      nameNode.addClass('is-invalid');
    } else {
      nameNode.removeClass('is-invalid');
    }

    if (nameEnNode.val() === '') {
      nameEnNode.addClass('is-invalid');
    } else {
      nameEnNode.removeClass('is-invalid');
    }
  } // Proceed to create the element
  else {
    startLoading(button);
    axios.post('/api/setting/' + type, {
      name: nameNode.val(),
      name_en: nameEnNode.val()
    }).then(function (res) {
      if (res.data.isCreated) {
        addNotification('通知', '创建成功！'); // Add pill display

        $('.element-parent').find('.pill-container-type-' + type).append(getPillTemplate(res.data.model)); // Reset form

        nameNode.val('');
        nameEnNode.val('');
        modal.hide();
      } else {
        console.log('Create ' + type + ' failed!');
      }

      stopLoading(button);
    })["catch"](function (error) {
      console.error(error);
      stopLoading(button);
    });
  }
};

window.openEditElementModal = function (event) {
  var type = $(event.target).closest('.element-parent').data('element-type');
  var modalNode = $('#edit-element-modal'); // Since the onclick event required data from "span" tag,
  // make sure both clicked frame can retrieve the data

  var data = $(event.target).data('element') || $(event.target).closest('span.badge').data('element'); // Set edit modal title

  if (type === 'origin') {
    modalNode.find('.modal-title').html('编辑出产地 "' + data.name + '"');
  } else if (type === 'category') {
    modalNode.find('.modal-title').html('编辑类别 "' + data.name + '"');
  } else {
    console.error('Fail to open edit element modal!');
    return;
  } // Set selected element properties


  $('#name-input-edit').val(data.name);
  $('#name-en-input-edit').val(data.name_en); // Set data for delete modal and update form submission to use

  modalNode.attr('data-element-type', type);
  modalNode.attr('data-element', JSON.stringify(data));
  var modal = new bootstrap.Modal(modalNode);
  modal.show();
};

window.updateItemSettingElement = function (event) {
  var button = $(event.target);
  var modalNode = button.closest('#edit-element-modal');
  var modal = bootstrap.Modal.getInstance(modalNode);
  var type = modalNode.data('element-type');
  var data = modalNode.data('element');
  var nameNode = $('#name-input-edit');
  var nameEnNode = $('#name-en-input-edit'); // "required" verification

  if (nameNode.val() === '' || nameEnNode.val() === '') {
    if (nameNode.val() === '') {
      nameNode.addClass('is-invalid');
    } else {
      nameNode.removeClass('is-invalid');
    }

    if (nameEnNode.val() === '') {
      nameEnNode.addClass('is-invalid');
    } else {
      nameEnNode.removeClass('is-invalid');
    }
  } // Proceed to update the element
  else {
    startLoading(button);
    axios.patch('/api/setting/' + type + '/' + data.id, {
      name: nameNode.val(),
      name_en: nameEnNode.val()
    }).then(function (res) {
      if (res.data.isUpdated) {
        addNotification('通知', '更新成功！'); // Update current pill display and current data saved on "span" tag

        var elementNode = $('#' + type + '-' + data.id);
        elementNode.find('.element-name').html(nameNode.val());
        elementNode.attr('data-element', JSON.stringify(res.data.model));
        modal.hide();
      } else {
        console.log('Update ' + type + ' failed!');
      }

      stopLoading(button);
    })["catch"](function (error) {
      console.error(error);
      stopLoading(button);
    });
  }
};

window.openDeleteElementModal = function (event) {
  var modalNode = $('#delete-element-modal');
  var data = JSON.parse($(event.target).closest('#edit-element-modal').attr('data-element'));
  modalNode.find('.element-name').html(data.name);
  var modal = new bootstrap.Modal(modalNode);
  modal.show();
};

window.deleteItemSettingElement = function (event) {
  var editModalNode = $('#edit-element-modal');
  var deleteModalNode = $('#delete-element-modal');
  var data = JSON.parse(editModalNode.attr('data-element'));
  var type = editModalNode.attr('data-element-type');
  axios["delete"]('/api/setting/' + type + '/' + data.id).then(function (res) {
    if (res.data.isDeleted) {
      addNotification('通知', '删除 "' + data.name + '" 成功！');
      var elementNode = $('#' + type + '-' + data.id);
      elementNode.remove();
      var modal = bootstrap.Modal.getInstance(deleteModalNode);
      modal.hide();
      modal = bootstrap.Modal.getInstance(editModalNode);
      modal.hide();
    }
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

/***/ "./resources/js/loading-button.js":
/*!****************************************!*\
  !*** ./resources/js/loading-button.js ***!
  \****************************************/
/***/ (() => {

var getLoadingAnimationTemplate = function getLoadingAnimationTemplate() {
  return "<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span>";
};

window.startLoading = function (button) {
  button.attr('disabled', true);
  button.find('i').attr('hidden', true);
  button.find('i').before(getLoadingAnimationTemplate());
};

window.stopLoading = function (button) {
  button.find('span.spinner-border').remove();
  button.attr('disabled', false);
  button.find('i').attr('hidden', false);
};

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

/***/ "./resources/js/payment-method.js":
/*!****************************************!*\
  !*** ./resources/js/payment-method.js ***!
  \****************************************/
/***/ (() => {

window.selectPayment = function (event) {
  var payments = $('.payment-method-selection-container').find('img');

  for (var i = 0; i < payments.length; i++) {
    payments.removeClass('payment-selected');
  }

  $(event.target).addClass('payment-selected');
  $('#payment-method-input').val($(event.target).data('id'));
};

window.openPaymentQRCode = function (event) {
  event.preventDefault();
  var qrCode = $('.payment-method-selection-container').find('.payment-selected').data('qrcode');
  $('#qr-code-image').attr('src', qrCode);
  var QRCodeModal = new bootstrap.Modal($('#qr-code-modal'));
  QRCodeModal.show();
};

/***/ }),

/***/ "./resources/js/quantity-control.js":
/*!******************************************!*\
  !*** ./resources/js/quantity-control.js ***!
  \******************************************/
/***/ (() => {

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return generator._invoke = function (innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; }(innerFn, self, context), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; this._invoke = function (method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); }; } function maybeInvokeDelegate(delegate, context) { var method = delegate.iterator[context.method]; if (undefined === method) { if (context.delegate = null, "throw" === context.method) { if (delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method)) return ContinueSentinel; context.method = "throw", context.arg = new TypeError("The iterator does not provide a 'throw' method"); } return ContinueSentinel; } var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) { if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; } return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, define(Gp, "constructor", GeneratorFunctionPrototype), define(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (object) { var keys = []; for (var key in object) { keys.push(key); } return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) { "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); } }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

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
  return parseInt(quantityControl.find('.quantity').attr('max'));
};

var getMinQuantity = function getMinQuantity(quantityControl) {
  return parseInt(quantityControl.find('.quantity').attr('min'));
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

var saveQuantity = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(event) {
    var quantityControl, barcode, currentValue;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            quantityControl = $(event.target).closest('.quantity-control');
            barcode = quantityControl.attr('id');
            currentValue = getQuantity(quantityControl);
            _context.next = 5;
            return axios.post('/api/cart/update-quantity', {
              barcode: barcode,
              quantity: currentValue
            })["catch"](function (error) {
              console.error(error);
            });

          case 5:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function saveQuantity(_x) {
    return _ref.apply(this, arguments);
  };
}();

window.useQuantityControl = /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
  var isCart,
      quantityControls,
      i,
      quantityControl,
      increaseButton,
      decreaseButton,
      quantityNode,
      _args2 = arguments;
  return _regeneratorRuntime().wrap(function _callee2$(_context2) {
    while (1) {
      switch (_context2.prev = _context2.next) {
        case 0:
          isCart = _args2.length > 0 && _args2[0] !== undefined ? _args2[0] : false;
          quantityControls = getAllQuantityControl();
          i = 0;

        case 3:
          if (!(i < quantityControls.length)) {
            _context2.next = 32;
            break;
          }

          quantityControl = quantityControls.eq(i);
          increaseButton = getIncreaseButton(quantityControl);
          decreaseButton = getDecreaseButton(quantityControl);
          quantityNode = getQuantityNode(quantityControl);
          increaseButton.click(increaseQuantity);
          decreaseButton.click(decreaseQuantity);
          quantityNode.change(updateQuantity);
          toggleButtonDisabled(quantityControl);

          if (!isCart) {
            _context2.next = 29;
            break;
          }

          _context2.t0 = increaseButton;
          _context2.t1 = $;
          _context2.next = 17;
          return saveQuantity;

        case 17:
          _context2.t2 = _context2.sent;
          _context2.t3 = _context2.t1.debounce.call(_context2.t1, 500, false, _context2.t2);

          _context2.t0.click.call(_context2.t0, _context2.t3);

          _context2.t4 = decreaseButton;
          _context2.t5 = $;
          _context2.next = 24;
          return saveQuantity;

        case 24:
          _context2.t6 = _context2.sent;
          _context2.t7 = _context2.t5.debounce.call(_context2.t5, 500, false, _context2.t6);

          _context2.t4.click.call(_context2.t4, _context2.t7);

          increaseButton.click(updateCartDisplayValue);
          decreaseButton.click(updateCartDisplayValue);

        case 29:
          i++;
          _context2.next = 3;
          break;

        case 32:
        case "end":
          return _context2.stop();
      }
    }
  }, _callee2);
}));

/***/ }),

/***/ "./resources/js/vender/jquery.ba-throttle-debounce.js":
/*!************************************************************!*\
  !*** ./resources/js/vender/jquery.ba-throttle-debounce.js ***!
  \************************************************************/
/***/ (function() {

/*!
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
// Script: jQuery throttle / debounce: Sometimes, less is more!
//
// *Version: 1.1, Last updated: 3/7/2010*
//
// Project Home - http://benalman.com/projects/jquery-throttle-debounce-plugin/
// GitHub       - http://github.com/cowboy/jquery-throttle-debounce/
// Source       - http://github.com/cowboy/jquery-throttle-debounce/raw/master/jquery.ba-throttle-debounce.js
// (Minified)   - http://github.com/cowboy/jquery-throttle-debounce/raw/master/jquery.ba-throttle-debounce.min.js (0.7kb)
//
// About: License
//
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
//
// About: Examples
//
// These working examples, complete with fully commented code, illustrate a few
// ways in which this plugin can be used.
//
// Throttle - http://benalman.com/code/projects/jquery-throttle-debounce/examples/throttle/
// Debounce - http://benalman.com/code/projects/jquery-throttle-debounce/examples/debounce/
//
// About: Support and Testing
//
// Information about what version or versions of jQuery this plugin has been
// tested with, what browsers it has been tested in, and where the unit tests
// reside (so you can test it yourself).
//
// jQuery Versions - none, 1.3.2, 1.4.2
// Browsers Tested - Internet Explorer 6-8, Firefox 2-3.6, Safari 3-4, Chrome 4-5, Opera 9.6-10.1.
// Unit Tests      - http://benalman.com/code/projects/jquery-throttle-debounce/unit/
//
// About: Release History
//
// 1.1 - (3/7/2010) Fixed a bug in <jQuery.throttle> where trailing callbacks
//       executed later than they should. Reworked a fair amount of internal
//       logic as well.
// 1.0 - (3/6/2010) Initial release as a stand-alone project. Migrated over
//       from jquery-misc repo v0.4 to jquery-throttle repo v1.0, added the
//       no_trailing throttle parameter and debounce functionality.
//
// Topic: Note for non-jQuery users
//
// jQuery isn't actually required for this plugin, because nothing internal
// uses any jQuery methods or properties. jQuery is just used as a namespace
// under which these methods can exist.
//
// Since jQuery isn't actually required for this plugin, if jQuery doesn't exist
// when this plugin is loaded, the method described below will be created in
// the `Cowboy` namespace. Usage will be exactly the same, but instead of
// $.method() or jQuery.method(), you'll need to use Cowboy.method().
(function (window, undefined) {
  '$:nomunge'; // Used by YUI compressor.
  // Since jQuery really isn't required for this plugin, use `jQuery` as the
  // namespace only if it already exists, otherwise use the `Cowboy` namespace,
  // creating it if necessary.
  // var $ = window.jQuery || window.Cowboy || ( window.Cowboy = {} ),
  // Internal method reference.
  // jq_throttle;

  var jq_throttle; // Method: jQuery.throttle
  //
  // Throttle execution of a function. Especially useful for rate limiting
  // execution of handlers on events like resize and scroll. If you want to
  // rate-limit execution of a function to a single time, see the
  // <jQuery.debounce> method.
  //
  // In this visualization, | is a throttled-function call and X is the actual
  // callback execution:
  //
  // > Throttled with `no_trailing` specified as false or unspecified:
  // > ||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
  // > X    X    X    X    X    X        X    X    X    X    X    X
  // >
  // > Throttled with `no_trailing` specified as true:
  // > ||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
  // > X    X    X    X    X             X    X    X    X    X
  //
  // Usage:
  //
  // > var throttled = jQuery.throttle( delay, [ no_trailing, ] callback );
  // >
  // > jQuery('selector').bind( 'someevent', throttled );
  // > jQuery('selector').unbind( 'someevent', throttled );
  //
  // This also works in jQuery 1.4+:
  //
  // > jQuery('selector').bind( 'someevent', jQuery.throttle( delay, [ no_trailing, ] callback ) );
  // > jQuery('selector').unbind( 'someevent', callback );
  //
  // Arguments:
  //
  //  delay - (Number) A zero-or-greater delay in milliseconds. For event
  //    callbacks, values around 100 or 250 (or even higher) are most useful.
  //  no_trailing - (Boolean) Optional, defaults to false. If no_trailing is
  //    true, callback will only execute every `delay` milliseconds while the
  //    throttled-function is being called. If no_trailing is false or
  //    unspecified, callback will be executed one final time after the last
  //    throttled-function call. (After the throttled-function has not been
  //    called for `delay` milliseconds, the internal counter is reset)
  //  callback - (Function) A function to be executed after delay milliseconds.
  //    The `this` context and all arguments are passed through, as-is, to
  //    `callback` when the throttled-function is executed.
  //
  // Returns:
  //
  //  (Function) A new, throttled, function.

  $.throttle = jq_throttle = function jq_throttle(delay, no_trailing, callback, debounce_mode) {
    // After wrapper has stopped being called, this timeout ensures that
    // `callback` is executed at the proper times in `throttle` and `end`
    // debounce modes.
    var timeout_id,
        // Keep track of the last time `callback` was executed.
    last_exec = 0; // `no_trailing` defaults to falsy.

    if (typeof no_trailing !== 'boolean') {
      debounce_mode = callback;
      callback = no_trailing;
      no_trailing = undefined;
    } // The `wrapper` function encapsulates all of the throttling / debouncing
    // functionality and when executed will limit the rate at which `callback`
    // is executed.


    function wrapper() {
      var that = this,
          elapsed = +new Date() - last_exec,
          args = arguments; // Execute `callback` and update the `last_exec` timestamp.

      function exec() {
        last_exec = +new Date();
        callback.apply(that, args);
      }

      ; // If `debounce_mode` is true (at_begin) this is used to clear the flag
      // to allow future `callback` executions.

      function clear() {
        timeout_id = undefined;
      }

      ;

      if (debounce_mode && !timeout_id) {
        // Since `wrapper` is being called for the first time and
        // `debounce_mode` is true (at_begin), execute `callback`.
        exec();
      } // Clear any existing timeout.


      timeout_id && clearTimeout(timeout_id);

      if (debounce_mode === undefined && elapsed > delay) {
        // In throttle mode, if `delay` time has been exceeded, execute
        // `callback`.
        exec();
      } else if (no_trailing !== true) {
        // In trailing throttle mode, since `delay` time has not been
        // exceeded, schedule `callback` to execute `delay` ms after most
        // recent execution.
        //
        // If `debounce_mode` is true (at_begin), schedule `clear` to execute
        // after `delay` ms.
        //
        // If `debounce_mode` is false (at end), schedule `callback` to
        // execute after `delay` ms.
        timeout_id = setTimeout(debounce_mode ? clear : exec, debounce_mode === undefined ? delay - elapsed : delay);
      }
    }

    ; // Set the guid of `wrapper` function to the same of original callback, so
    // it can be removed in jQuery 1.4+ .unbind or .die by using the original
    // callback as a reference.

    if ($.guid) {
      wrapper.guid = callback.guid = callback.guid || $.guid++;
    } // Return the wrapper function.


    return wrapper;
  }; // Method: jQuery.debounce
  //
  // Debounce execution of a function. Debouncing, unlike throttling,
  // guarantees that a function is only executed a single time, either at the
  // very beginning of a series of calls, or at the very end. If you want to
  // simply rate-limit execution of a function, see the <jQuery.throttle>
  // method.
  //
  // In this visualization, | is a debounced-function call and X is the actual
  // callback execution:
  //
  // > Debounced with `at_begin` specified as false or unspecified:
  // > ||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
  // >                          X                                 X
  // >
  // > Debounced with `at_begin` specified as true:
  // > ||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
  // > X                                 X
  //
  // Usage:
  //
  // > var debounced = jQuery.debounce( delay, [ at_begin, ] callback );
  // >
  // > jQuery('selector').bind( 'someevent', debounced );
  // > jQuery('selector').unbind( 'someevent', debounced );
  //
  // This also works in jQuery 1.4+:
  //
  // > jQuery('selector').bind( 'someevent', jQuery.debounce( delay, [ at_begin, ] callback ) );
  // > jQuery('selector').unbind( 'someevent', callback );
  //
  // Arguments:
  //
  //  delay - (Number) A zero-or-greater delay in milliseconds. For event
  //    callbacks, values around 100 or 250 (or even higher) are most useful.
  //  at_begin - (Boolean) Optional, defaults to false. If at_begin is false or
  //    unspecified, callback will only be executed `delay` milliseconds after
  //    the last debounced-function call. If at_begin is true, callback will be
  //    executed only at the first debounced-function call. (After the
  //    throttled-function has not been called for `delay` milliseconds, the
  //    internal counter is reset)
  //  callback - (Function) A function to be executed after delay milliseconds.
  //    The `this` context and all arguments are passed through, as-is, to
  //    `callback` when the debounced-function is executed.
  //
  // Returns:
  //
  //  (Function) A new, debounced, function.


  $.debounce = function (delay, at_begin, callback) {
    return callback === undefined ? jq_throttle(delay, at_begin, false) : jq_throttle(delay, callback, at_begin !== false);
  };
})(this);

/***/ }),

/***/ "./resources/js/verify-image.js":
/*!**************************************!*\
  !*** ./resources/js/verify-image.js ***!
  \**************************************/
/***/ (() => {

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return generator._invoke = function (innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; }(innerFn, self, context), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; this._invoke = function (method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); }; } function maybeInvokeDelegate(delegate, context) { var method = delegate.iterator[context.method]; if (undefined === method) { if (context.delegate = null, "throw" === context.method) { if (delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method)) return ContinueSentinel; context.method = "throw", context.arg = new TypeError("The iterator does not provide a 'throw' method"); } return ContinueSentinel; } var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) { if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; } return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, define(Gp, "constructor", GeneratorFunctionPrototype), define(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (object) { var keys = []; for (var key in object) { keys.push(key); } return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) { "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); } }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

window.verifyImage = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(event) {
    var isVerified, data;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            data = new FormData();
            data.append('image', $(event.target).prop('files')[0]);
            _context.next = 4;
            return axios.post('/api/image/verify', data).then(function (res) {
              isVerified = res.data.isVerified;
            })["catch"](function (error) {
              console.error(error);
            });

          case 4:
            return _context.abrupt("return", isVerified);

          case 5:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function (_x) {
    return _ref.apply(this, arguments);
  };
}();

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