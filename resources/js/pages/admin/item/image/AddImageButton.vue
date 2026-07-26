<script setup lang="ts">
import ImageUploaderDialog from '@/components/dialog/ImageUploaderDialog.vue'
import { WHITE_EDGE } from '@/enums/ImageUploadOption'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import type { Identifier, Image } from '@/types'
import { useToast } from 'primevue/usetoast'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        itemId: Identifier
        disabled?: boolean
    }>(),
    {
        disabled: false,
    }
)

const itemStore = useItemStore()
const toast = Notification.init(useToast())
const { t } = useI18n()
const visible = ref(false)

const onClick = (): void => {
    visible.value = true
}

const onStoreItemImage = async (image: Image): Promise<void> => {
    await itemStore.onUploadItemImage(props.itemId, image.id)
    toast.success(
        t('admin.items.images.uploaded-success'),
        t('common.notifications.success')
    )
}
</script>

<template>
    <button
        :aria-label="t('admin.items.images.add')"
        :data-testid="`add-item-image-${itemId}`"
        :disabled="disabled"
        class="add-image-button"
        type="button"
        @click="onClick"
    >
        <i aria-hidden="true" class="add-image-button-icon pi pi-plus" />
    </button>

    <ImageUploaderDialog
        v-model:visible="visible"
        :on-confirmed="onStoreItemImage"
        :show-image-option="true"
        :default-image-option="WHITE_EDGE"
        :confirmation-failed-message="t('admin.items.images.upload-failed')"
    />
</template>

<style scoped>
@reference "../../../../../css/app.css";

.add-image-button {
    @apply relative w-full pt-[100%] border-0 bg-[rgba(255,255,255,0.5)] rounded-lg transition-all duration-300 cursor-pointer hover:bg-gray-100 active:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50;
}

.add-image-button-icon {
    @apply absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 text-primary text-[25px] transition-all duration-300 hover:text-primary-600 active:text-primary-600;
}
</style>
