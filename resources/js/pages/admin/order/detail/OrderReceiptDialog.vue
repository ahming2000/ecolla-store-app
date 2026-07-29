<script setup lang="ts">
import type { Order } from '@/types'
import Dialog from 'primevue/dialog'
import { useI18n } from 'vue-i18n'

defineProps<{
    order: Order
    receiptSource: string | null
    visible: boolean
}>()

const emit = defineEmits<{
    'update:visible': [value: boolean]
}>()

const { t } = useI18n()
</script>

<template>
    <Dialog
        :draggable="false"
        :header="
            t('admin.orders.receipt-dialog-title', {
                reference: order.reference_num,
            })
        "
        :style="{ width: 'min(96vw, 64rem)' }"
        :visible="visible"
        modal
        @update:visible="(value) => emit('update:visible', value)"
    >
        <img
            v-if="receiptSource"
            :alt="
                t('admin.orders.receipt-image-alt', {
                    reference: order.reference_num,
                })
            "
            :src="receiptSource"
            class="max-h-[75vh] w-full object-contain"
        />
    </Dialog>
</template>
