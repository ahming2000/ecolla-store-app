<script setup lang="ts">
import { download as downloadOrder } from '@/routes/admin/order'
import type { Order } from '@/types'
import Button from 'primevue/button'
import { useI18n } from 'vue-i18n'

defineProps<{
    hasReceipt: boolean
    order: Order
}>()

const emit = defineEmits<{
    viewReceipt: []
}>()

const { t } = useI18n()

const onDownloadOrder = (order: Order): void => {
    const link = document.createElement('a')

    link.href = downloadOrder.url(order.id)
    link.download = `${order.reference_num}.pdf`
    link.hidden = true
    document.body.append(link)
    link.click()
    link.remove()
}
</script>

<template>
    <div class="flex gap-1">
        <Button
            :aria-label="t('admin.orders.view-receipt')"
            :data-testid="`view-order-receipt-${order.id}`"
            :disabled="!hasReceipt"
            :label="t('admin.orders.view-receipt')"
            icon="pi pi-image"
            size="small"
            @click="emit('viewReceipt')"
        />
        <Button
            :aria-label="t('admin.orders.download-order')"
            :data-testid="`download-order-${order.id}`"
            :label="t('admin.orders.download-order')"
            icon="pi pi-download"
            size="small"
            @click="onDownloadOrder(order)"
        />
    </div>
</template>
