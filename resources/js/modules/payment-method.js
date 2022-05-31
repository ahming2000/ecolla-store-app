window.selectPayment = (event) => {
    let payments = $('.payment-method-selection-container').find('img')

    for (let i = 0; i < payments.length; i++) {
        payments.removeClass('payment-selected')
    }

    $(event.target).addClass('payment-selected')
    $('#payment-method-input').val($(event.target).data('id'))
}

window.openPaymentQRCode = (event) => {
    event.preventDefault()

    let qrCode = $('.payment-method-selection-container').find('.payment-selected').data('qrcode')

    $('#qr-code-image').attr('src', qrCode)
    let QRCodeModal = new bootstrap.Modal($('#qr-code-modal'))
    QRCodeModal.show()
}
