<script setup lang="ts">
import landingHeroImage from '@/assets/images/landing/ecolla-shop.jpeg'
import shopClipartImage from '@/assets/images/landing/shop-clipart.jpeg'
import Shop from '@/layouts/Shop.vue'
import ItemCard from '@/pages/shop/common/ItemCard.vue'
import LinkCard from '@/pages/shop/landing/LinkCard.vue'
import { page as cartPage } from '@/routes/shop/cart'
import { page as itemPage } from '@/routes/shop/item'
import { page as paymentMethodPage } from '@/routes/shop/payment-method'
import type { Item } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Carousel from 'primevue/carousel'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

defineProps<{
    highestViewCountItems: Item[]
    highestSoldCountItems: Item[]
}>()

const { t } = useI18n()

// TODO not working for this version of preset
const responsiveOptions = ref([
    {
        breakpoint: '1400px',
        numVisible: 7,
        numScroll: 1,
    },
    {
        breakpoint: '1200px',
        numVisible: 6,
        numScroll: 1,
    },
    {
        breakpoint: '992px',
        numVisible: 5,
        numScroll: 1,
    },
    {
        breakpoint: '768px',
        numVisible: 4,
        numScroll: 1,
    },
    {
        breakpoint: '576px',
        numVisible: 3,
        numScroll: 1,
    },
])
</script>

<template>
    <div>
        <Head :title="t('landing.title')" />

        <div
            :style="{ backgroundImage: `url(${landingHeroImage})` }"
            class="bg-cover bg-no-repeat bg-top py-12 h-[18em] text-center"
        >
            <div class="text-white text-[35px] mb-5">
                <span>{{ t('landing.hero.before-brand') }}</span>
                <span class="text-[#f02b73] inline mx-2">
                    {{ t('landing.hero.brand') }}
                </span>
                <span>{{ t('landing.hero.after-brand') }}</span>
            </div>

            <Button
                class="tada-animation delay-1s"
                icon="pi pi-arrow-circle-right"
                severity="success"
                :label="t('landing.hero.browse-items')"
                @click="router.visit(itemPage())"
            />
        </div>

        <div class="container mx-auto px-2 md:px-0 my-3 space-y-1">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <div
                    class="md:col-span-2 my-auto animate-fadeinleft animate-once animate-duration-1000"
                >
                    <div class="text-3xl md:text-4xl font-bold mb-3">
                        {{ t('landing.about.title') }}
                    </div>

                    <p class="text-sm md:text-md">
                        {{ t('landing.about.description') }}
                    </p>
                </div>

                <img
                    class="animate-fadeinright animate-once animate-duration-1000"
                    :src="shopClipartImage"
                    :alt="t('landing.about.image-alt')"
                />
            </div>

            <div class="animate-fadeindown animate-once animate-duration-1000">
                <div class="text-3xl mb-3">
                    {{ t('landing.carousels.most-viewed') }}
                </div>

                <Carousel
                    :value="highestViewCountItems"
                    :num-visible="6"
                    :num-scroll="1"
                    :responsive-options="responsiveOptions"
                >
                    <template #item="{ data }">
                        <ItemCard class="mx-1" :item="data" />
                    </template>
                </Carousel>
            </div>

            <div class="animate-fadeindown animate-once animate-duration-1000">
                <div class="text-3xl mb-3">
                    {{ t('landing.carousels.best-selling') }}
                </div>

                <Carousel
                    :value="highestSoldCountItems"
                    :num-visible="6"
                    :num-scroll="1"
                    :responsive-options="responsiveOptions"
                >
                    <template #item="{ data }">
                        <ItemCard class="mx-1" :item="data" />
                    </template>
                </Carousel>
            </div>

            <div class="animate-fadeindown animate-once animate-duration-1000">
                <div class="text-3xl mb-3">
                    {{ t('landing.links.title') }}
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                    <LinkCard
                        :label="t('landing.links.payment-methods')"
                        icon="pi pi-wallet"
                        :href="paymentMethodPage.url()"
                        background-color="#1c713e"
                    />

                    <LinkCard
                        :label="t('landing.links.all-items')"
                        icon="pi pi-list"
                        :href="itemPage.url()"
                        background-color="#e968a8"
                    />

                    <LinkCard
                        :label="t('landing.links.cart')"
                        icon="pi pi-wallet"
                        :href="cartPage.url()"
                        background-color="#8c9126"
                    />

                    <LinkCard
                        :label="t('landing.links.contact')"
                        icon="pi pi-wallet"
                        href="https://wa.link/fcfum1"
                        background-color="#2fe577"
                        :open-in-new-tab="true"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes tada {
    0% {
        transform: scale(1);
    }
    10%,
    20% {
        transform: scale(0.9) rotate(-3deg);
    }
    30%,
    50%,
    70%,
    90% {
        transform: scale(1.1) rotate(3deg);
    }
    40%,
    60%,
    80% {
        transform: scale(1.1) rotate(-3deg);
    }
    100% {
        transform: scale(1) rotate(0);
    }
}

.tada-animation {
    animation: tada 1s ease-in-out;
}

.delay-1s {
    animation-delay: 1s;
}
</style>
