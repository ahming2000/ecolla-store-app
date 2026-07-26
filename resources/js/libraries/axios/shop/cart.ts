import shopAjax from '@/libraries/axios/common/shop-ajax'
import {
    checkout as checkoutCartRoute,
    verify as verifyCartRoute,
} from '@/routes/shop/ajax/cart'
import type { CartData, Image, Order, PaymentMethod } from '@/types'

export interface CheckoutFormData {
    cus_name: string | null
    cus_phone: string | null
    cus_address: string | null
    receipt_image: Image | null
    payment_method: PaymentMethod | null
}

export const verifyCart = async (cart: CartData): Promise<CartData> => {
    return await shopAjax.post<CartData>(verifyCartRoute.url(), cart)
}

export const checkoutCart = async (
    cart: CartData,
    checkoutForm: CheckoutFormData
): Promise<Order> => {
    return await shopAjax.post<Order>(checkoutCartRoute.url(), {
        cart,
        checkoutForm,
    })
}
