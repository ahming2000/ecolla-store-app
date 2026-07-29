<script setup lang="ts">
import { getOrderStatuses, getOrderStatusLabel } from '@/enums/OrderStatus'
import type { Order } from '@/types'
import Select from 'primevue/select'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        disabled?: boolean
        loading?: boolean
        modelValue: Order['status']
        orderId: Order['id']
    }>(),
    {
        disabled: false,
        loading: false,
    }
)

const emit = defineEmits<{
    change: []
    'update:modelValue': [value: Order['status']]
}>()

const { t } = useI18n()

const options = computed(() => {
    return getOrderStatuses().map((status) => ({
        label: getOrderStatusLabel(t, status),
        value: status,
    }))
})
</script>

<template>
    <Select
        :aria-label="t('admin.orders.columns.status')"
        :data-testid="`order-status-${orderId}`"
        :disabled="disabled"
        :loading="loading"
        :model-value="modelValue"
        :options="options"
        option-label="label"
        option-value="value"
        @change="emit('change')"
        @update:model-value="(value) => emit('update:modelValue', value)"
    />
</template>
