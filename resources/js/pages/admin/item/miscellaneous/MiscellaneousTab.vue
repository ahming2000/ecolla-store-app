<script setup lang="ts">
import { EDITOR } from '@/enums/AccessLevel'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import type { AppPageProps, Item, ResettableItemCounter } from '@/types'
import { usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    item: Item
}>()

interface MiscellaneousItemRow {
    counter: ResettableItemCounter
    label: string
    value: number
}

const itemStore = useItemStore()
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())
const { t } = useI18n()

const isConfirmationVisible = ref(false)
const isResetting = ref(false)
const selectedCounter = ref<ResettableItemCounter | null>(null)

const canUpdateItem = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= EDITOR
})

const list = computed<MiscellaneousItemRow[]>(() => {
    return [
        {
            counter: 'view_count',
            label: t('admin.items.miscellaneous.view-count'),
            value: props.item.view_count,
        },
        {
            counter: 'sold_count',
            label: t('admin.items.miscellaneous.sold-count'),
            value: props.item.sold_count,
        },
    ]
})

const selectedCounterLabel = computed(() => {
    if (selectedCounter.value === 'view_count') {
        return t('admin.items.miscellaneous.view-count')
    }

    if (selectedCounter.value === 'sold_count') {
        return t('admin.items.miscellaneous.sold-count')
    }

    return ''
})

const openResetConfirmation = (counter: ResettableItemCounter): void => {
    selectedCounter.value = counter
    isConfirmationVisible.value = true
}

const onReset = async (): Promise<void> => {
    if (!selectedCounter.value) {
        return
    }

    const counter = selectedCounter.value
    const counterLabel = selectedCounterLabel.value

    try {
        isResetting.value = true
        await itemStore.onResetItemMiscellaneousDetail(props.item.id, counter)
        isConfirmationVisible.value = false
        toast.success(
            t('admin.items.miscellaneous.reset-success', {
                counter: counterLabel,
            }),
            t('common.notifications.success')
        )
    } catch (error) {
        console.error(error)
        toast.axiosError(
            error,
            t('admin.items.miscellaneous.reset-failed', {
                counter: counterLabel,
            }),
            t('common.notifications.error')
        )
    } finally {
        isResetting.value = false
    }
}
</script>

<template>
    <DataTable :value="list">
        <Column
            field="label"
            :header="t('admin.items.miscellaneous.feature')"
        />
        <Column field="value" :header="t('admin.items.miscellaneous.value')" />

        <Column :header="t('admin.items.miscellaneous.reset-action')">
            <template #body="{ data }">
                <Button
                    :aria-label="
                        t('admin.items.miscellaneous.reset-counter', {
                            counter: data.label,
                        })
                    "
                    :data-testid="`reset-item-${data.counter}-${item.id}`"
                    :disabled="!canUpdateItem || data.value === 0"
                    :label="t('common.actions.reset')"
                    @click="openResetConfirmation(data.counter)"
                />
            </template>
        </Column>
    </DataTable>

    <Dialog
        v-model:visible="isConfirmationVisible"
        :closable="!isResetting"
        :close-on-escape="!isResetting"
        :header="
            t('admin.items.miscellaneous.reset-title', {
                counter: selectedCounterLabel,
            })
        "
        :style="{ width: '26rem' }"
        modal
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-start gap-3">
                <i
                    aria-hidden="true"
                    class="pi pi-exclamation-triangle mt-1 text-orange-500"
                />
                <p class="m-0 text-sm">
                    {{
                        t('admin.items.miscellaneous.reset-confirmation', {
                            counter: selectedCounterLabel,
                        })
                    }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="isResetting"
                    :label="t('common.actions.cancel')"
                    severity="secondary"
                    @click="isConfirmationVisible = false"
                />
                <Button
                    :data-testid="`confirm-reset-item-${selectedCounter}`"
                    :label="t('common.actions.reset')"
                    :loading="isResetting"
                    severity="danger"
                    @click="onReset"
                />
            </div>
        </div>
    </Dialog>
</template>

<style scoped lang="postcss"></style>
