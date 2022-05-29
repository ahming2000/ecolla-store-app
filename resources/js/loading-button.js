const getLoadingAnimationTemplate = () => {
    return `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`
}

window.startLoading = (button) => {
    button.attr('disabled', true)
    button.find('i').attr('hidden', true)
    button.find('i').before(getLoadingAnimationTemplate())
}

window.stopLoading = (button) => {
    button.find('span.spinner-border').remove()
    button.attr('disabled', false)
    button.find('i').attr('hidden', false)
}
