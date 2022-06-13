import {DELIVERY} from "../enums/order-mode.enum";
import {reset, count, setOrderMode, remove, setQuantity, add} from "../api/cart/cart";
import {getShippingFeeConfig} from "../api/system-config/system-config";

const getSubPrice = (cartItem) => {
    let quantity = parseInt(cartItem.find('.quantity-control').find('.quantity').val())
    let price = parseFloat(cartItem.find('.variation-price').val())

    return price * quantity
}

const getWeight = (cartItem) => {
    let quantity = parseInt(cartItem.find('.quantity-control').find('.quantity').val())
    let weight = parseFloat(cartItem.find('.variation-weight').val())

    return weight * quantity
}

const getSubtotal = () => {
    let cartItems = $('.cart-item')
    let subtotal = 0.0

    for (let i = 0; i < cartItems.length; i++) {
        let price = getSubPrice(cartItems.eq(i))
        subtotal += price
    }

    return subtotal
}

const getShippingFee = async (subtotal) => {
    let orderMode = parseInt($('#order-mode-input').val())

    let {shippingFee, freeShippingIsActivated, freeShippingThreshold} = await getShippingFeeConfig()

    if (orderMode === DELIVERY) {
        if (freeShippingIsActivated) {
            if (subtotal >= freeShippingThreshold) {
                return 0.0
            } else {
                return shippingFee
            }
        } else {
            return shippingFee
        }
    }

    return 0.0
}

window.updateCartCount = async () => {
    $('#cart-count').html(await count())
}

const saveQuantity = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')

    let barcode = quantityControl.attr('data-barcode')
    let currentQuantity = parseInt(quantityControl.find('.quantity').val())

    await setQuantity(barcode, currentQuantity)
}

const updateSummary = async () => {
    let subtotal = getSubtotal()
    let shippingFee = await getShippingFee(subtotal)
    let total = subtotal + shippingFee

    let subtotalNode = $('#subtotal')
    let shippingFeeNode = $('#shipping-fee')
    let totalNode = $('#total')

    subtotalNode.html(subtotal.toFixed(2))
    shippingFeeNode.html(shippingFee.toFixed(2))
    totalNode.html(total.toFixed(2))

    // Disable check out button
    if (await count() === 0) {
        $('#check-out-button').addClass('disabled')
    }
}

const updatePriceWeight = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let cartItem = quantityControl.closest('.cart-item')

    let price = getSubPrice(cartItem)
    let weight = getWeight(cartItem)

    let weightNode = cartItem.find('.cart-item-weight')
    let subPriceNode = cartItem.find('.cart-item-sub-price')

    weightNode.html(weight.toFixed(3) + 'kg')
    subPriceNode.html('RM' + price.toFixed(2))
}

const quantityOnChange = async (event) => {
    await saveQuantity(event)
    await updatePriceWeight(event)
    await updateSummary()
}

window.useCartControl = async () => {
    let quantityControls = await useQuantityControl()

    for (let i = 0; i < quantityControls.length; i++) {
        let quantityControl = quantityControls.eq(i)

        let increaseButton = quantityControl.find('.quantity-increase')
        let decreaseButton = quantityControl.find('.quantity-decrease')
        let quantityNode = quantityControl.find('.quantity')

        increaseButton.click($.debounce(500, false, quantityOnChange))
        decreaseButton.click($.debounce(500, false, quantityOnChange))
        quantityNode.change($.debounce(500, false, quantityOnChange))
    }
}

window.addToCart = async (event, lang) => {
    let quantityControls = $('.quantity-control')
    let addList = [];

    for (let i = 0; i < quantityControls.length; i++) {
        let barcode = quantityControls.eq(i).attr('data-barcode')
        let quantity = parseInt(quantityControls.eq(i).find('.quantity').val())

        if (quantity !== 0) {
            addList.push(
                {
                    barcode: barcode,
                    quantity: quantity,
                }
            )
        }
    }

    if (addList.length !== 0) {
        await add(addList)

        if (lang === 'en')
            addNotification('Cart', 'Add to cart successfully!', [
                    {buttonText: 'Go To Cart', redirectTo: '/cart'},
                    {buttonText: 'Back To Item List', redirectTo: '/item'}
                ]
            )
        else {
            addNotification('购物车', '成功加入购物车！', [
                    {buttonText: '前往购物车', redirectTo: '/cart'},
                    {buttonText: '返回商品列表', redirectTo: '/item'}
                ]
            )
        }

        await updateCartCount()
    }
}

window.removeFromCart = async (event) => {
    let cartItem = $(event.target).closest('.cart-item')

    let barcode = cartItem.attr('data-barcode')

    await remove(barcode)

    // Remove the cart item row
    cartItem.remove()

    if ($('.cart-item').length === 0) {
        $('#cart-empty-icon').attr('hidden', false)
        $('#cart-reset-button').attr('hidden', true)
    }

    await updateCartCount()

    await updateSummary()
}

window.resetCart = async () => {
    await reset()

    let cartItems = $('.cart-item')

    for (let i = 0; i < cartItems.length; i++) {
        cartItems.eq(i).remove()
    }

    $('#cart-empty-icon').attr('hidden', false)
    $('#cart-reset-button').attr('hidden', true)

    await updateCartCount()
    await updateSummary()
}

window.orderModeOnChange = async (event) => {
    await setOrderMode(event.target.value)
    await updateSummary()
}
