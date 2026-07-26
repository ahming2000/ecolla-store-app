<script setup lang="ts">
import { SUPERVISOR } from '@/enums/AccessLevel'
import Admin from '@/layouts/Admin.vue'
import Notification from '@/libraries/primevue/toast/Notification'
import ElementTagCard from '@/pages/admin/setting/ElementTagCard.vue'
import { update as updateFreeShipping } from '@/routes/admin/setting/free-shipping'
import { update as updateShippingFee } from '@/routes/admin/setting/shipping'
import type { AppPageProps, Category, Origin, ShippingSettings } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import ToggleSwitch from 'primevue/toggleswitch'
import { useToast } from 'primevue/usetoast'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

const props = withDefaults(
    defineProps<{
        origins?: Origin[]
        categories?: Category[]
        shipping?: ShippingSettings
    }>(),
    {
        origins: () => [],
        categories: () => [],
        shipping: () => ({
            fee: 0,
            freeShipping: {
                isActivated: false,
                threshold: 0,
                description: '',
            },
        }),
    }
)

const onUpdateOrigin = () => {}
const onDeleteOrigin = () => {}
const onUpdateCategory = () => {}
const onDeleteCategory = () => {}

const { t } = useI18n()
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())

const shippingFeeForm = useForm<{ shipping_fee: number | null }>({
    shipping_fee: props.shipping.fee,
})
const freeShippingForm = useForm<{
    is_activated: boolean
    threshold: number | null
    description: string
}>({
    is_activated: props.shipping.freeShipping.isActivated,
    threshold: props.shipping.freeShipping.threshold,
    description: props.shipping.freeShipping.description,
})

const canUpdateSettings = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= SUPERVISOR
})

const onUpdateShippingFee = (): void => {
    if (!canUpdateSettings.value) {
        return
    }

    shippingFeeForm.submit(updateShippingFee(), {
        preserveScroll: true,
        onSuccess: () => {
            shippingFeeForm.defaults(shippingFeeForm.data())
            toast.success(
                t('admin.settings.shipping-updated-success'),
                t('common.notifications.success')
            )
        },
        onError: () => {
            toast.error(
                t('admin.settings.shipping-update-failed'),
                t('common.notifications.error')
            )
        },
    })
}

const onUpdateFreeShipping = (): void => {
    if (!canUpdateSettings.value) {
        return
    }

    freeShippingForm.submit(updateFreeShipping(), {
        preserveScroll: true,
        onSuccess: () => {
            freeShippingForm.defaults(freeShippingForm.data())
            toast.success(
                t('admin.settings.free-shipping-updated-success'),
                t('common.notifications.success')
            )
        },
        onError: () => {
            toast.error(
                t('admin.settings.free-shipping-update-failed'),
                t('common.notifications.error')
            )
        },
    })
}
</script>

<template>
    <Head :title="t('admin.settings.title')" />

    <div class="container mx-auto my-3">
        <div class="md:mx-24 lg:mx-48 xl:mx-80 space-y-5">
            <div class="text-3xl font-bold">
                {{ t('admin.settings.title') }}
            </div>

            <div data-testid="origin-settings">
                <div class="flex justify-between items-center mb-2">
                    <div class="text-2xl">
                        {{ t('admin.settings.origin-filter') }}
                    </div>

                    <Button
                        icon="pi pi-plus"
                        :label="t('admin.settings.add-origin')"
                        type="submit"
                        size="small"
                    />
                </div>

                <ElementTagCard
                    :elements="origins"
                    :subject="t('admin.settings.origin')"
                    :on-update="onUpdateOrigin"
                    :on-delete="onDeleteOrigin"
                />
            </div>

            <div data-testid="category-settings">
                <div class="flex justify-between items-center mb-2">
                    <div class="text-2xl">
                        {{ t('admin.settings.category-filter') }}
                    </div>

                    <Button
                        icon="pi pi-plus"
                        :label="t('admin.settings.add-category')"
                        type="submit"
                        size="small"
                    />
                </div>

                <ElementTagCard
                    :elements="categories"
                    :subject="t('admin.settings.category')"
                    :on-update="onUpdateCategory"
                    :on-delete="onDeleteCategory"
                />
            </div>

            <Card>
                <template #title>{{ t('admin.settings.shipping') }}</template>

                <template #content>
                    <form @submit.prevent="onUpdateShippingFee">
                        <InputGroup>
                            <InputGroupAddon>RM</InputGroupAddon>
                            <InputNumber
                                v-model="shippingFeeForm.shipping_fee"
                                :aria-label="
                                    t('admin.settings.shipping-fee-input')
                                "
                                :disabled="!canUpdateSettings"
                                :invalid="!!shippingFeeForm.errors.shipping_fee"
                                :max="999999.99"
                                :max-fraction-digits="2"
                                :min="0"
                                :min-fraction-digits="2"
                                :placeholder="t('admin.settings.price')"
                                input-id="shipping-fee"
                            />
                            <Button
                                data-testid="save-shipping-fee"
                                :disabled="!canUpdateSettings"
                                :label="t('common.actions.save')"
                                :loading="shippingFeeForm.processing"
                                icon="pi pi-save"
                                type="submit"
                            />
                        </InputGroup>

                        <small
                            v-if="shippingFeeForm.errors.shipping_fee"
                            class="text-red-700"
                        >
                            {{ shippingFeeForm.errors.shipping_fee }}
                        </small>
                    </form>
                </template>
            </Card>

            <Card>
                <template #content>
                    <form @submit.prevent="onUpdateFreeShipping">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-2">
                                <div class="text-2xl">
                                    {{ t('admin.settings.shipping-discount') }}
                                </div>
                                <ToggleSwitch
                                    v-model="freeShippingForm.is_activated"
                                    :aria-label="
                                        t('admin.settings.free-shipping-toggle')
                                    "
                                    :disabled="!canUpdateSettings"
                                    input-id="free-shipping-activated"
                                />
                            </div>

                            <Button
                                data-testid="save-free-shipping"
                                :disabled="!canUpdateSettings"
                                :label="t('common.actions.save')"
                                :loading="freeShippingForm.processing"
                                icon="pi pi-save"
                                size="small"
                                type="submit"
                            />
                        </div>

                        <div class="space-y-1">
                            <InputGroup>
                                <InputGroupAddon>
                                    {{ t('admin.settings.over-amount') }}
                                </InputGroupAddon>
                                <InputNumber
                                    v-model="freeShippingForm.threshold"
                                    :aria-label="
                                        t(
                                            'admin.settings.free-shipping-threshold-input'
                                        )
                                    "
                                    :disabled="!canUpdateSettings"
                                    :invalid="
                                        !!freeShippingForm.errors.threshold
                                    "
                                    :max="999999.99"
                                    :max-fraction-digits="2"
                                    :min="0"
                                    :min-fraction-digits="2"
                                    :placeholder="t('admin.settings.price')"
                                    input-id="free-shipping-threshold"
                                    size="small"
                                />
                                <InputGroupAddon>
                                    {{
                                        t('admin.settings.free-shipping-after')
                                    }}
                                </InputGroupAddon>
                            </InputGroup>
                            <small
                                v-if="freeShippingForm.errors.threshold"
                                class="text-red-700"
                            >
                                {{ freeShippingForm.errors.threshold }}
                            </small>

                            <InputGroup>
                                <InputGroupAddon>
                                    {{ t('admin.settings.note') }}
                                </InputGroupAddon>
                                <InputText
                                    v-model="freeShippingForm.description"
                                    :aria-label="
                                        t(
                                            'admin.settings.free-shipping-description-input'
                                        )
                                    "
                                    :disabled="!canUpdateSettings"
                                    :invalid="
                                        !!freeShippingForm.errors.description
                                    "
                                    :placeholder="
                                        t('admin.settings.free-shipping-note')
                                    "
                                    id="free-shipping-description"
                                    maxlength="255"
                                    size="small"
                                />
                            </InputGroup>
                            <small
                                v-if="freeShippingForm.errors.description"
                                class="text-red-700"
                            >
                                {{ freeShippingForm.errors.description }}
                            </small>
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>
