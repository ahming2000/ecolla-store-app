<script setup lang="ts">
import { SUPERVISOR } from '@/enums/AccessLevel'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import type { AppPageProps, Item } from '@/types'
import { usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    item: Item
}>()

const itemStore = useItemStore()
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())
const { locale: activeLanguage, t } = useI18n()

const isDeleting = ref(false)
const isDialogVisible = ref(false)

const canDeleteItem = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= SUPERVISOR
})

const deleteActionLabel = computed(() => {
    return t('admin.items.delete-item', {
        name: getLocalizedName(props.item, activeLanguage.value),
    })
})

const localizedItemName = computed(() => {
    return getLocalizedName(props.item, activeLanguage.value)
})

const onDeleteItem = async (): Promise<void> => {
    try {
        isDeleting.value = true
        await itemStore.onDeleteItem(props.item.id)
        isDialogVisible.value = false
        toast.success(
            t('admin.items.deleted-success'),
            t('common.notifications.success')
        )
    } catch (error) {
        console.error(error)
        toast.axiosError(
            error,
            t('admin.items.delete-failed'),
            t('common.notifications.error')
        )
    } finally {
        isDeleting.value = false
    }
}
</script>

<template>
    <Button
        :aria-label="deleteActionLabel"
        :data-testid="`delete-item-${item.id}`"
        :disabled="!canDeleteItem"
        :label="t('common.actions.delete')"
        icon="pi pi-trash"
        severity="danger"
        @click="isDialogVisible = true"
    />

    <Dialog
        v-model:visible="isDialogVisible"
        :closable="!isDeleting"
        :closeOnEscape="!isDeleting"
        :header="t('admin.items.delete-title')"
        :style="{ width: '26rem' }"
        modal
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-start gap-3">
                <i
                    aria-hidden="true"
                    class="pi pi-exclamation-triangle mt-1 text-red-500"
                />
                <p class="m-0 text-sm">
                    {{
                        t('admin.items.delete-confirmation', {
                            name: localizedItemName,
                        })
                    }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="isDeleting"
                    :label="t('common.actions.cancel')"
                    severity="secondary"
                    @click="isDialogVisible = false"
                />
                <Button
                    :label="t('common.actions.delete')"
                    :loading="isDeleting"
                    severity="danger"
                    @click="onDeleteItem"
                />
            </div>
        </div>
    </Dialog>
</template>
