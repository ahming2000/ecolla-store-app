<script setup lang="ts">
import { EDITOR } from '@/enums/AccessLevel'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import type { AppPageProps, Item, Variation } from '@/types'
import { usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    item: Item
    variation: Variation
}>()

const itemStore = useItemStore()
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())
const { locale: activeLanguage, t } = useI18n()

const isDeleting = ref(false)
const isDialogVisible = ref(false)

const canUpdateItem = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= EDITOR
})

const localizedVariationName = computed(() => {
    return getLocalizedName(props.variation, activeLanguage.value)
})

const onDelete = async (): Promise<void> => {
    try {
        isDeleting.value = true
        await itemStore.onDeleteVariation(props.item.id, props.variation.id)
        isDialogVisible.value = false
        toast.success(
            t('admin.items.variation.deleted-success'),
            t('common.notifications.success')
        )
    } catch (error) {
        toast.error(
            t('admin.items.variation.delete-failed'),
            t('common.notifications.error')
        )
        console.error(error)
    } finally {
        isDeleting.value = false
    }
}
</script>

<template>
    <Button
        :aria-label="
            t('admin.items.variation.delete-variation', {
                name: localizedVariationName,
            })
        "
        :data-testid="`delete-variation-${variation.id}`"
        :disabled="!canUpdateItem"
        :label="t('common.actions.delete')"
        icon="pi pi-trash"
        severity="danger"
        size="small"
        @click="isDialogVisible = true"
    />

    <Dialog
        v-model:visible="isDialogVisible"
        :closable="!isDeleting"
        :close-on-escape="!isDeleting"
        :header="t('admin.items.variation.delete-title')"
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
                        t('admin.items.variation.delete-confirmation', {
                            name: localizedVariationName,
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
                    :data-testid="`confirm-delete-variation-${variation.id}`"
                    :label="t('common.actions.delete')"
                    :loading="isDeleting"
                    severity="danger"
                    @click="onDelete"
                />
            </div>
        </div>
    </Dialog>
</template>

<style scoped lang="postcss"></style>
