<script setup lang="ts">
import SmartImage from '@/components/image/SmartImage.vue'
import { EDITOR } from '@/enums/AccessLevel'
import Notification from '@/libraries/primevue/toast/Notification'
import AddImageButton from '@/pages/admin/item/image/AddImageButton.vue'
import { useItemStore } from '@/stores/item.store'
import type { AppPageProps, Item, Image as ItemImage } from '@/types'
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
const { t } = useI18n()

const isConfirmationVisible = ref(false)
const isRemoving = ref(false)
const selectedImage = ref<ItemImage | null>(null)

const canUpdateItem = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= EDITOR
})

const selectedImageName = computed(() => {
    return selectedImage.value?.name ?? t('admin.items.tabs.images')
})

const openRemoveConfirmation = (image: ItemImage): void => {
    selectedImage.value = image
    isConfirmationVisible.value = true
}

const onRemoveImage = async (): Promise<void> => {
    if (!selectedImage.value) {
        return
    }

    try {
        isRemoving.value = true
        await itemStore.onRemoveItemImage(props.item.id, selectedImage.value.id)
        isConfirmationVisible.value = false
        toast.success(
            t('admin.items.images.removed-success'),
            t('common.notifications.success')
        )
    } catch (error) {
        console.error(error)
        toast.axiosError(
            error,
            t('admin.items.images.remove-failed'),
            t('common.notifications.error')
        )
    } finally {
        isRemoving.value = false
    }
}
</script>

<template>
    <div class="h-full overflow-y-auto">
        <div
            class="grid grid-cols-2 gap-5 p-5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
        >
            <div
                v-for="image in item.images"
                :key="image.id"
                :data-testid="`item-image-${image.id}`"
                class="relative"
            >
                <SmartImage
                    :alt="image.name"
                    :image="image"
                    image-class="aspect-square h-full w-full rounded-lg object-contain"
                    preview
                    wrapper-class="block aspect-square overflow-hidden rounded-lg shadow-lg"
                />

                <div
                    class="absolute right-0 top-0 flex -translate-y-1/2 translate-x-1/2 items-center justify-center"
                >
                    <Button
                        :aria-label="
                            t('admin.items.images.remove-image', {
                                name: image.name,
                            })
                        "
                        :data-testid="`remove-item-image-${image.id}`"
                        :disabled="!canUpdateItem"
                        class="!h-7 !w-7"
                        icon="pi pi-times"
                        icon-class="text-[10px]"
                        rounded
                        severity="danger"
                        @click="openRemoveConfirmation(image)"
                    />
                </div>
            </div>

            <AddImageButton :disabled="!canUpdateItem" :item-id="item.id" />
        </div>

        <Dialog
            v-model:visible="isConfirmationVisible"
            :closable="!isRemoving"
            :close-on-escape="!isRemoving"
            :header="t('admin.items.images.remove-title')"
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
                            t('admin.items.images.remove-confirmation', {
                                name: selectedImageName,
                            })
                        }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        :disabled="isRemoving"
                        :label="t('common.actions.cancel')"
                        severity="secondary"
                        @click="isConfirmationVisible = false"
                    />
                    <Button
                        data-testid="confirm-remove-item-image"
                        :label="t('common.actions.delete')"
                        :loading="isRemoving"
                        severity="danger"
                        @click="onRemoveImage"
                    />
                </div>
            </div>
        </Dialog>
    </div>
</template>
