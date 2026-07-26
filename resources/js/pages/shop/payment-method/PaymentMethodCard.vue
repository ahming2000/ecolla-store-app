<script setup lang="ts">
import {
    resolvePaymentMethodIcon,
    resolvePaymentMethodQrCode,
} from '@/assets/payment-method-images'
import type { PaymentMethod } from '@/types'
import Card from 'primevue/card'
import Dialog from 'primevue/dialog'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineProps<{
    paymentMethod: PaymentMethod
}>()

const visible = ref(false)
const { t } = useI18n()
</script>

<template>
    <Card>
        <template #content>
            <div class="flex items-center">
                <img
                    :src="resolvePaymentMethodIcon(paymentMethod.icon_img_path)"
                    :alt="paymentMethod.name"
                    :style="{ height: '100px', width: '100px' }"
                    loading="lazy"
                />

                <div class="ml-5">
                    <div class="text-2xl">
                        {{ paymentMethod.name }}
                    </div>

                    <div
                        class="text-sm text-blue-500 underline cursor-pointer"
                        @click="visible = true"
                    >
                        {{ t('shop.payment-methods.view-qr-code') }}
                    </div>
                </div>
            </div>
        </template>
    </Card>

    <Dialog
        v-model:visible="visible"
        :header="paymentMethod.name"
        :draggable="false"
        modal
    >
        <img
            :src="resolvePaymentMethodQrCode(paymentMethod.qr_code_img_path)"
            :alt="paymentMethod.name"
            class="h-full"
            loading="lazy"
        />
    </Dialog>
</template>
