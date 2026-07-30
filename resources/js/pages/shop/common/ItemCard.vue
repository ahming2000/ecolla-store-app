<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import SmartImage from '@/components/image/SmartImage.vue'
import { getLocalizedName } from '@/libraries/i18n/language'
import { show as showItem } from '@/routes/shop/item'
import type { Item } from '@/types'
import { Link } from '@inertiajs/vue3'
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
    return getLocalizedName(props.item, activeLanguage.value)
})

const cheapestInStockVariation = computed(() => {
    return props.item.variations.reduce<Item['variations'][number] | null>(
        (cheapestVariation, variation) => {
            if (variation.stock <= 0) {
                return cheapestVariation
            }

            if (
                cheapestVariation === null ||
                variation.final_price < cheapestVariation.final_price
            ) {
                return variation
            }

            return cheapestVariation
        },
        null
    )
})
</script>

<template>
    <Link
        :href="showItem(item)"
        class="block text-inherit no-underline"
        :aria-label="itemName"
    >
        <Card
            :key="item.id"
            :data-testid="`shop-item-card-${item.id}`"
            class="h-full cursor-pointer"
            :class="props.class"
            :pt="{
                body: {
                    class: '!gap-2 !p-3',
                },
                caption: {
                    class: '!gap-1',
                },
            }"
        >
            <template #header>
                <div
                    class="flex aspect-square items-center justify-center overflow-hidden"
                >
                    <SmartImage
                        :alt="`${itemName} - ${t('common.alt.item-image')}`"
                        :fallback-src="fallbackImage"
                        image-class="h-full w-full object-cover"
                        :src="item.cover_image"
                        :thumbnail-src="item.cover_thumbnail"
                        data-testid="shop-item-image"
                    />
                </div>
            </template>

            <template #title>
                <div class="line-clamp-2 text-sm font-semibold leading-snug">
                    {{ itemName }}
                </div>
            </template>

            <template #content>
                <div class="text-sm font-medium">
                    {{
                        cheapestInStockVariation?.final_price_text ??
                        t('shop.item.sold-out')
                    }}
                </div>
            </template>

            <template #footer>
                <div class="flex items-center gap-1">
                    <div
                        class="flex w-full items-center justify-between text-xs text-surface-600"
                    >
                        <div class="flex items-center gap-1">
                            <i class="pi pi-box"></i>
                            <span>{{ item.total_stock }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <i class="pi pi-eye"></i>
                            <span>{{ item.view_count }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </Link>
</template>
