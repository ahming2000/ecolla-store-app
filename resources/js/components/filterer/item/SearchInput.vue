<script setup lang="ts">
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import { useI18n } from 'vue-i18n'

defineProps<{
    modelValue?: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string | undefined]
}>()

const { t } = useI18n()
</script>

<template>
    <IconField>
        <InputIcon class="pi pi-search" />

        <InputText
            :model-value="modelValue"
            @update:model-value="(value) => emit('update:modelValue', value)"
            :placeholder="t('common.filters.search-items')"
            class="w-full"
        />
    </IconField>
</template>
