<script setup lang="ts">
import { useCartStore } from '@/stores/cart.store'
import type { Variation } from '@/types'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputNumber from 'primevue/inputnumber'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        variation: Variation
        quantity?: number
    }>(),
    {
        quantity: 0,
    }
)

const emit = defineEmits<{
    'select-image': [variation: Variation]
    'update:quantity': [quantity: number]
}>()

const cartStore = useCartStore()
const { locale, t } = useI18n()

const variationName = computed(() => {
    return locale.value === 'en' && props.variation.name_en
        ? props.variation.name_en
        : props.variation.name
})
</script>

<template>
    <Card :key="variation.id" :data-testid="`variation-card-${variation.id}`">
        <template #content>
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="self-start flex flex-col">
                    <div class="font-bold">{{ variationName }}</div>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span
                            :class="{
                                'line-through': variation.sale_price,
                                'text-gray-400': variation.sale_price,
                            }"
                        >
                            {{ variation.price_text }}
                        </span>

                        <span v-if="variation.sale_price">
                            {{ variation.sale_price_text }}
                        </span>

                        <span
                            class="text-sm text-gray-500"
                            :data-testid="`variation-stock-${variation.id}`"
                        >
                            {{
                                t('shop-item.variation.stock', {
                                    count: variation.stock,
                                })
                            }}
                        </span>

                        <Badge
                            v-if="variation.stock <= 0"
                            :value="t('shop.item.sold-out')"
                            class="px-2"
                            severity="danger"
                        />
                    </div>
                </div>

                <div
                    class="flex shrink-0 items-center gap-2 self-end sm:self-auto"
                >
                    <Button
                        v-if="variation.image"
                        type="button"
                        icon="pi pi-image"
                        outlined
                        rounded
                        severity="secondary"
                        size="small"
                        :aria-label="
                            t('shop-item.variation.view-image', {
                                name: variationName,
                            })
                        "
                        :title="
                            t('shop-item.variation.view-image', {
                                name: variationName,
                            })
                        "
                        :data-testid="`variation-image-button-${variation.id}`"
                        @click="emit('select-image', variation)"
                    />

                    <InputNumber
                        :disabled="
                            variation.stock <= 0 ||
                            cartStore.getMaxQuantity(variation) <= 0
                        "
                        input-class="w-[60px]"
                        :model-value="quantity"
                        @update:model-value="
                            (value) => emit('update:quantity', value)
                        "
                        :min="0"
                        :max="cartStore.getMaxQuantity(variation)"
                        show-buttons
                        button-layout="horizontal"
                        :data-testid="`variation-quantity-${variation.id}`"
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
        </template>
    </Card>
</template>
