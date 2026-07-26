<script setup lang="ts">
import FloatInputText from '@/components/input/FloatInputText.vue'
import { getLocalizedName } from '@/libraries/i18n/language'
import type { Category, Origin } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

type ElementTag = Category | Origin

const props = withDefaults(
    defineProps<{
        elements?: ElementTag[]
        subject?: string
        onDelete?: (element: ElementTag) => void
        onUpdate?: (element: ElementTag, name: string, nameEn: string) => void
    }>(),
    {
        elements: () => [],
        subject: '',
        onDelete: () => {},
        onUpdate: () => {},
    }
)

const name = ref('')
const nameEn = ref('')

const visible = ref(false)
const selectedElement = ref<ElementTag | null>(null)
const { locale: activeLanguage, t } = useI18n()

const onEdit = (element: ElementTag): void => {
    visible.value = true
    selectedElement.value = element
    name.value = element.name
    nameEn.value = element.name_en ?? ''
}

const getElementLabel = (element: ElementTag): string => {
    return getLocalizedName(element, activeLanguage.value)
}
</script>

<template>
    <div class="pill-container shadow-lg">
        <div class="p-2 space-x-1 space-y-1">
            <Tag
                v-for="element in elements"
                :value="getElementLabel(element)"
                :key="element.id"
                icon="pi pi-pencil"
                class="cursor-pointer"
                rounded
                @click="onEdit(element)"
            />
        </div>
    </div>

    <Dialog
        v-model:visible="visible"
        :header="
            t('admin.settings.edit-subject', {
                subject,
                name: selectedElement ? getElementLabel(selectedElement) : '',
            })
        "
        modal
    >
        <div class="space-y-6 mt-5">
            <FloatInputText
                v-model="name"
                class="w-full"
                id="name"
                :label="t('admin.settings.name-zh')"
            />

            <FloatInputText
                v-model="nameEn"
                class="w-full"
                id="nameEn"
                :label="t('admin.settings.name-en')"
            />
        </div>

        <template #footer>
            <div class="flex justify-end space-x-2">
                <Button
                    icon="pi pi-trash"
                    :label="t('common.actions.delete')"
                    :disabled="!selectedElement"
                    @click="selectedElement && onDelete(selectedElement)"
                    severity="danger"
                    outlined
                />

                <Button
                    icon="pi pi-save"
                    :label="t('common.actions.save')"
                    :disabled="!selectedElement"
                    @click="
                        selectedElement &&
                        onUpdate(selectedElement, name, nameEn)
                    "
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
@reference "../../../../css/app.css";

.pill-container {
    @apply border border-gray-300 rounded-lg bg-white min-h-[100px];
}

.pill-container span {
    @apply cursor-pointer text-base bg-purple-500 text-white;
}
</style>
