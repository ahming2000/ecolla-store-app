export const PENDING = '处理中'
export const READY = '准备就绪'
export const COMPLETED = '已完成'
export const REFUNDED = '已退款'
export const CANCELED = '已取消'

export type OrderStatus =
    | typeof PENDING
    | typeof READY
    | typeof COMPLETED
    | typeof REFUNDED
    | typeof CANCELED

type Translate = (key: string) => string

export const getOrderStatuses = (): OrderStatus[] => {
    return [PENDING, READY, COMPLETED, REFUNDED, CANCELED]
}

export const getOrderStatusLabel = (
    translate: Translate,
    status: string
): string => {
    switch (status) {
        case PENDING:
            return translate('admin.orders.status.pending')
        case READY:
            return translate('admin.orders.status.ready')
        case COMPLETED:
            return translate('admin.orders.status.completed')
        case REFUNDED:
            return translate('admin.orders.status.refunded')
        case CANCELED:
            return translate('admin.orders.status.canceled')
        default:
            return status
    }
}
