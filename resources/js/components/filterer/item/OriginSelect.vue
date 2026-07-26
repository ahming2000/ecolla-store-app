<script setup lang="ts">
import { getLocalizedName } from '@/libraries/i18n/language'
import type { Origin } from '@/types'
import MultiSelect from 'primevue/multiselect'
import { useI18n } from 'vue-i18n'

withDefaults(
    defineProps<{
        modelValue?: Origin[]
        options?: Origin[]
    }>(),
    {
        modelValue: () => [],
        options: () => [],
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: Origin[]]
}>()

const { locale: activeLanguage, t } = useI18n()

const getOriginLabel = (origin: Origin): string => {
    return getLocalizedName(origin, activeLanguage.value)
}
</script>

<template>
    <MultiSelect
        class="w-full"
        :model-value="modelValue"
        @update:model-value="(value) => emit('update:modelValue', value)"
        :options="options"
        :option-label="getOriginLabel"
        :placeholder="t('common.filters.all-origins')"
        filter
    >
        <template #option="{ option }">
            <span>
                {{ getOriginLabel(option) }}
            </span>
            <span class="ml-2">({{ option.items_count }})</span>
        </template>
    </MultiSelect>
</template>
