<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import type { Order } from '@/types'
import { Head } from '@inertiajs/vue3'
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
        </div>
    </div>
</template>
