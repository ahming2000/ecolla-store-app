<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import { show as showItem } from '@/routes/shop/item'
import type { Item } from '@/types'
import { router } from '@inertiajs/vue3'
import Card from 'primevue/card'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        item: Item
        class?: string
    }>(),
    {
        class: '',
    }
)

const { locale: activeLanguage, t } = useI18n()

const itemName = computed(() => {
    return activeLanguage.value === 'en' && props.item.name_en
        ? props.item.name_en
        : props.item.name
})

const itemVariationPriceRange = computed(() => {
    const priceList = props.item.variations
        .filter((variation) => variation.stock > 0)
        .map((variation) => variation.final_price)

    return `RM${priceList[0]} - RM${priceList[0]}`
})

const onImageError = (event: Event): void => {
    const image = event.currentTarget

    if (!(image instanceof HTMLImageElement)) {
        return
    }

    image.onerror = null
    image.src = fallbackImage
}

const onClick = () => {
    router.visit(showItem(props.item))
}
</script>

<template>
    <Card
        :key="item.id"
        class="cursor-pointer"
        :class="props.class"
        @click="onClick"
    >
        <template #header>
            <div
                class="flex aspect-square items-center justify-center overflow-hidden"
            >
                <img
                    :src="item.cover_image ?? fallbackImage"
                    :alt="`${itemName} - ${t('common.alt.item-image')}`"
                    class="h-full w-full object-cover"
                    data-testid="shop-item-image"
                    @error="onImageError"
                />
            </div>
        </template>

        <template #title>
            {{ itemName }}
        </template>

        <template #content>
            {{ itemVariationPriceRange }}
        </template>

        <template #footer>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="pi pi-box"></i>
                    <span class="ml-1">{{ item.total_stock }}</span>
                </div>

                <div class="flex items-center">
                    <i class="pi pi-eye"></i>
                    <span class="ml-1">{{ item.view_count }}</span>
                </div>
            </div>
        </template>
    </Card>
</template>
