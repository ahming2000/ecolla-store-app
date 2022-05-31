import {DELIVERY} from "../enums/order-mode.enum";
import {reset, count, setOrderMode, remove} from "../api/cart";
import {getShippingFeeConfig} from "../api/system-config";

window.updateCartCount = async () => {
    $('#cart-count').html(await count())
}

const getSubPrice = (cartItemContainer) => {
    let quantity = parseInt(cartItemContainer.find('.quantity-control').find('.quantity').val())
    let price = parseFloat(cartItemContainer.find('.variation-price').val())

    return price * quantity
}

const getWeight = (cartItemContainer) => {
    let quantity = parseInt(cartItemContainer.find('.quantity-control').find('.quantity').val())
    let weight = parseFloat(cartItemContainer.find('.variation-weight').val())

    return weight * quantity
}

const getSubtotal = () => {
    let cartItemContainers = $('.cart-item-container')
    let subtotal = 0.0

    for (let i = 0; i < cartItemContainers.length; i++) {
        let price = getSubPrice(cartItemContainers.eq(i))
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

window.updateSummary = async () => {
    let subtotal = getSubtotal()
    let shippingFee = await getShippingFee(subtotal)
    let total = subtotal + shippingFee

    let subtotalNode = $('#subtotal')
    let shippingFeeNode = $('#shipping-fee')
    let totalNode = $('#total')

    subtotalNode.html(subtotal.toFixed(2))
    shippingFeeNode.html(shippingFee.toFixed(2))
    totalNode.html(total.toFixed(2))
}

window.updateCartDisplayValue = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let cartItemContainer = quantityControl.closest('.cart-item-container')

    let price = getSubPrice(cartItemContainer)
    let weight = getWeight(cartItemContainer)

    let weightNode = cartItemContainer.find('.cart-item-weight')
    let subPriceNode = cartItemContainer.find('.cart-item-sub-price')

    weightNode.html(weight.toFixed(3) + 'kg')
    subPriceNode.html('RM' + price.toFixed(2))
}

window.updateShippingFee = async (event) => {
    await setOrderMode(event.target.value)
    await updateSummary()
}

window.resetCart = async () => {
    await reset()

    let cartItemContainers = $('.cart-item-container')

    for (let i = 0; i < cartItemContainers.length; i++) {
        cartItemContainers.eq(i).remove()
    }

    $('#cart-empty-icon').attr('hidden', false)
    $('#cart-reset-button').attr('hidden', true)

    await updateCartCount()
    await updateSummary()
}

window.removeCartItem = async (event) => {
    let cartItemContainer = $(event.target).closest('.cart-item-container')

    await remove(cartItemContainer.attr('id'))

    cartItemContainer.remove()

    if ($('.cart-item-container').length === 0) {
        $('#cart-empty-icon').attr('hidden', false)
        $('#cart-reset-button').attr('hidden', true)
    }

    await updateCartCount()
    await updateSummary()
}
