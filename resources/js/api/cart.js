import api from "../api";

export const count = async () => {
    let { data } = await api.get('/cart/count')
    return data.count
}

export const setOrderMode = async (orderMode) => {
    return await api.post('/cart/update-order-mode', {
        orderMode: orderMode,
    })
}

export const reset = async () => {
    return await api.post('/cart/reset')
}

export const add = async (addList) => {
    return await api.post('/cart/add', {
        addList: addList,
    })
}

export const remove = async (barcode) => {
    return await api.post('/cart/remove', {
        barcode: barcode,
    })
}

export const setQuantity = async (barcode, quantity) => {
    return await api.post('/cart/update-quantity', {
        barcode: barcode,
        quantity: quantity,
    })
}
