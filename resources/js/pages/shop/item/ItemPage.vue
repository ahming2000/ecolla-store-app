<script setup lang="ts">
import SmartImage from '@/components/image/SmartImage.vue'
import Shop from '@/layouts/Shop.vue'
import Notification from '@/libraries/primevue/toast/Notification'
import ItemCard from '@/pages/shop/common/ItemCard.vue'
import ItemPageBreadcrumb from '@/pages/shop/item/Breadcrumb.vue'
import ItemPageVariationCard from '@/pages/shop/item/VariationCard.vue'
import { page as itemPage } from '@/routes/shop/item'
import { useCartStore } from '@/stores/cart.store'
import type { Item } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Carousel, { type CarouselPassThroughOptions } from 'primevue/carousel'
import Galleria from 'primevue/galleria'
import { useToast } from 'primevue/usetoast'
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
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

const isItemOutOfStock = computed(() => props.item.total_stock <= 0)

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
        numScroll: 5,
    },
    {
        breakpoint: '1200px',
        numVisible: 4,
        numScroll: 4,
    },
    {
        breakpoint: '640px',
        numVisible: 3,
        numScroll: 3,
    },
    {
        breakpoint: '480px',
        numVisible: 2,
        numScroll: 2,
    },
])

const recommendationCarouselPassThrough: CarouselPassThroughOptions = {
    item: { class: '!grow-0' },
    itemClone: { class: '!grow-0' },
}

const activeImageIndex = ref(0)
const isImagePreviewVisible = ref(false)
const galleryContainerElement = ref<HTMLElement | null>(null)
const galleryHeight = ref('auto')
let galleryResizeObserver: ResizeObserver | null = null

const itemDetailGridStyle = computed<Record<string, string>>(() => {
    return {
        '--item-gallery-height': galleryHeight.value,
    }
})

const openImagePreview = (): void => {
    isImagePreviewVisible.value = true
}

const onSelectVariationImage = (
    variation: Item['variations'][number]
): void => {
    const variationImageIndex = props.item.all_images.findIndex((image) => {
        return (
            image.variation_id === variation.id ||
            (image.variation_id === undefined &&
                image.id === variation.image_id)
        )
    })

    if (variationImageIndex >= 0) {
        activeImageIndex.value = variationImageIndex
        galleryContainerElement.value?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    }
}

onMounted(() => {
    if (!galleryContainerElement.value) {
        return
    }

    galleryResizeObserver = new ResizeObserver(([entry]) => {
        if (entry) {
            galleryHeight.value = `${entry.borderBoxSize[0]?.blockSize ?? entry.target.getBoundingClientRect().height}px`
        }
    })
    galleryResizeObserver.observe(galleryContainerElement.value)
})

onBeforeUnmount(() => {
    galleryResizeObserver?.disconnect()
})

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
                :style="itemDetailGridStyle"
            >
                <div
                    ref="galleryContainerElement"
                    class="self-start overflow-hidden rounded-xl bg-white shadow-sm lg:col-span-2"
                    data-testid="item-image-galleria"
                >
                    <Galleria
                        v-model:active-index="activeImageIndex"
                        :auto-play="!isImagePreviewVisible"
                        :circular="true"
                        :num-visible="5"
                        :responsive-options="galleriaResponsiveOptions"
                        :transition-interval="5000"
                        :value="item.all_images"
                    >
                        <template #item="{ item }">
                            <button
                                type="button"
                                class="flex h-[min(78vw,22rem)] w-full cursor-zoom-in items-center justify-center overflow-hidden bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-primary lg:h-96"
                                :aria-label="
                                    t('shop-item.images.preview', {
                                        name: item.name,
                                    })
                                "
                                data-testid="item-image-preview"
                                @click="openImagePreview"
                            >
                                <SmartImage
                                    :image="item"
                                    :alt="`${item.name} - ${t('common.alt.item-image')}`"
                                    full
                                    image-class="h-full w-full object-contain"
                                    loading="eager"
                                />
                            </button>
                        </template>

                        <template #thumbnail="{ item }">
                            <SmartImage
                                :image="item"
                                :alt="`${item.name} - ${t('common.alt.item-image')}`"
                                image-class="h-16 w-full object-cover"
                                data-testid="item-image-thumbnail"
                            />
                        </template>
                    </Galleria>
                </div>

                <Galleria
                    v-model:active-index="activeImageIndex"
                    v-model:visible="isImagePreviewVisible"
                    :circular="true"
                    :full-screen="true"
                    :num-visible="5"
                    :responsive-options="galleriaResponsiveOptions"
                    :show-item-navigators="true"
                    :value="item.all_images"
                    :pt="{
                        mask: {
                            'data-testid': 'item-image-fullscreen-gallery',
                        },
                    }"
                >
                    <template #item="{ item }">
                        <SmartImage
                            :image="item"
                            :alt="`${item.name} - ${t('common.alt.item-image')}`"
                            full
                            image-class="block max-h-[calc(100vh-12rem)] max-w-[90vw] object-contain"
                            loading="eager"
                            data-testid="item-image-fullscreen-preview"
                        />
                    </template>

                    <template #thumbnail="{ item }">
                        <SmartImage
                            :image="item"
                            :alt="`${item.name} - ${t('common.alt.item-image')}`"
                            image-class="h-16 w-full object-cover"
                            data-testid="item-image-fullscreen-thumbnail"
                        />
                    </template>
                </Galleria>

                <div
                    class="space-y-3 md:flex md:h-[var(--item-gallery-height)] md:min-h-0 md:flex-col md:gap-3 md:space-y-0 md:overflow-hidden lg:col-span-3"
                    data-testid="item-variation-panel"
                >
                    <div class="shrink-0 space-y-2">
                        <div class="flex flex-wrap gap-1">
                            <Link
                                v-for="category in item.categories"
                                :key="category.id"
                                :href="
                                    itemPage({
                                        query: { category: category.id },
                                    })
                                "
                                class="rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                                :data-testid="`item-category-link-${category.id}`"
                            >
                                <Badge
                                    :value="getCategoryName(category)"
                                    class="cursor-pointer px-2"
                                />
                            </Link>
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

                    <div
                        class="space-y-2 md:min-h-0 md:flex-1 md:overflow-y-auto md:overscroll-contain md:pe-1"
                        data-testid="variation-scroll-container"
                    >
                        <ItemPageVariationCard
                            v-for="variation in item.variations"
                            :key="variation.id"
                            :variation="variation"
                            v-model:quantity="quantityForm[variation.id]"
                            @select-image="onSelectVariationImage"
                        />
                    </div>

                    <div class="flex shrink-0 justify-center">
                        <Button
                            :disabled="isItemOutOfStock"
                            icon="pi pi-cart-plus"
                            :label="
                                isItemOutOfStock
                                    ? t('shop.item.sold-out')
                                    : t('shop-item.cart.add')
                            "
                            data-testid="add-to-cart-button"
                            @click="onAddToCart"
                        />
                    </div>
                </div>
            </div>

            <Card>
                <template #title>
                    <span class="font-bold">
                        {{ t('shop-item.description') }}
                    </span>
                </template>

                <template #content>
                    <p>{{ item.desc }}</p>
                </template>
            </Card>

            <section
                v-if="similarItems.length > 0"
                class="space-y-2"
                data-testid="similar-items-section"
            >
                <h2 class="text-2xl font-bold">
                    {{ t('shop-item.recommendations.similar') }}
                </h2>

                <Carousel
                    :num-scroll="6"
                    :num-visible="6"
                    :pt="recommendationCarouselPassThrough"
                    :responsive-options="recommendationResponsiveOptions"
                    :value="similarItems"
                    data-testid="similar-items-carousel"
                >
                    <template #item="{ data }">
                        <ItemCard class="mx-1 sm:h-full" :item="data" />
                    </template>
                </Carousel>
            </section>

            <section
                v-if="randomItems.length > 0"
                class="space-y-2"
                data-testid="recommended-items-section"
            >
                <h2 class="text-2xl font-bold">
                    {{ t('shop-item.recommendations.you-may-like') }}
                </h2>

                <Carousel
                    :num-scroll="6"
                    :num-visible="6"
                    :pt="recommendationCarouselPassThrough"
                    :responsive-options="recommendationResponsiveOptions"
                    :value="randomItems"
                    data-testid="recommended-items-carousel"
                >
                    <template #item="{ data }">
                        <ItemCard class="mx-1 sm:h-full" :item="data" />
                    </template>
                </Carousel>
            </section>
        </div>
    </div>
</template>
