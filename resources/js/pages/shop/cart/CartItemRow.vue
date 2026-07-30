<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import SmartImage from '@/components/image/SmartImage.vue'
import { show as showItem } from '@/routes/shop/item'
import { useCartStore } from '@/stores/cart.store'
import type { CartItemData } from '@/types'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    cartItem: CartItemData
}>()

const cartStore = useCartStore()
const { locale, t } = useI18n()

const quantity = ref(props.cartItem.quantity)
const itemName = computed(() => {
    return locale.value === 'en' && props.cartItem.item.name_en
        ? props.cartItem.item.name_en
        : props.cartItem.item.name
})
const variationName = computed(() => {
    return locale.value === 'en' && props.cartItem.variation.name_en
        ? props.cartItem.variation.name_en
        : props.cartItem.variation.name
})
const totalWeightText = computed(
    () => `${(props.cartItem.variation.weight * quantity.value).toFixed(3)} kg`
)
const totalFinalPriceText = computed(
    () =>
        `RM ${(props.cartItem.variation.final_price * quantity.value).toFixed(2)}`
)
watch(
    () => [props.cartItem.quantity],
    () => {
        quantity.value = props.cartItem.quantity
    }
)

const onImageClick = (): void => {
    router.visit(showItem(props.cartItem.item))
}

const onQuantityChange = (): void => {
    cartStore.adjustQuantity(props.cartItem.variation.id, quantity.value)
}
</script>

<template>
    <div class="flex" :key="cartItem.variation.id">
        <div class="grow grid grid-cols-3 lg:grid-cols-4 gap-3">
            <div class="cursor-pointer" @click="onImageClick">
                <SmartImage
                    :alt="t('shop.cart.item.image-alt', { name: itemName })"
                    :fallback-src="fallbackImage"
                    :image="cartItem.variation.image"
                    image-class="rounded-3xl"
                    :src="cartItem.item.cover_image"
                    :thumbnail-src="cartItem.item.cover_thumbnail"
                    data-testid="cart-item-image"
                />
            </div>

            <div
                class="col-span-2 lg:col-span-3 flex flex-col justify-between p-3"
            >
                <div class="space-y-2">
                    <div class="text-2xl font-bold">
                        {{ itemName }}
                    </div>
                    <div>{{ variationName }}</div>
                    <div class="text-gray-500">
                        {{ totalWeightText }}
                    </div>
                    <div>
                        {{ totalFinalPriceText }}
                    </div>
                </div>

                <div class="flex justify-center">
                    <InputNumber
                        input-class="w-[60px]"
                        v-model="quantity"
                        @update:model-value="onQuantityChange"
                        :min="1"
                        :max="cartItem.variation.stock"
                        show-buttons
                        button-layout="horizontal"
                        size="small"
                    >
                        <template #incrementicon>
                            <i class="pi pi-plus" />
                        </template>

                        <template #decrementicon>
                            <i class="pi pi-minus" />
                        </template>
                    </InputNumber>
                </div>
            </div>
        </div>

        <div class="shrink">
            <Button
                :aria-label="
                    t('shop.cart.item.remove', { name: variationName })
                "
                icon="pi pi-cart-minus"
                severity="danger"
                size="small"
                @click="cartStore.removeItem(cartItem.variation.id)"
            />
        </div>
    </div>
</template>
