<script setup lang="ts">
import ImageUploaderDialog from '@/components/dialog/ImageUploaderDialog.vue'
import type { Image } from '@/types'
import Button from 'primevue/button'
import type { HTMLAttributes } from 'vue'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        modelValue?: Image | null
        class?: HTMLAttributes['class']
        error?: string
    }>(),
    {
        modelValue: null,
        class: undefined,
        error: undefined,
    }
)

const emit = defineEmits<{
    'update:modelValue': [image: Image]
}>()

const visible = ref(false)
const { t } = useI18n()

const onAssignReceiptImage = (image: Image): void => {
    emit('update:modelValue', image)
}
</script>

<template>
    <div :class="props.class">
        <div class="flex items-center gap-2">
            <Button
                :label="t('shop.checkout.receipt.upload')"
                @click="visible = true"
            />

            <div>
                {{ modelValue?.name ?? t('shop.checkout.receipt.prompt') }}
            </div>
        </div>

        <small v-show="error" class="text-red-500">
            {{ error }}
        </small>
    </div>

    <ImageUploaderDialog
        v-model:visible="visible"
        :on-confirmed="onAssignReceiptImage"
        :show-image-option="false"
        :with-thumbnail="false"
    />
</template>
