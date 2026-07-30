<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import { page as orderTrackingPage } from '@/routes/shop/order-tracking'
import type { Order } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import Button from 'primevue/button'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

defineProps<{
    order: Order
}>()

const { t } = useI18n()
</script>

<template>
    <div>
        <Head :title="t('shop.checkout.success.title')" />

        <div class="container mx-auto my-10 px-3 text-center">
            <h1 class="text-3xl font-bold">
                {{ t('shop.checkout.success.heading') }}
            </h1>

            <p class="mt-3">
                {{
                    t('shop.checkout.success.reference', {
                        reference: order.reference_num,
                    })
                }}
            </p>

            <Link
                class="mt-5 inline-block"
                :href="
                    orderTrackingPage({
                        query: { reference: order.reference_num },
                    })
                "
            >
                <Button
                    icon="pi pi-map-marker"
                    :label="t('shop.checkout.success.track')"
                />
            </Link>
        </div>
    </div>
</template>
