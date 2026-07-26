<script setup lang="ts">
import {
    resolvePaymentMethodIcon,
    resolvePaymentMethodQrCode,
} from '@/assets/payment-method-images'
import type { PaymentMethod } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Image from 'primevue/image'
import type { HTMLAttributes } from 'vue'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        modelValue?: PaymentMethod | null
        paymentMethods?: PaymentMethod[]
        class?: HTMLAttributes['class']
        error?: string
    }>(),
    {
        modelValue: null,
        paymentMethods: () => [],
        class: undefined,
        error: undefined,
    }
)

const emit = defineEmits<{
    'update:modelValue': [paymentMethod: PaymentMethod]
}>()

const isPaymentQrCodeDialogVisible = ref(false)
const { t } = useI18n()

const onPaymentSelect = (paymentMethod: PaymentMethod): void => {
    emit('update:modelValue', paymentMethod)
}
</script>

<template>
    <div :class="props.class" v-if="paymentMethods.length !== 0">
        <div class="text-lg">{{ t('shop.checkout.payment.select') }}</div>

        <div
            class="whitespace-nowrap overflow-x-auto scroll-touch inline-flex space-x-1"
        >
            <img
                v-for="paymentMethod in paymentMethods"
                :key="paymentMethod.id"
                :src="resolvePaymentMethodIcon(paymentMethod.icon_img_path)"
                :alt="paymentMethod.name"
                @click="() => onPaymentSelect(paymentMethod)"
                style="width: 109px"
                :class="{ selected: paymentMethod.id === modelValue?.id }"
            />
        </div>

        <small v-show="error" class="text-red-500">
            {{ error }}
        </small>

        <div class="text-center">
            <Button
                icon="pi pi-image"
                :label="t('shop.checkout.payment.show-qr-code')"
                @click="isPaymentQrCodeDialogVisible = true"
            />
        </div>
    </div>

    <Dialog
        v-model:visible="isPaymentQrCodeDialogVisible"
        v-if="!!modelValue"
        :header="modelValue.name"
        :draggable="false"
        modal
    >
        <Image
            :src="resolvePaymentMethodQrCode(modelValue.qr_code_img_path)"
            :alt="modelValue.name"
        />
    </Dialog>
</template>

<style scoped>
@reference "../../../../css/app.css";

.selected {
    @apply border-2 border-sky-400;
}
</style>
