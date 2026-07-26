<script setup lang="ts">
import { useCartStore } from '@/stores/cart.store'
import type { Variation } from '@/types'
import Badge from 'primevue/badge'
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
                class="flex md:flex-col lg:flex-row justify-between items-center space-y-3"
            >
                <div class="self-start flex flex-col">
                    <div class="font-bold">{{ variationName }}</div>

                    <div class="flex items-center space-x-2">
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

                        <Badge
                            v-if="variation.stock <= 0"
                            :value="t('shop.item.sold-out')"
                            class="px-2"
                            severity="danger"
                        />
                    </div>
                </div>

                <InputNumber
                    input-class="w-[60px]"
                    :model-value="quantity"
                    @update:model-value="
                        (value) => emit('update:quantity', value)
                    "
                    :min="0"
                    :max="cartStore.getMaxQuantity(variation)"
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
        </template>
    </Card>
</template>
