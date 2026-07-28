<script setup lang="ts">
import FloatInputText from '@/components/input/FloatInputText.vue'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import {
    destroy as destroyCategory,
    store as storeCategory,
    update as updateCategory,
} from '@/routes/admin/setting/category'
import {
    destroy as destroyOrigin,
    store as storeOrigin,
    update as updateOrigin,
} from '@/routes/admin/setting/origin'
import type { Category, FormErrors, Identifier, Origin } from '@/types'
import { useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import { useToast } from 'primevue/usetoast'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

type ElementTag = Category | Origin
type ElementType = 'category' | 'origin'

const props = withDefaults(
    defineProps<{
        addLabel: string
        canManage?: boolean
        defaultElementId?: Identifier | null
        elements?: ElementTag[]
        elementType: ElementType
        subject: string
        title: string
    }>(),
    {
        canManage: false,
        defaultElementId: null,
        elements: () => [],
    }
)

const { locale: activeLanguage, t } = useI18n()
const toast = Notification.init(useToast())

const isDeleteDialogVisible = ref(false)
const isUpsertDialogVisible = ref(false)
const selectedElement = ref<ElementTag | null>(null)

const upsertForm = useForm({
    name: '',
    name_en: '',
})
const deleteForm = useForm({})

const upsertDialogHeader = computed(() => {
    if (!selectedElement.value) {
        return t('admin.settings.create-subject', {
            subject: props.subject,
        })
    }

    return t('admin.settings.edit-subject', {
        subject: props.subject,
        name: getElementLabel(selectedElement.value),
    })
})

const isDefaultElement = computed(() => {
    return (
        selectedElement.value !== null &&
        props.defaultElementId !== null &&
        selectedElement.value.id === props.defaultElementId
    )
})

const itemCount = computed(() => {
    return selectedElement.value?.items_count ?? 0
})

const isDeleteDisabled = computed(() => {
    return isDefaultElement.value || itemCount.value > 0
})

const deleteDisabledReason = computed(() => {
    if (isDefaultElement.value) {
        return t('admin.settings.default-category-delete-disabled')
    }

    if (itemCount.value > 0) {
        return t('admin.settings.catalog-in-use', {
            subject: props.subject.toLocaleLowerCase(),
            count: itemCount.value,
        })
    }

    return ''
})

const deleteError = computed(() => {
    return (deleteForm.errors as FormErrors)[props.elementType]
})

const getElementLabel = (element: ElementTag): string => {
    return getLocalizedName(element, activeLanguage.value)
}

const openCreateDialog = (): void => {
    if (!props.canManage) {
        return
    }

    selectedElement.value = null
    upsertForm.reset()
    upsertForm.clearErrors()
    isUpsertDialogVisible.value = true
}

const openEditDialog = (element: ElementTag): void => {
    if (!props.canManage) {
        return
    }

    selectedElement.value = element
    upsertForm.name = element.name
    upsertForm.name_en = element.name_en ?? ''
    upsertForm.clearErrors()
    deleteForm.clearErrors()
    isUpsertDialogVisible.value = true
}

const onSubmit = (): void => {
    if (!props.canManage) {
        return
    }

    const wasCreating = selectedElement.value === null
    const action =
        props.elementType === 'category'
            ? wasCreating
                ? storeCategory()
                : updateCategory(selectedElement.value!.id)
            : wasCreating
              ? storeOrigin()
              : updateOrigin(selectedElement.value!.id)

    upsertForm.submit(action, {
        preserveScroll: true,
        onSuccess: () => {
            isUpsertDialogVisible.value = false
            toast.success(
                t(
                    wasCreating
                        ? 'admin.settings.catalog-created-success'
                        : 'admin.settings.catalog-updated-success',
                    { subject: props.subject }
                ),
                t('common.notifications.success')
            )
        },
        onError: () => {
            toast.error(
                t('admin.settings.catalog-save-failed', {
                    subject: props.subject.toLocaleLowerCase(),
                }),
                t('common.notifications.error')
            )
        },
    })
}

const openDeleteDialog = (): void => {
    if (!selectedElement.value || isDeleteDisabled.value) {
        return
    }

    deleteForm.clearErrors()
    isDeleteDialogVisible.value = true
}

const onDelete = (): void => {
    if (!selectedElement.value || isDeleteDisabled.value) {
        return
    }

    const action =
        props.elementType === 'category'
            ? destroyCategory(selectedElement.value.id)
            : destroyOrigin(selectedElement.value.id)

    deleteForm.submit(action, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogVisible.value = false
            isUpsertDialogVisible.value = false
            toast.success(
                t('admin.settings.catalog-deleted-success', {
                    subject: props.subject,
                }),
                t('common.notifications.success')
            )
        },
        onError: () => {
            toast.error(
                t('admin.settings.catalog-delete-failed', {
                    subject: props.subject.toLocaleLowerCase(),
                }),
                t('common.notifications.error')
            )
        },
    })
}
</script>

<template>
    <section>
        <div class="mb-2 flex items-center justify-between gap-3">
            <h2 class="text-2xl">
                {{ title }}
            </h2>

            <Button
                :data-testid="`add-${elementType}`"
                :disabled="!canManage"
                icon="pi pi-plus"
                :label="addLabel"
                size="small"
                type="button"
                @click="openCreateDialog"
            />
        </div>

        <div class="pill-container shadow-lg">
            <div class="flex flex-wrap gap-1 p-2">
                <Tag
                    v-for="element in elements"
                    :key="element.id"
                    :aria-label="
                        canManage
                            ? t('admin.settings.edit-subject', {
                                  subject,
                                  name: getElementLabel(element),
                              })
                            : getElementLabel(element)
                    "
                    :class="canManage ? 'cursor-pointer' : 'cursor-default'"
                    :data-testid="`${elementType}-${element.id}`"
                    :icon="canManage ? 'pi pi-pencil' : undefined"
                    rounded
                    :value="getElementLabel(element)"
                    @click="openEditDialog(element)"
                />
            </div>
        </div>

        <Dialog
            v-model:visible="isUpsertDialogVisible"
            :closable="!upsertForm.processing && !deleteForm.processing"
            :close-on-escape="!upsertForm.processing && !deleteForm.processing"
            :header="upsertDialogHeader"
            :style="{ width: '28rem' }"
            modal
        >
            <form class="flex flex-col gap-6 pt-5" @submit.prevent="onSubmit">
                <FloatInputText
                    v-model="upsertForm.name"
                    class="w-full"
                    :error="upsertForm.errors.name"
                    :id="`${elementType}-name`"
                    input-class="w-full"
                    :label="t('admin.settings.name-zh')"
                    required
                />

                <FloatInputText
                    v-model="upsertForm.name_en"
                    class="w-full"
                    :error="upsertForm.errors.name_en"
                    :id="`${elementType}-name-en`"
                    input-class="w-full"
                    :label="t('admin.settings.name-en')"
                    required
                />

                <div class="flex flex-wrap justify-end gap-2">
                    <Button
                        v-if="selectedElement"
                        :disabled="isDeleteDisabled || upsertForm.processing"
                        icon="pi pi-trash"
                        :label="t('common.actions.delete')"
                        outlined
                        severity="danger"
                        type="button"
                        @click="openDeleteDialog"
                    />

                    <Button
                        :data-testid="`save-${elementType}`"
                        icon="pi pi-save"
                        :label="t('common.actions.save')"
                        :loading="upsertForm.processing"
                        type="submit"
                    />
                </div>

                <small
                    v-if="selectedElement && deleteDisabledReason"
                    class="text-right text-gray-600"
                >
                    {{ deleteDisabledReason }}
                </small>
            </form>
        </Dialog>

        <Dialog
            v-model:visible="isDeleteDialogVisible"
            :closable="!deleteForm.processing"
            :close-on-escape="!deleteForm.processing"
            :header="
                t('admin.settings.delete-subject', {
                    subject,
                })
            "
            :style="{ width: '28rem' }"
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
                            t('admin.settings.catalog-delete-confirmation', {
                                subject,
                                name: selectedElement
                                    ? getElementLabel(selectedElement)
                                    : '',
                            })
                        }}
                    </p>
                </div>

                <small v-if="deleteError" class="text-red-700">
                    {{ deleteError }}
                </small>

                <div class="flex justify-end gap-2">
                    <Button
                        :disabled="deleteForm.processing"
                        :label="t('common.actions.cancel')"
                        severity="secondary"
                        type="button"
                        @click="isDeleteDialogVisible = false"
                    />
                    <Button
                        :data-testid="`confirm-delete-${elementType}`"
                        :label="t('common.actions.delete')"
                        :loading="deleteForm.processing"
                        severity="danger"
                        type="button"
                        @click="onDelete"
                    />
                </div>
            </div>
        </Dialog>
    </section>
</template>

<style scoped>
@reference "../../../../css/app.css";

.pill-container {
    @apply min-h-[100px] rounded-lg border border-gray-300 bg-white;
}

.pill-container span {
    @apply bg-purple-500 text-base text-white;
}
</style>
