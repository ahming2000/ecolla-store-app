<script setup lang="ts">
import type { Order } from '@/types'
import InputText from 'primevue/inputtext'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        disabled?: boolean
        error?: string
        modelValue: Order['tracking_no']
        orderId: Order['id']
    }>(),
    {
        disabled: false,
        error: '',
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: Order['tracking_no']]
}>()

const { t } = useI18n()

const inputId = computed(() => `order-tracking-number-${props.orderId}`)
</script>

<template>
    <div class="flex flex-col gap-1">
        <InputText
            :id="inputId"
            :aria-describedby="error ? `${inputId}-help` : undefined"
            :aria-label="t('admin.orders.delivery-tracking-id')"
            :data-testid="inputId"
            :disabled="disabled"
            :invalid="!!error"
            :model-value="modelValue ?? undefined"
            class="w-full"
            size="small"
            @update:model-value="
                (value) => emit('update:modelValue', value ?? null)
            "
        />

        <small v-if="error" :id="`${inputId}-help`" class="text-red-600">
            {{ error }}
        </small>
    </div>
</template>
