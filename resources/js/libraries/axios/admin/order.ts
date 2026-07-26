import adminAjax from '@/libraries/axios/common/admin-ajax'
import { index as orderIndex } from '@/routes/admin/ajax/order'
import { update as updateOrderStatusRoute } from '@/routes/admin/ajax/order/status'
import { update as updateOrderTrackingNumberRoute } from '@/routes/admin/ajax/order/tracking-number'
import type {
    Identifier,
    Order,
    OrderFulfilment,
    PaginatedResponse,
} from '@/types'

export interface AdminOrderIndexQuery {
    order_date?: string
    delivery_mode?: Order['delivery_mode']
    page: number
    per_page: number
}

export const getAdminOrders = async (
    query: AdminOrderIndexQuery
): Promise<PaginatedResponse<Order>> => {
    return await adminAjax.get<PaginatedResponse<Order>>(
        orderIndex.url({
            query: {
                order_date: query.order_date,
                delivery_mode: query.delivery_mode,
                page: query.page,
                per_page: query.per_page,
            },
        })
    )
}

export const updateOrderStatus = async (
    orderId: Identifier,
    status: Order['status'],
    trackingNumber: Order['tracking_no']
): Promise<OrderFulfilment> => {
    return await adminAjax.patch<OrderFulfilment>(
        updateOrderStatusRoute.url(orderId),
        {
            status,
            tracking_no: trackingNumber,
        }
    )
}

export const updateOrderTrackingNumber = async (
    orderId: Identifier,
    trackingNumber: Order['tracking_no']
): Promise<OrderFulfilment> => {
    return await adminAjax.patch<OrderFulfilment>(
        updateOrderTrackingNumberRoute.url(orderId),
        {
            tracking_no: trackingNumber,
        }
    )
}
