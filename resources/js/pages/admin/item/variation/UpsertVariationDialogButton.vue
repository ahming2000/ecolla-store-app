<script setup lang="ts">
import IftaInputText from '@/components/input/IftaInputText.vue'
import { EDITOR } from '@/enums/AccessLevel'
import { WHITE_EDGE } from '@/enums/ImageUploadOption'
import { parseFormError } from '@/libraries/axios/common/parser'
import { uploadImage } from '@/libraries/axios/image'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import VariationImageEditor from '@/pages/admin/item/variation/VariationImageEditor.vue'
import { useItemStore } from '@/stores/item.store'
import type {
    AppPageProps,
    Identifier,
    Item,
    Variation,
    VariationFormData,
} from '@/types'
import { useForm, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        item: Item
        variation?: Variation | null
    }>(),
    {
        variation: null,
    }
)

const itemStore = useItemStore()
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())
const { locale: activeLanguage, t } = useI18n()

const isDialogVisible = ref(false)
const isSaving = ref(false)
const pendingVariationImage = ref<File | null>(null)
const pendingUploadedImageId = ref<Identifier | null>(null)
const persistedCreatedVariationId = ref<Identifier | null>(null)

interface VariationDraft extends Omit<
    VariationFormData,
    'price' | 'weight' | 'stock'
> {
    price: number | null
    weight: number | null
    stock: number | null
}

const emptyVariation = (): VariationDraft => ({
    barcode: '',
    name: '',
    name_en: '',
    price: 0.01,
    sale_price: null,
    weight: 0,
    stock: 0,
})

const form = useForm<VariationDraft>(emptyVariation())

const canUpdateItem = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= EDITOR
})

const isEditing = computed(() => props.variation !== null)

const localizedVariationName = computed(() => {
    return props.variation
        ? getLocalizedName(props.variation, activeLanguage.value)
        : ''
})

const dialogHeader = computed(() => {
    return isEditing.value
        ? t('admin.items.variation.edit-title')
        : t('admin.items.variation.create-title')
})

const inputIdPrefix = computed(() => {
    return props.variation
        ? `variation-${props.variation.id}`
        : `variation-new-${props.item.id}`
})

const hasValue = (value: string): boolean => value.trim().length > 0

const isFormValid = computed(() => {
    const hasSalePrice = form.sale_price !== null
    const price = form.price
    const weight = form.weight
    const stock = form.stock

    return (
        hasValue(form.barcode) &&
        hasValue(form.name) &&
        hasValue(form.name_en) &&
        price !== null &&
        Number.isFinite(price) &&
        price >= 0.01 &&
        (!hasSalePrice ||
            (Number.isFinite(form.sale_price as number) &&
                (form.sale_price as number) >= 0 &&
                (form.sale_price as number) <= price)) &&
        weight !== null &&
        Number.isFinite(weight) &&
        weight >= 0 &&
        stock !== null &&
        Number.isInteger(stock) &&
        stock >= 0
    )
})

const openDialog = (): void => {
    const draft = props.variation
        ? {
              barcode: props.variation.barcode,
              name: props.variation.name,
              name_en: props.variation.name_en,
              price: props.variation.price,
              sale_price: props.variation.sale_price,
              weight: props.variation.weight,
              stock: props.variation.stock,
          }
        : emptyVariation()

    Object.assign(form, structuredClone(draft))
    form.clearErrors()
    pendingVariationImage.value = null
    pendingUploadedImageId.value = null
    persistedCreatedVariationId.value = null
    isDialogVisible.value = true
}

const normalizedFormData = (): VariationFormData => ({
    barcode: form.barcode.trim(),
    name: form.name.trim(),
    name_en: form.name_en.trim(),
    price: Number(form.price),
    sale_price: form.sale_price === null ? null : Number(form.sale_price),
    weight: Number(form.weight),
    stock: Number(form.stock),
})

watch(pendingVariationImage, () => {
    pendingUploadedImageId.value = null
})

const onSubmit = async (): Promise<void> => {
    if (!isFormValid.value) {
        return
    }

    let isUploadingVariationImage = false

    try {
        isSaving.value = true
        form.clearErrors()

        let persistedVariationId: Identifier

        if (props.variation) {
            await itemStore.onUpdateVariation(
                props.item.id,
                props.variation.id,
                normalizedFormData()
            )
            persistedVariationId = props.variation.id
        } else if (persistedCreatedVariationId.value) {
            await itemStore.onUpdateVariation(
                props.item.id,
                persistedCreatedVariationId.value,
                normalizedFormData()
            )
            persistedVariationId = persistedCreatedVariationId.value
        } else {
            const createdVariation = await itemStore.onCreateVariation(
                props.item.id,
                normalizedFormData()
            )

            persistedCreatedVariationId.value = createdVariation.id
            persistedVariationId = createdVariation.id
        }

        if (pendingVariationImage.value) {
            isUploadingVariationImage = true

            if (!pendingUploadedImageId.value) {
                const uploadedImage = await uploadImage(
                    pendingVariationImage.value,
                    WHITE_EDGE
                )

                pendingUploadedImageId.value = uploadedImage.id
            }

            await itemStore.onUploadVariationImage(
                props.item.id,
                persistedVariationId,
                pendingUploadedImageId.value
            )
            pendingVariationImage.value = null
        }

        toast.success(
            isEditing.value
                ? t('admin.items.variation.updated-success')
                : t('admin.items.variation.created-success'),
            t('common.notifications.success')
        )

        persistedCreatedVariationId.value = null
        isDialogVisible.value = false
    } catch (error) {
        if (isUploadingVariationImage) {
            toast.axiosError(
                error,
                t('admin.items.variation.photo-upload-failed'),
                t('common.notifications.error')
            )
            console.error(error)

            return
        }

        form.errors = parseFormError(error)
        toast.error(
            isEditing.value
                ? t('admin.items.variation.update-failed')
                : t('admin.items.variation.create-failed'),
            t('common.notifications.error')
        )
        console.error(error)
    } finally {
        isSaving.value = false
    }
}
</script>

<template>
    <Button
        v-if="variation"
        :aria-label="
            t('admin.items.variation.edit-variation', {
                name: localizedVariationName,
            })
        "
        :data-testid="`edit-variation-${variation.id}`"
        :disabled="!canUpdateItem"
        :label="t('common.actions.edit')"
        icon="pi pi-pen-to-square"
        size="small"
        @click="openDialog"
    />
    <Button
        v-else
        :aria-label="t('admin.items.variation.add-variation')"
        :data-testid="`add-variation-${item.id}`"
        :disabled="!canUpdateItem"
        :label="t('admin.items.variation.add-variation')"
        class="py-5"
        fluid
        icon="pi pi-plus"
        outlined
        @click="openDialog"
    />

    <Dialog
        v-model:visible="isDialogVisible"
        :closable="!isSaving"
        :close-on-escape="!isSaving"
        :draggable="false"
        :header="dialogHeader"
        :style="{ width: 'min(36rem, calc(100vw - 2rem))' }"
        modal
    >
        <form class="flex flex-col gap-3" @submit.prevent="onSubmit">
            <VariationImageEditor
                v-model:pending-image="pendingVariationImage"
                :disabled="!canUpdateItem || isSaving"
                :item-id="item.id"
                :variation="variation"
            />

            <IftaInputText
                v-model="form.barcode"
                :error="form.errors.barcode"
                :input-id="`${inputIdPrefix}-barcode`"
                :label="t('admin.items.variation.barcode')"
                input-class="w-full"
                required
            />

            <IftaInputText
                v-model="form.name"
                :error="form.errors.name"
                :input-id="`${inputIdPrefix}-name`"
                :label="t('admin.items.variation.name')"
                input-class="w-full"
                required
            />

            <IftaInputText
                v-model="form.name_en"
                :error="form.errors.name_en"
                :input-id="`${inputIdPrefix}-name-en`"
                :label="t('admin.items.variation.name-en')"
                input-class="w-full"
                required
            />

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <IftaInputText
                    v-model.number="form.price"
                    :error="form.errors.price"
                    :input-id="`${inputIdPrefix}-price`"
                    :label="t('admin.items.variation.price')"
                    :min="0.01"
                    :step="0.01"
                    input-class="w-full"
                    required
                    type="number"
                />

                <IftaInputText
                    v-model.number="form.sale_price"
                    :error="form.errors.sale_price"
                    :input-id="`${inputIdPrefix}-sale-price`"
                    :label="t('admin.items.variation.sale-price')"
                    :max="form.price ?? undefined"
                    :min="0"
                    :step="0.01"
                    input-class="w-full"
                    type="number"
                />
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <IftaInputText
                    v-model.number="form.weight"
                    :error="form.errors.weight"
                    :input-id="`${inputIdPrefix}-weight`"
                    :label="t('admin.items.variation.weight')"
                    :min="0"
                    :step="0.001"
                    input-class="w-full"
                    required
                    type="number"
                />

                <IftaInputText
                    v-model.number="form.stock"
                    :error="form.errors.stock"
                    :input-id="`${inputIdPrefix}-stock`"
                    :label="t('admin.items.variation.stock')"
                    :min="0"
                    :step="1"
                    input-class="w-full"
                    required
                    type="number"
                />
            </div>

            <small class="text-surface-500">
                {{ t('admin.items.variation.save-requirements') }}
            </small>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="isSaving"
                    :label="t('common.actions.cancel')"
                    severity="secondary"
                    type="button"
                    @click="isDialogVisible = false"
                />
                <Button
                    :data-testid="
                        variation
                            ? `save-variation-${variation.id}`
                            : `create-variation-${item.id}`
                    "
                    :disabled="!canUpdateItem || !isFormValid"
                    :icon="variation ? 'pi pi-save' : 'pi pi-plus'"
                    :label="
                        variation
                            ? t('common.actions.save')
                            : t('common.actions.create')
                    "
                    :loading="isSaving"
                    type="submit"
                />
            </div>
        </form>
    </Dialog>
</template>
