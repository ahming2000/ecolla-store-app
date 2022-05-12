window.updateCartCount = () => {
    let cartCountNode = $('#cart-count')

    axios.get('/api/cart/count').then((res) => {
        cartCountNode.html(res.data.count)
    }).catch((error) => {
        console.error(error)
    })
}
