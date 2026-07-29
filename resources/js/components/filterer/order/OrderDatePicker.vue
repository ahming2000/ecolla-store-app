<script setup lang="ts">
import DatePicker from 'primevue/datepicker'
import { useI18n } from 'vue-i18n'

defineProps<{
    modelValue?: Date | null
}>()

const emit = defineEmits<{
    'update:modelValue': [value: Date | null]
}>()

const { t } = useI18n()

const onUpdate = (
    value: Date | Date[] | (Date | null)[] | null | undefined
): void => {
    emit('update:modelValue', value instanceof Date ? value : null)
}
</script>

<template>
    <DatePicker
        :date-format="t('admin.orders.date-format')"
        :model-value="modelValue"
        :placeholder="t('admin.orders.date-filter')"
        class="w-full"
        show-button-bar
        @update:model-value="onUpdate"
    />
</template>
