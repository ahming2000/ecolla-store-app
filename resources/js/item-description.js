$(document).ready(() => {
    let itemDescriptionNode = $('#item-description')

    if (itemDescriptionNode.length) {
        let displayNode = itemDescriptionNode.closest('div').find('p')
        displayNode.html(itemDescriptionNode.val().split('\n').join('<br />'));
    }
})

