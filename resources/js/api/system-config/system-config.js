import api from "../../api";

export const getShippingFeeConfig = async () => {
    let { data } = await api.get('/system-config/shipping-fee-config')
    return data
}

export const setShippingFee = async (shipping_fee) => {
    return await api.patch('/setting/shipping/fee', {
        shipping_fee: shipping_fee,
    })
}

export const setFreeShippingIsActivated = async (freeShipping_isActivated) => {
    return await api.patch('/setting/shipping/discount', {
        freeShipping_isActivated: freeShipping_isActivated,
    })
}

export const setFreeShippingConfig = async (freeShipping_threshold, freeShipping_desc) => {
    return await api.patch('/setting/shipping/discount', {
        freeShipping_threshold: freeShipping_threshold,
        freeShipping_desc: freeShipping_desc,
    })
}
