const SELF_PICKUP = 0
const DELIVERY = 1

window.updateCartCount = () => {
    let cartCountNode = $('#cart-count')

    axios.get('/api/cart/count').then((res) => {
        cartCountNode.html(res.data.count)
    }).catch((error) => {
        console.error(error)
    })
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

    let data

    await axios.get('/api/system-config/shipping-fee-config')
        .then((res) => {
            data = res.data
        })
        .catch((error) => {
            console.error(error)
        })

    let {fee, hasDiscount, discountThreshold} = data

    if (orderMode === DELIVERY) {
        if (hasDiscount) {
            if (subtotal >= discountThreshold) {
                return 0.0
            } else {
                return fee
            }
        } else {
            return fee
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

    await updateSummary()
}

window.updateShippingFee = (event) => {
    axios.post('/api/cart/update-order-mode', {
        orderMode: event.target.value
    }).then((res) => {
        if (res.data.isUpdated) {
            updateSummary()
        }
    }).catch((error) => {
        console.error(error)
    })
}

window.resetCart = async () => {
    axios.post('/api/cart/reset')
        .then(async (res) => {
            if (res.data.isReset) {
                let cartItemContainers = $('.cart-item-container')

                for (let i = 0; i < cartItemContainers.length; i++) {
                    cartItemContainers.eq(i).remove()
                }

                $('#cart-empty-icon').attr('hidden', false)
                $('#cart-reset-button').attr('hidden', true)

                updateCartCount()
                await updateSummary()
            }
        })
        .catch((error) => {
            console.error(error)
        })
}

window.removeCartItem = (event) => {
    let cartItemContainer = $(event.target).closest('.cart-item-container')

    axios.post('/api/cart/remove', {
        barcode: cartItemContainer.attr('id')
    }).then(async (res) => {
        if (res.data.isRemoved) {
            cartItemContainer.remove()

            if ($('.cart-item-container').length === 0) {
                $('#cart-empty-icon').attr('hidden', false)
                $('#cart-reset-button').attr('hidden', true)
            }

            updateCartCount()
            await updateSummary()
        }
    }).catch((error) => {
        console.error(error)
    })
}
