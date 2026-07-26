import { DELIVERY, getDefaultDeliveryMode } from '@/enums/DeliveryMode'
import { verifyCart } from '@/libraries/axios/shop/cart'
import CartItem from '@/objects/cart/CartItem'
import type { CartData, Item, ShippingSettings, Variation } from '@/types'
import { useForm } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import { defineStore } from 'pinia'
import { computed, nextTick, ref, watch } from 'vue'

export const useCartStore = defineStore('cart', () => {
    const cart = useForm<CartData>({
        deliveryMode: getDefaultDeliveryMode(),
        items: [],
    })
    const shippingSettings = ref<ShippingSettings>({
        fee: 0,
        freeShipping: {
            isActivated: false,
            threshold: 0,
            description: '',
        },
    })

    const setCart = (
        data: CartData,
        softAssign = true,
        hardReset = true
    ): void => {
        if (softAssign) {
            cart.deliveryMode = data.deliveryMode
            cart.items = data.items
        } else {
            cart.defaults(data)

            if (hardReset) {
                cart.reset()
            }
        }
    }

    /*
     * Searching function.
     */
    const getItemByVariationId = (variationId: number) => {
        return cart.items.find((item) => item.variation.id === variationId)
    }

    /*
     * Cart computed variables.
     */
    const itemCount = computed(() => {
        return cart.items.length
    })

    const isEmpty = computed(() => {
        return itemCount.value === 0
    })

    const getMaxQuantity = computed(() => {
        return (variation: Variation): number => {
            const existedItem = getItemByVariationId(variation.id)

            if (!existedItem) {
                return variation.stock
            }

            return variation.stock - existedItem.quantity
        }
    })

    const subtotal = computed(() => {
        return cart.items.reduce((acc, item) => {
            return acc + item.variation.final_price * item.quantity
        }, 0)
    })

    const subtotalText = computed(() => {
        return `RM ${subtotal.value.toFixed(2)}`
    })

    const isFreeShippingApplied = computed(() => {
        return (
            cart.deliveryMode === DELIVERY &&
            shippingSettings.value.freeShipping.isActivated &&
            subtotal.value >= shippingSettings.value.freeShipping.threshold
        )
    })

    const shippingFee = computed(() => {
        if (cart.deliveryMode !== DELIVERY || isFreeShippingApplied.value) {
            return 0
        }

        return shippingSettings.value.fee
    })

    const shippingFeeText = computed(() => {
        return `RM ${shippingFee.value.toFixed(2)}`
    })

    const configuredShippingFeeText = computed(() => {
        return `RM ${shippingSettings.value.fee.toFixed(2)}`
    })

    const total = computed(() => {
        return subtotal.value + shippingFee.value
    })

    const totalText = computed(() => {
        return `RM ${total.value.toFixed(2)}`
    })

    /*
     * Item adjustments.
     */
    const addItem = (
        item: Item,
        variation: Variation,
        quantity: number
    ): void => {
        const existedItem = getItemByVariationId(variation.id)

        if (existedItem) {
            existedItem.quantity += quantity
        } else {
            cart.items.push(new CartItem(item, variation, quantity))
        }
    }

    const removeItem = (variationId: number): void => {
        cart.items = cart.items.filter((item) => {
            return item.variation.id !== variationId
        })
    }

    const adjustQuantity = (variationId: number, quantity = 0): void => {
        const existedItem = getItemByVariationId(variationId)

        if (!existedItem) {
            return
        }

        existedItem.quantity = quantity
    }

    const reset = (): void => {
        setCart(
            {
                deliveryMode: getDefaultDeliveryMode(),
                items: [],
            },
            false
        )
        sessionStorage.removeItem('cart')
    }

    const setShippingSettings = (settings: ShippingSettings): void => {
        shippingSettings.value = settings
    }

    /*
     * Session management.
     */
    const retrieveSession = async (): Promise<void> => {
        try {
            const sessionCartData = sessionStorage.getItem('cart')

            if (!sessionCartData) {
                return
            }

            const data = JSON.parse(sessionCartData) as Partial<CartData>

            setCart(
                {
                    deliveryMode: data.deliveryMode ?? getDefaultDeliveryMode(),
                    items: CartItem.fromArray(data.items ?? []),
                },
                false
            )
        } catch (e) {
            throw e
        }
    }

    const storeSession = async (): Promise<void> => {
        try {
            const data = await verifyCart(cart.data())

            setCart(
                {
                    deliveryMode: data.deliveryMode,
                    items: CartItem.fromArray(data.items),
                },
                false
            )

            await nextTick(() => {
                sessionStorage.setItem('cart', JSON.stringify(cart.data()))
            })
        } catch (e) {
            throw e
        }
    }

    const autoSave = async (): Promise<void> => {
        if (cart.isDirty) {
            try {
                await storeSession()
            } catch (e) {
                console.error(e)
            }
        }
    }

    watch(() => cart.data(), debounce(autoSave, 3000), { deep: true })

    /*
     * Cart initiation.
     */
    const init = async (): Promise<void> => {
        try {
            await retrieveSession()
            await storeSession()
        } catch (e) {
            console.error(e)
            throw e
        }
    }

    return {
        cart,

        itemCount,
        isEmpty,
        getMaxQuantity,

        subtotal,
        subtotalText,
        shippingSettings,
        isFreeShippingApplied,
        shippingFee,
        shippingFeeText,
        configuredShippingFeeText,
        total,
        totalText,

        addItem,
        removeItem,
        adjustQuantity,
        reset,
        setShippingSettings,

        retrieveSession,
        validateCart: storeSession,

        init,
        autoSave,
    }
})
