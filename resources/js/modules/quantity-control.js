const updateButtonBehavior = async (quantityControl) => {
    let currentQuantity = parseInt(quantityControl.find('.quantity').val())
    let maxQuantity = parseInt(quantityControl.find('.quantity').attr('max'))
    let minQuantity = parseInt(quantityControl.find('.quantity').attr('min'))
    let increaseButton = quantityControl.find('.quantity-increase')
    let decreaseButton = quantityControl.find('.quantity-decrease')

    increaseButton.attr('disabled', false)
    decreaseButton.attr('disabled', false)

    if (currentQuantity === minQuantity) {
        decreaseButton.attr('disabled', true)
    }

    if (currentQuantity === maxQuantity) {
        increaseButton.attr('disabled', true)
    }
}

const increaseQuantity = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let quantityNode = quantityControl.find('.quantity')

    let currentQuantity = parseInt(quantityNode.val())
    let maxQuantity = parseInt(quantityControl.find('.quantity').attr('max'))
    let minQuantity = parseInt(quantityControl.find('.quantity').attr('min'))

    if (minQuantity <= currentQuantity && currentQuantity < maxQuantity) {
        quantityNode.val(currentQuantity + 1)
    }

    await updateButtonBehavior(quantityControl)
}

const decreaseQuantity = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let quantityNode = quantityControl.find('.quantity')

    let currentQuantity = parseInt(quantityNode.val())
    let maxQuantity = parseInt(quantityControl.find('.quantity').attr('max'))
    let minQuantity = parseInt(quantityControl.find('.quantity').attr('min'))

    if (minQuantity < currentQuantity && currentQuantity <= maxQuantity) {
        quantityNode.val(currentQuantity - 1)
    }

    await updateButtonBehavior(quantityControl)
}

const quantityOnChange = async (event) => {
    let quantityControl = $(event.target).closest('.quantity-control')
    let quantityNode = quantityControl.find('.quantity')

    let maxQuantity = parseInt(quantityControl.find('.quantity').attr('max'))
    let minQuantity = parseInt(quantityControl.find('.quantity').attr('min'))
    let currentQuantity = parseInt(quantityNode.val() === '' ? minQuantity : quantityNode.val())

    if (currentQuantity >= maxQuantity) {
        quantityNode.val(maxQuantity)
    } else if (currentQuantity <= minQuantity) {
        quantityNode.val(minQuantity)
    }

    await updateButtonBehavior(quantityControl)
}

window.useQuantityControl = async () => {
    let quantityControls = $('.quantity-control')

    for (let i = 0; i < quantityControls.length; i++) {
        let quantityControl = quantityControls.eq(i)

        let increaseButton = quantityControl.find('.quantity-increase')
        let decreaseButton = quantityControl.find('.quantity-decrease')
        let quantityNode = quantityControl.find('.quantity')

        increaseButton.click(increaseQuantity)
        decreaseButton.click(decreaseQuantity)
        quantityNode.change(quantityOnChange)

        await updateButtonBehavior(quantityControl)
    }

    return quantityControls
}
