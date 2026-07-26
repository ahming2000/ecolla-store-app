<script setup lang="ts">
import IftaInputText from '@/components/input/IftaInputText.vue'
import { DELIVERY, getDeliveryModeLabel } from '@/enums/DeliveryMode'
import { parseFormError } from '@/libraries/axios/common/parser'
import type { CheckoutFormData } from '@/libraries/axios/shop/cart'
import { checkoutCart } from '@/libraries/axios/shop/cart'
import { getAllPaymentMethod } from '@/libraries/axios/shop/payment-method'
import PaymentMethodSelect from '@/pages/shop/checkout/PaymentMethodSelect.vue'
import ReceiptUploader from '@/pages/shop/checkout/ReceiptUploader.vue'
import { successfulPage } from '@/routes/shop/cart'
import { useCartStore } from '@/stores/cart.store'
import type { PaymentMethod } from '@/types'
import { router, useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const { t } = useI18n()

const paymentMethods = ref<PaymentMethod[]>([])

const form = useForm<CheckoutFormData>({
    cus_name: null,
    cus_phone: null,
    cus_address: null,
    receipt_image: null,
    payment_method: null,
})

const onSubmit = async (): Promise<void> => {
    try {
        const order = await checkoutCart(cartStore.cart.data(), form.data())
        form.reset()
        cartStore.reset()
        router.visit(successfulPage(order.id))
    } catch (e) {
        form.errors = parseFormError(e)
        console.error(e)
    }
}

onMounted(async () => {
    try {
        paymentMethods.value = await getAllPaymentMethod()

        if (paymentMethods.value.length > 0) {
            form.payment_method = paymentMethods.value[0]
        }
    } catch (e) {
        console.error(e)
    }
})
</script>

<template>
    <Card>
        <template #title>
            {{
                t('shop.checkout.form-title', {
                    mode: getDeliveryModeLabel(t, cartStore.cart.deliveryMode),
                })
            }}
        </template>

        <template #content>
            <form @submit.prevent="onSubmit" class="space-y-2">
                <IftaInputText
                    v-if="cartStore.cart.deliveryMode === DELIVERY"
                    v-model="form.cus_name"
                    autofocus
                    input-class="w-full"
                    input-id="name"
                    :error="form.errors.cus_name"
                    :label="t('shop.checkout.fields.name')"
                    :placeholder="t('shop.checkout.placeholders.name')"
                />

                <IftaInputText
                    v-model="form.cus_phone"
                    input-class="w-full"
                    input-id="phone"
                    :label="t('shop.checkout.fields.phone')"
                    :placeholder="t('shop.checkout.placeholders.phone')"
                />

                <IftaInputText
                    v-if="cartStore.cart.deliveryMode === DELIVERY"
                    v-model="form.cus_address"
                    input-class="w-full"
                    input-id="address"
                    :error="form.errors.cus_address"
                    :label="t('shop.checkout.fields.address')"
                    :placeholder="t('shop.checkout.placeholders.address')"
                />

                <PaymentMethodSelect
                    v-model="form.payment_method"
                    :payment-methods="paymentMethods"
                    :error="form.errors.payment_method"
                />

                <ReceiptUploader
                    v-model="form.receipt_image"
                    :error="form.errors.receipt_image"
                />

                <div class="text-center">
                    <Button
                        icon="pi pi-arrow-circle-right"
                        :label="t('shop.checkout.submit')"
                        type="submit"
                    />
                </div>
            </form>
        </template>
    </Card>
</template>
