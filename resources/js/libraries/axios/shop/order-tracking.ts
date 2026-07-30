import shopAjax from '@/libraries/axios/common/shop-ajax'
import { lookup as lookupOrderRoute } from '@/routes/shop/ajax/order-tracking'

export interface TrackedOrderItem {
    id: number
    name: string
    name_en: string | null
    barcode: string
    quantity: number
    unit_price: number
    line_total: number
}

export interface TrackedOrder {
    reference_num: string
    delivery_mode: string
    status: string
    tracking_no: string | null
    shipping_fee: number
    subtotal: number
    total: number
    note: string | null
    created_at: string
    updated_at: string
    items: TrackedOrderItem[]
}

export const lookupOrder = async (
    referenceNumber: string,
    phone: string
): Promise<TrackedOrder> => {
    return await shopAjax.post<TrackedOrder>(lookupOrderRoute.url(), {
        reference_num: referenceNumber,
        phone,
    })
}
