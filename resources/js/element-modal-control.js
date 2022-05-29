const getPillTemplate = (data) => {
    return `
            <span class="badge rounded-pill mb-1 me-1" onclick="openEditElementModal(event)"
                  data-element='${JSON.stringify(data)}' id="${data.id}">
                <i class="bi bi-pencil-square"></i> ${data.name}</span>
            </span>
            `
}

window.openCreateElementModal = (event) => {
    let type = $(event.target).closest('.element-parent').data('element-type')
    let modalNode = $('#create-element-modal')

    // Set create modal title
    if (type === 'origin') {
        modalNode.find('.modal-title').html('创建新出产地')
    } else if (type === 'category') {
        modalNode.find('.modal-title').html('创建新类别')
    } else {
        console.error('Fail to open create element modal!')
        return
    }

    // Set type for modal to recognize the type of the creation
    modalNode.attr('data-element-type', type)

    let modal = new bootstrap.Modal(modalNode)
    modal.show()
}

window.createItemSettingElement = (event) => {
    let button = $(event.target)
    let modalNode = button.closest('#create-element-modal')
    let modal = bootstrap.Modal.getInstance(modalNode)
    let type = modalNode.attr('data-element-type')
    let nameNode = $('#name-input-create')
    let nameEnNode = $('#name-en-input-create')

    // "required" verification
    if (nameNode.val() === '' || nameEnNode.val() === '') {
        if (nameNode.val() === '') {
            nameNode.addClass('is-invalid')
        } else {
            nameNode.removeClass('is-invalid')
        }

        if (nameEnNode.val() === '') {
            nameEnNode.addClass('is-invalid')
        } else {
            nameEnNode.removeClass('is-invalid')
        }
    }

    // Proceed to create the element
    else {
        startLoading(button)

        axios.post('/api/setting/' + type, {
            name: nameNode.val(),
            name_en: nameEnNode.val(),
        }).then((res) => {
            if (res.data.isCreated) {
                addNotification('通知', '创建成功！')

                // Add pill display
                $('.element-parent').find('.pill-container-type-' + type).append(getPillTemplate(res.data.model))

                // Reset form
                nameNode.val('')
                nameEnNode.val('')

                modal.hide()
            } else {
                console.log('Create ' + type + ' failed!')
            }

            stopLoading(button)
        }).catch((error) => {
            console.error(error)
            stopLoading(button)
        })
    }
}

window.openEditElementModal = (event) => {
    let type = $(event.target).closest('.element-parent').data('element-type')
    let modalNode = $('#edit-element-modal')

    // Since the onclick event required data from "span" tag,
    // make sure both clicked frame can retrieve the data
    let data = $(event.target).data('element') || $(event.target).closest('span.badge').data('element')

    // Set edit modal title
    if (type === 'origin') {
        modalNode.find('.modal-title').html('编辑出产地 "' + data.name + '"')
    } else if (type === 'category') {
        modalNode.find('.modal-title').html('编辑类别 "' + data.name + '"')
    } else {
        console.error('Fail to open edit element modal!')
        return
    }

    // Set selected element properties
    $('#name-input-edit').val(data.name)
    $('#name-en-input-edit').val(data.name_en)

    // Set data for delete modal and update form submission to use
    modalNode.attr('data-element-type', type)
    modalNode.attr('data-element', JSON.stringify(data))

    let modal = new bootstrap.Modal(modalNode)
    modal.show()
}

window.updateItemSettingElement = (event) => {
    let button = $(event.target)
    let modalNode = button.closest('#edit-element-modal')
    let modal = bootstrap.Modal.getInstance(modalNode)
    let type = modalNode.data('element-type')
    let data = modalNode.data('element')
    let nameNode = $('#name-input-edit')
    let nameEnNode = $('#name-en-input-edit')

    // "required" verification
    if (nameNode.val() === '' || nameEnNode.val() === '') {
        if (nameNode.val() === '') {
            nameNode.addClass('is-invalid')
        } else {
            nameNode.removeClass('is-invalid')
        }

        if (nameEnNode.val() === '') {
            nameEnNode.addClass('is-invalid')
        } else {
            nameEnNode.removeClass('is-invalid')
        }
    }

    // Proceed to update the element
    else {
        startLoading(button)

        axios.patch('/api/setting/' + type + '/' + data.id, {
            name: nameNode.val(),
            name_en: nameEnNode.val(),
        }).then((res) => {
            if (res.data.isUpdated) {
                addNotification('通知', '更新成功！')

                // Update current pill display and current data saved on "span" tag
                let elementNode = $('#' + type + '-' + data.id)
                elementNode.find('.element-name').html(nameNode.val())
                elementNode.attr('data-element', JSON.stringify(res.data.model))

                modal.hide()
            } else {
                console.log('Update ' + type + ' failed!')
            }

            stopLoading(button)
        }).catch((error) => {
            console.error(error)
            stopLoading(button)
        })
    }
}

window.openDeleteElementModal = (event) => {
    let modalNode = $('#delete-element-modal')
    let data = JSON.parse($(event.target).closest('#edit-element-modal').attr('data-element'))

    modalNode.find('.element-name').html(data.name)

    let modal = new bootstrap.Modal(modalNode)
    modal.show()
}

window.deleteItemSettingElement = (event) => {
    let editModalNode = $('#edit-element-modal')
    let deleteModalNode = $('#delete-element-modal')
    let data = JSON.parse(editModalNode.attr('data-element'))
    let type = editModalNode.attr('data-element-type')

    axios.delete('/api/setting/' + type + '/' + data.id).then((res) => {
        if (res.data.isDeleted) {
            addNotification('通知', '删除 "' + data.name + '" 成功！')

            let elementNode = $('#' + type + '-' + data.id)
            elementNode.remove()

            let modal = bootstrap.Modal.getInstance(deleteModalNode)
            modal.hide()
            modal = bootstrap.Modal.getInstance(editModalNode)
            modal.hide()
        }
    }).catch((error) => {
        console.error(error)
    })

}
