<script setup lang="ts">
import type { DeliveryMode } from '@/enums/DeliveryMode'
import { getAllDeliveryModes, getDeliveryModeLabel } from '@/enums/DeliveryMode'
import Select from 'primevue/select'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineProps<{
    modelValue?: DeliveryMode | null
}>()

const emit = defineEmits<{
    'update:modelValue': [value: DeliveryMode | null]
}>()

const { t } = useI18n()

const options = computed(() => {
    return getAllDeliveryModes().map((deliveryMode) => ({
        label: getDeliveryModeLabel(t, deliveryMode),
        value: deliveryMode,
    }))
})
</script>

<template>
    <Select
        :model-value="modelValue"
        :options="options"
        :placeholder="t('admin.orders.order-mode-filter')"
        class="w-full"
        option-label="label"
        option-value="value"
        show-clear
        @update:model-value="(value) => emit('update:modelValue', value)"
    />
</template>
