const getAllQuantityControl = () => {
    return $('.quantity-control')
}

const getQuantityNode = (quantityControl) => {
    return quantityControl.find('.quantity')
}

const getQuantity = (quantityControl) => {
    return parseInt(quantityControl.find('.quantity').val())
}

const getMaxQuantity = (quantityControl) => {
    return parseInt(quantityControl.find('.quantity').attr('max'))
}

const getMinQuantity = (quantityControl) => {
    return parseInt(quantityControl.find('.quantity').attr('min'))
}

const getIncreaseButton = (quantityControl) => {
    return quantityControl.find('.quantity-increase')
}

const getDecreaseButton = (quantityControl) => {
    return quantityControl.find('.quantity-decrease')
}

const toggleButtonDisabled = (quantityControl) => {
    let currentValue = getQuantity(quantityControl)
    let maxValue = getMaxQuantity(quantityControl)
    let minValue = getMinQuantity(quantityControl)
    let increaseButton = getIncreaseButton(quantityControl)
    let decreaseButton = getDecreaseButton(quantityControl)

    increaseButton.attr('disabled', false)
    decreaseButton.attr('disabled', false)

    if (currentValue === minValue) {
        decreaseButton.attr('disabled', true)
    }

    if (currentValue === maxValue) {
        increaseButton.attr('disabled', true)
    }
}

const increaseQuantity = (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let current = getQuantityNode(quantityControl)
    let currentValue = getQuantity(quantityControl)
    let maxValue = getMaxQuantity(quantityControl)
    let minValue = getMinQuantity(quantityControl)

    if (minValue <= currentValue && currentValue < maxValue) {
        current.val(currentValue + 1)
    }

    toggleButtonDisabled(quantityControl)
}

const decreaseQuantity = (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let current = getQuantityNode(quantityControl)
    let currentValue = getQuantity(quantityControl)
    let maxValue = getMaxQuantity(quantityControl)
    let minValue = getMinQuantity(quantityControl)

    if (minValue < currentValue && currentValue <= maxValue) {
        current.val(currentValue - 1)
    }

    toggleButtonDisabled(quantityControl)
}

const updateQuantity = (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let current = getQuantityNode(quantityControl)
    let currentValue = getQuantity(quantityControl)
    let maxValue = getMaxQuantity(quantityControl)
    let minValue = getMinQuantity(quantityControl)

    if (currentValue >= maxValue) {
        current.val(maxValue)
    } else if (currentValue <= minValue) {
        current.val(minValue)
    }

    toggleButtonDisabled(quantityControl)
}

const saveQuantity = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let barcode = quantityControl.attr('id')
    let currentValue = getQuantity(quantityControl)

    await axios.post('/api/cart/update-quantity', {
        barcode: barcode,
        quantity: currentValue,
    }).catch((error) => {
        console.error(error)
    })
}

window.useQuantityControl = async (isCart = false) => {
    let quantityControls = getAllQuantityControl()
    for (let i = 0; i < quantityControls.length; i++) {
        let quantityControl = quantityControls.eq(i)
        let increaseButton = getIncreaseButton(quantityControl)
        let decreaseButton = getDecreaseButton(quantityControl)
        let quantityNode = getQuantityNode(quantityControl)

        increaseButton.click(increaseQuantity)
        decreaseButton.click(decreaseQuantity)
        quantityNode.change(updateQuantity)

        toggleButtonDisabled(quantityControl)

        if (isCart) {
            increaseButton.click($.debounce(500, false, await saveQuantity))
            decreaseButton.click($.debounce(500, false, await saveQuantity))

            increaseButton.click(updateCartDisplayValue)
            decreaseButton.click(updateCartDisplayValue)
        }
    }
}
