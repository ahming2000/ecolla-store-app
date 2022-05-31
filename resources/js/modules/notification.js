const getNotificationTemplate = (title, message) => {
    return `
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="toast-header">
            <strong class="me-auto">
                ${title}
            </strong>

            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>

        <div class="toast-body">
            ${message}
        </div>
    </div>
    `
}

const actionNotificationTemplate = (title, message, actionButton) => {
    return `
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="toast-header">
            <strong class="me-auto">
                ${title}
            </strong>

            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}

            <div class="d-flex justify-content-center mt-2 pt-2 border-top">
                ${actionButton}
            </div>
        </div>
    </div>
    `
}

const actionButtonTemplate = (buttonText, redirectTo) => {
    return `<a class="btn btn-primary btn-sm mx-1" href="${redirectTo}">${buttonText}</a>`
}

window.addNotification = (title, message, action = []) => {
    let toastContainer = $('.toast-container');

    if (action.length === 0) {
        toastContainer.append(getNotificationTemplate(title, message))

        let toast = new bootstrap.Toast($('.toast').last())
        toast.show()
    } else {
        let actionButtons = ''

        for (let i = 0; i < action.length; i++) {
            actionButtons = actionButtons + actionButtonTemplate(action[i].buttonText, action[i].redirectTo)
        }

        toastContainer.append(actionNotificationTemplate(title, message, actionButtons))

        let toast = new bootstrap.Toast($('.toast').last())
        toast.show()
    }
}
