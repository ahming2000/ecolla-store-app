<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import Notification from '@/libraries/primevue/toast/Notification'
import ItemCard from '@/pages/shop/common/ItemCard.vue'
import ItemPageBreadcrumb from '@/pages/shop/item/Breadcrumb.vue'
import ItemPageVariationCard from '@/pages/shop/item/VariationCard.vue'
import { useCartStore } from '@/stores/cart.store'
import type { Item } from '@/types'
import { Head } from '@inertiajs/vue3'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Carousel, { type CarouselPassThroughOptions } from 'primevue/carousel'
import Galleria from 'primevue/galleria'
import { useToast } from 'primevue/usetoast'
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

const props = defineProps<{
    item: Item
    similarItems: Item[]
    randomItems: Item[]
}>()

const toast = Notification.init(useToast())
const cartStore = useCartStore()
const { locale, t } = useI18n()

const itemName = computed(() => {
    return locale.value === 'en' && props.item.name_en
        ? props.item.name_en
        : props.item.name
})

const getCategoryName = (category: Item['categories'][number]): string => {
    return locale.value === 'en' && category.name_en
        ? category.name_en
        : category.name
}

const galleriaResponsiveOptions = ref([
    {
        breakpoint: '768px',
        numVisible: 4,
    },
    {
        breakpoint: '480px',
        numVisible: 3,
    },
])

const recommendationResponsiveOptions = ref([
    {
        breakpoint: '1400px',
        numVisible: 5,
        numScroll: 1,
    },
    {
        breakpoint: '1200px',
        numVisible: 4,
        numScroll: 1,
    },
    {
        breakpoint: '640px',
        numVisible: 3,
        numScroll: 1,
    },
    {
        breakpoint: '480px',
        numVisible: 2,
        numScroll: 1,
    },
])

const recommendationCarouselPassThrough: CarouselPassThroughOptions = {
    item: { class: '!grow-0' },
    itemClone: { class: '!grow-0' },
}

const quantityForm = reactive<Record<string, number>>(
    props.item.variations.reduce<Record<string, number>>((acc, variation) => {
        acc[variation.id.toString()] = 0
        return acc
    }, {})
)

const onAddToCart = (): void => {
    for (const variationId of Object.keys(quantityForm)) {
        if (quantityForm[variationId] > 0) {
            const variation = props.item.variations.find((v) => {
                return v.id?.toString() === variationId?.toString()
            })

            if (variation) {
                cartStore.addItem(
                    props.item,
                    variation,
                    quantityForm[variationId]
                )
            }
        }
    }

    toast.info(t('shop-item.cart.added'), t('shop-item.cart.title'))
}
</script>

<template>
    <div>
        <Head :title="itemName" />

        <div class="container mx-auto my-3 space-y-6 px-3">
            <ItemPageBreadcrumb :item="item" />

            <div
                class="grid grid-cols-1 items-start gap-5 md:grid-cols-2 lg:grid-cols-5"
            >
                <Galleria
                    :auto-play="true"
                    :circular="true"
                    :num-visible="5"
                    :responsive-options="galleriaResponsiveOptions"
                    :transition-interval="5000"
                    :value="item.all_images"
                    container-class="self-start overflow-hidden rounded-xl bg-white shadow-sm lg:col-span-2"
                    data-testid="item-image-galleria"
                >
                    <template #item="{ item }">
                        <div
                            class="flex h-[min(78vw,22rem)] w-full items-center justify-center overflow-hidden bg-gray-50 lg:h-96"
                            data-testid="item-image-preview"
                        >
                            <img
                                :src="item.src ?? undefined"
                                :alt="`${item.name} - ${t('common.alt.item-image')}`"
                                class="h-full w-full object-contain"
                            />
                        </div>
                    </template>

                    <template #thumbnail="{ item }">
                        <img
                            :src="item.src ?? undefined"
                            :alt="`${item.name} - ${t('common.alt.item-image')}`"
                            class="h-16 w-full object-cover"
                        />
                    </template>
                </Galleria>

                <div class="space-y-3 lg:col-span-3">
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-1">
                            <Badge
                                v-for="category in item.categories"
                                :key="category.id"
                                :value="getCategoryName(category)"
                                class="px-2"
                            />
                        </div>

                        <div class="text-3xl">{{ itemName }}</div>

                        <div class="text-gray-500 space-x-1">
                            <span>
                                {{
                                    t('shop-item.stats.sold', {
                                        count: item.sold_count,
                                    })
                                }}
                            </span>
                            <span>|</span>
                            <span>
                                {{
                                    t('shop-item.stats.views', {
                                        count: item.view_count,
                                    })
                                }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <ItemPageVariationCard
                            v-for="variation in item.variations"
                            :key="variation.id"
                            :variation="variation"
                            v-model:quantity="quantityForm[variation.id]"
                        />
                    </div>

                    <div class="flex justify-center">
                        <Button
                            icon="pi pi-cart-plus"
                            :label="t('shop-item.cart.add')"
                            @click="onAddToCart"
                        />
                    </div>
                </div>
            </div>

            <Card>
                <template #title>{{ t('shop-item.description') }}</template>

                <template #content>
                    <p>{{ item.desc }}</p>
                </template>
            </Card>

            <section
                v-if="similarItems.length > 0"
                class="space-y-2"
                data-testid="similar-items-section"
            >
                <h2 class="text-2xl">
                    {{ t('shop-item.recommendations.similar') }}
                </h2>

                <Carousel
                    :num-scroll="1"
                    :num-visible="6"
                    :pt="recommendationCarouselPassThrough"
                    :responsive-options="recommendationResponsiveOptions"
                    :value="similarItems"
                    data-testid="similar-items-carousel"
                >
                    <template #item="{ data }">
                        <ItemCard class="mx-1 h-full" :item="data" />
                    </template>
                </Carousel>
            </section>

            <section
                v-if="randomItems.length > 0"
                class="space-y-2"
                data-testid="recommended-items-section"
            >
                <h2 class="text-2xl">
                    {{ t('shop-item.recommendations.you-may-like') }}
                </h2>

                <Carousel
                    :num-scroll="1"
                    :num-visible="6"
                    :pt="recommendationCarouselPassThrough"
                    :responsive-options="recommendationResponsiveOptions"
                    :value="randomItems"
                    data-testid="recommended-items-carousel"
                >
                    <template #item="{ data }">
                        <ItemCard class="mx-1 h-full" :item="data" />
                    </template>
                </Carousel>
            </section>
        </div>
    </div>
</template>
