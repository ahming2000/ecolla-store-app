<script setup lang="ts">
import type { ImageUploadOption } from '@/enums/ImageUploadOption'
import * as ImageOption from '@/enums/ImageUploadOption'
import { parseFormError } from '@/libraries/axios/common/parser'
import { uploadImage } from '@/libraries/axios/image'
import { prepareImageForUpload } from '@/libraries/image-preparation'
import Notification from '@/libraries/primevue/toast/Notification'
import type { Image as UploadedImage } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import type { FileUploadUploaderEvent } from 'primevue/fileupload'
import FileUpload from 'primevue/fileupload'
import Image from 'primevue/image'
import ProgressSpinner from 'primevue/progressspinner'
import RadioButton from 'primevue/radiobutton'
import { useToast } from 'primevue/usetoast'
import { computed, onUnmounted, ref, useId, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        visible?: boolean
        onConfirmed?: (image: UploadedImage) => Promise<void> | void
        showImageOption?: boolean
        defaultImageOption?: ImageUploadOption
        confirmationFailedMessage?: string
    }>(),
    {
        visible: false,
        onConfirmed: async () => {},
        showImageOption: false,
        defaultImageOption: ImageOption.ORIGINAL,
        confirmationFailedMessage: '',
    }
)

const emit = defineEmits<{
    'update:visible': [visible: boolean]
}>()

const toast = Notification.init(useToast())
const { t } = useI18n()

const isUploading = ref(false)
const isSaving = ref(false)
const selectedImage = ref<File | null>(null)
const previewImageUrl = ref<string | null>(null)
const squareImageOption = ref<ImageUploadOption>(props.defaultImageOption)
const imageOptionInputId = useId()

const imagePath = computed(() => {
    return previewImageUrl.value ?? undefined
})

const previewFrameClass = computed(() => {
    if (squareImageOption.value === ImageOption.ORIGINAL) {
        return 'h-full w-full'
    }

    return 'aspect-square w-full sm:h-full sm:w-auto'
})

const previewImageClass = computed(() => {
    switch (squareImageOption.value) {
        case ImageOption.FILL:
            return 'block h-full w-full cursor-zoom-in object-cover'
        case ImageOption.STRETCH:
            return 'block h-full w-full cursor-zoom-in object-fill'
        case ImageOption.ORIGINAL:
        case ImageOption.WHITE_EDGE:
        default:
            return 'block h-full w-full cursor-zoom-in object-contain'
    }
})

const imageOptionId = (option: ImageUploadOption): string => {
    return `${imageOptionInputId}-${option}`
}

const clearSelectedImage = (): void => {
    if (previewImageUrl.value) {
        URL.revokeObjectURL(previewImageUrl.value)
    }

    selectedImage.value = null
    previewImageUrl.value = null
}

const uploader = async (event: FileUploadUploaderEvent): Promise<void> => {
    try {
        isUploading.value = true
        clearSelectedImage()
        const selectedFile = Array.isArray(event.files)
            ? event.files[0]
            : event.files

        if (!selectedFile) {
            return
        }

        const preparedImage = await prepareImageForUpload(selectedFile)

        selectedImage.value = preparedImage
        previewImageUrl.value = URL.createObjectURL(preparedImage)
    } catch (e) {
        const validationMessage = parseFormError(e).image

        toast.axiosError(
            e,
            validationMessage ?? t('common.image-upload.upload-failed'),
            t('common.notifications.error')
        )
        console.error(e)
    } finally {
        isUploading.value = false
    }
}

const onConfirmImage = async (): Promise<void> => {
    try {
        if (!selectedImage.value) {
            return
        }

        isSaving.value = true
        const selectedOption = squareImageOption.value
        const uploadedImage = await uploadImage(
            selectedImage.value,
            selectedOption
        )

        await props.onConfirmed(uploadedImage)
        emit('update:visible', false)
        clearSelectedImage()
    } catch (e) {
        toast.axiosError(
            e,
            props.confirmationFailedMessage ||
                t('common.notifications.generic-error'),
            t('common.notifications.error')
        )
        console.error(e)
    } finally {
        isSaving.value = false
    }
}

const onVisibilityChange = (visible: boolean): void => {
    if (!visible) {
        clearSelectedImage()
    }

    emit('update:visible', visible)
}

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            squareImageOption.value = props.defaultImageOption

            return
        }

        clearSelectedImage()
    }
)

onUnmounted(clearSelectedImage)
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="onVisibilityChange"
        :header="t('common.image-upload.title')"
        :draggable="false"
        :pt="{
            content: {
                class: 'flex min-h-0 flex-1 flex-col overflow-hidden',
            },
        }"
        class="h-[min(36rem,calc(100dvh-1rem))] w-[calc(100vw-1rem)] !max-h-[calc(100dvh-1rem)] sm:w-[32rem]"
        data-testid="image-uploader-dialog"
        modal
    >
        <div class="flex h-full min-h-0 flex-col gap-3">
            <div class="flex shrink-0 flex-wrap gap-2">
                <FileUpload
                    mode="basic"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    :max-file-size="10000000"
                    :auto="true"
                    :choose-label="t('common.actions.browse')"
                    :disabled="isUploading || isSaving"
                    :invalid-file-size-message="
                        t('common.image-upload.invalid-file-size')
                    "
                    :invalid-file-type-message="
                        t('common.image-upload.invalid-file-type')
                    "
                    custom-upload
                    @uploader="uploader"
                />

                <Button
                    :label="t('common.actions.confirm')"
                    icon="pi pi-check"
                    :loading="isSaving"
                    @click="onConfirmImage"
                    :disabled="!imagePath || isUploading"
                    data-testid="confirm-image-upload"
                />
            </div>

            <div class="flex shrink-0 flex-wrap gap-3" v-show="showImageOption">
                <div class="flex items-center">
                    <RadioButton
                        v-model="squareImageOption"
                        :disabled="isSaving"
                        :input-id="imageOptionId(ImageOption.WHITE_EDGE)"
                        :value="ImageOption.WHITE_EDGE"
                    />

                    <label
                        :for="imageOptionId(ImageOption.WHITE_EDGE)"
                        class="ml-2"
                    >
                        {{ t('common.image-upload.white-edge') }}
                    </label>
                </div>

                <div class="flex items-center">
                    <RadioButton
                        v-model="squareImageOption"
                        :disabled="isSaving"
                        :input-id="imageOptionId(ImageOption.FILL)"
                        :value="ImageOption.FILL"
                    />

                    <label :for="imageOptionId(ImageOption.FILL)" class="ml-2">
                        {{ t('common.image-upload.fill') }}
                    </label>
                </div>

                <div class="flex items-center">
                    <RadioButton
                        v-model="squareImageOption"
                        :disabled="isSaving"
                        :input-id="imageOptionId(ImageOption.STRETCH)"
                        :value="ImageOption.STRETCH"
                    />

                    <label
                        :for="imageOptionId(ImageOption.STRETCH)"
                        class="ml-2"
                    >
                        {{ t('common.image-upload.stretch') }}
                    </label>
                </div>

                <div class="flex items-center">
                    <RadioButton
                        v-model="squareImageOption"
                        :disabled="isSaving"
                        :input-id="imageOptionId(ImageOption.ORIGINAL)"
                        :value="ImageOption.ORIGINAL"
                    />

                    <label
                        :for="imageOptionId(ImageOption.ORIGINAL)"
                        class="ml-2"
                    >
                        {{ t('common.image-upload.original') }}
                    </label>
                </div>
            </div>

            <div
                :aria-busy="isUploading"
                class="flex min-h-0 flex-1 items-center justify-center overflow-hidden rounded-lg bg-surface-50 dark:bg-surface-800"
                data-testid="image-upload-preview-area"
            >
                <ProgressSpinner v-if="isUploading" />

                <div
                    v-else-if="imagePath"
                    :class="[
                        'flex items-center justify-center overflow-hidden',
                        previewFrameClass,
                        {
                            'bg-white':
                                squareImageOption === ImageOption.WHITE_EDGE,
                        },
                    ]"
                    data-testid="image-transformation-preview"
                >
                    <Image
                        :src="imagePath"
                        :alt="t('common.alt.uploaded-image')"
                        class="flex h-full w-full items-center justify-center overflow-hidden"
                        :image-class="previewImageClass"
                        preview
                    />
                </div>
            </div>
        </div>
    </Dialog>
</template>
