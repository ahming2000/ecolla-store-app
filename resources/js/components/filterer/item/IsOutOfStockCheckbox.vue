<script setup lang="ts">
import Checkbox from 'primevue/checkbox'
import { useI18n } from 'vue-i18n'

defineProps<{
    modelValue?: boolean
}>()

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
}>()

const { t } = useI18n()
</script>

<template>
    <div class="flex items-center mr-3">
        <Checkbox
            :model-value="modelValue"
            @update:model-value="(value) => emit('update:modelValue', value)"
            input-id="outOfStock"
            name="outOfStock"
            binary
        />

        <label for="outOfStock" class="ml-2">
            {{ t('common.filters.only-out-of-stock') }}
        </label>
    </div>
</template>
