<script setup lang="ts">
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import Select from 'primevue/select'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineProps<{
    modelValue?: string | null
    order?: 'asc' | 'desc'
    onOrderChange?: () => void
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string | null]
}>()

const { t } = useI18n()

const options = computed(() => [
    {
        label: t('common.filters.created-at'),
        value: 'created_at',
    },
    {
        label: t('common.filters.sales'),
        value: 'sold_count',
    },
    {
        label: t('common.filters.clicks'),
        value: 'view_count',
    },
    {
        label: t('common.filters.name'),
        value: 'name',
    },
])
</script>

<template>
    <InputGroup>
        <Select
            class="w-full"
            :model-value="modelValue"
            @update:model-value="(value) => emit('update:modelValue', value)"
            :options="options"
            option-label="label"
            option-value="value"
            :placeholder="t('common.filters.no-sort')"
            show-clear
        />

        <InputGroupAddon @click="onOrderChange">
            <i
                class="cursor-pointer pi"
                :class="{
                    'pi-sort-amount-down-alt': order === 'asc',
                    'pi-sort-amount-up': order === 'desc',
                }"
            ></i>
        </InputGroupAddon>
    </InputGroup>
</template>
