import shopAjax from '@/libraries/axios/common/shop-ajax'
import { index as paymentMethodIndex } from '@/routes/shop/ajax/payment-method'
import type { PaymentMethod } from '@/types'

export const getAllPaymentMethod = async (): Promise<PaymentMethod[]> => {
    return await shopAjax.get<PaymentMethod[]>(paymentMethodIndex.url())
}
