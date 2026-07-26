export const SELF_PICKUP = '预购取货'
export const DELIVERY = '外送'

export type DeliveryMode = typeof SELF_PICKUP | typeof DELIVERY

type Translate = (key: string) => string

export const getDefaultDeliveryMode = (): DeliveryMode => {
    return SELF_PICKUP
}

export const getAllDeliveryModes = (): DeliveryMode[] => {
    return [SELF_PICKUP, DELIVERY]
}

export const getDeliveryModeLabel = (
    translate: Translate,
    deliveryMode: string
): string => {
    switch (deliveryMode) {
        case SELF_PICKUP:
            return translate('admin.orders.delivery-mode.self-pickup')
        case DELIVERY:
            return translate('admin.orders.delivery-mode.delivery')
        default:
            return deliveryMode
    }
}
