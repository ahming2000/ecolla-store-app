<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import PaymentMethodCard from '@/pages/shop/payment-method/PaymentMethodCard.vue'
import type { PaymentMethod } from '@/types'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

defineProps<{
    paymentMethods: PaymentMethod[]
}>()

const { t } = useI18n()
</script>

<template>
    <div>
        <Head :title="t('shop.payment-methods.title')" />

        <div class="container mx-auto px-3 md:px-24 lg:px-40 xl:px-56 my-3">
            <div class="text-3xl">{{ t('shop.payment-methods.title') }}</div>

            <div class="my-3">
                {{ t('shop.payment-methods.description') }}
            </div>

            <div class="space-y-2">
                <PaymentMethodCard
                    v-for="paymentMethod in paymentMethods"
                    :key="paymentMethod.id"
                    :payment-method="paymentMethod"
                />
            </div>
        </div>
    </div>
</template>
