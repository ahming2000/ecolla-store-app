<script setup lang="ts">
import SmartImage from '@/components/image/SmartImage.vue'
import { prepareImageForUpload } from '@/libraries/image-preparation'
import Notification from '@/libraries/primevue/toast/Notification'
import type { Identifier, Variation } from '@/types'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { computed, onUnmounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        itemId: Identifier
        variation?: Variation | null
        pendingImage?: File | null
        disabled?: boolean
    }>(),
    {
        variation: null,
        pendingImage: null,
        disabled: false,
    }
)

const emit = defineEmits<{
    'update:pendingImage': [image: File | null]
}>()

const toast = Notification.init(useToast())
const { t } = useI18n()
const imageInput = ref<HTMLInputElement | null>(null)
const isPreparingImage = ref(false)
const previewImageUrl = ref<string | null>(null)

const editorIdentifier = computed(() => {
    return props.variation?.id ?? `new-${props.itemId}`
})
const currentImage = computed(() => props.variation?.image ?? null)
const displayedImageSource = computed(() => {
    return previewImageUrl.value ?? currentImage.value?.src ?? null
})
const displayedImageName = computed(() => {
    return props.pendingImage?.name ?? currentImage.value?.name ?? ''
})

const uploadActionLabel = computed(() => {
    return displayedImageSource.value
        ? t('admin.items.variation.change-photo')
        : t('admin.items.variation.add-photo')
})

const clearPreviewImageUrl = (): void => {
    if (previewImageUrl.value) {
        URL.revokeObjectURL(previewImageUrl.value)
        previewImageUrl.value = null
    }
}

const onSelectImage = async (event: Event): Promise<void> => {
    const input = event.target as HTMLInputElement
    const selectedImage = input.files?.[0]

    if (!selectedImage) {
        return
    }

    try {
        isPreparingImage.value = true
        emit('update:pendingImage', await prepareImageForUpload(selectedImage))
    } catch (error) {
        toast.error(
            t('admin.items.variation.photo-upload-failed'),
            t('common.notifications.error')
        )
        console.error(error)
    } finally {
        isPreparingImage.value = false
        input.value = ''
    }
}

watch(
    () => props.pendingImage,
    (pendingImage) => {
        clearPreviewImageUrl()

        if (pendingImage) {
            previewImageUrl.value = URL.createObjectURL(pendingImage)
        }
    },
    { immediate: true }
)

onUnmounted(clearPreviewImageUrl)
</script>

<template>
    <section
        :data-testid="`variation-photo-editor-${editorIdentifier}`"
        class="flex flex-col items-center gap-2 rounded-lg border border-surface-200 bg-surface-50 p-4 dark:border-surface-700 dark:bg-surface-800"
    >
        <span
            class="text-sm font-medium text-surface-700 dark:text-surface-200"
        >
            {{ t('admin.items.variation.photo') }}
        </span>

        <input
            ref="imageInput"
            accept="image/gif,image/jpeg,image/png,image/webp"
            :data-testid="`variation-photo-input-${editorIdentifier}`"
            :disabled="disabled || isPreparingImage"
            class="sr-only"
            type="file"
            @change="onSelectImage"
        />

        <SmartImage
            v-if="displayedImageSource"
            :alt="displayedImageName"
            :full="Boolean(previewImageUrl)"
            :image="previewImageUrl ? null : currentImage"
            image-class="h-full w-full object-contain"
            preview
            :src="displayedImageSource"
            wrapper-class="block size-36 overflow-hidden rounded-lg border border-surface-200 bg-white dark:border-surface-700 dark:bg-surface-900"
        />

        <button
            v-else
            :aria-label="uploadActionLabel"
            :data-testid="`upload-variation-photo-${editorIdentifier}`"
            :disabled="disabled || isPreparingImage"
            class="flex size-36 items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-primary-300 bg-white transition hover:border-primary hover:bg-primary-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:cursor-not-allowed disabled:opacity-50 dark:bg-surface-900"
            type="button"
            @click="imageInput?.click()"
        >
            <span class="flex flex-col items-center gap-2 text-primary">
                <i aria-hidden="true" class="pi pi-image text-3xl" />
                <span class="text-xs font-medium">
                    {{ uploadActionLabel }}
                </span>
            </span>
        </button>

        <Button
            v-if="displayedImageSource"
            :aria-label="uploadActionLabel"
            :data-testid="`upload-variation-photo-${editorIdentifier}`"
            :disabled="disabled || isPreparingImage"
            icon="pi pi-upload"
            :label="uploadActionLabel"
            severity="secondary"
            size="small"
            @click="imageInput?.click()"
        />
    </section>
</template>
