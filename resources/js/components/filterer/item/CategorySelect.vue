<script setup lang="ts">
import { getLocalizedName } from '@/libraries/i18n/language'
import type { Category } from '@/types'
import MultiSelect from 'primevue/multiselect'
import { useI18n } from 'vue-i18n'

withDefaults(
    defineProps<{
        modelValue?: Category[]
        options?: Category[]
    }>(),
    {
        modelValue: () => [],
        options: () => [],
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: Category[]]
}>()

const { locale: activeLanguage, t } = useI18n()

const getCategoryLabel = (category: Category): string => {
    return getLocalizedName(category, activeLanguage.value)
}
</script>

<template>
    <MultiSelect
        class="w-full"
        :model-value="modelValue"
        @update:model-value="(value) => emit('update:modelValue', value)"
        :options="options"
        :option-label="getCategoryLabel"
        :placeholder="t('common.filters.all-items')"
        filter
    >
        <template #option="{ option }">
            <span>
                {{ getCategoryLabel(option) }}
            </span>
            <span class="ml-2">({{ option.items_count }})</span>
        </template>
    </MultiSelect>
</template>
