<script setup lang="ts">
import FloatInputText from '@/components/input/FloatInputText.vue'
import { EDITOR } from '@/enums/AccessLevel'
import { parseFormError } from '@/libraries/axios/common/parser'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import type { AppPageProps, Category, FormErrors, Item, Origin } from '@/types'
import { useForm, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import MultiSelect from 'primevue/multiselect'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
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

const isSaving = ref(false)
const validationErrors = ref<FormErrors>({})

interface ItemDetailDraft {
    name: string
    name_en: string
    desc: string
    origin: Origin | null
    categories: Category[]
}

const basicInfoForm = useForm<ItemDetailDraft>({
    name: props.item.name,
    name_en: props.item.name_en ?? '',
    desc: props.item.desc ?? '',
    origin: props.item.origin ?? null,
    categories: [...props.item.categories],
})

const canUpdateItem = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= EDITOR
})

const canSubmit = computed(() => {
    return canUpdateItem.value && basicInfoForm.name.trim().length > 0
})

const categoryError = computed(() => {
    return (
        validationErrors.value.category_ids ??
        validationErrors.value['category_ids.0']
    )
})

const getCategoryLabel = (category: Category): string => {
    if (activeLanguage.value === 'en' && category.name_en) {
        return category.name_en
    }

    return category.name
}

const onSubmit = async (): Promise<void> => {
    if (!canSubmit.value) {
        return
    }

    try {
        isSaving.value = true
        basicInfoForm.clearErrors()
        validationErrors.value = {}

        await itemStore.onUpdateItemDetail(props.item.id, {
            name: basicInfoForm.name.trim(),
            name_en: basicInfoForm.name_en.trim() || null,
            desc: basicInfoForm.desc.trim() || null,
            origin_id: basicInfoForm.origin?.id ?? null,
            category_ids: basicInfoForm.categories.map(
                (category) => category.id
            ),
        })

        Object.assign(basicInfoForm, {
            name: props.item.name,
            name_en: props.item.name_en ?? '',
            desc: props.item.desc ?? '',
            origin: props.item.origin ?? null,
            categories: [...props.item.categories],
        })
        basicInfoForm.defaults(basicInfoForm.data())
        toast.success(
            t('admin.items.updated-success'),
            t('common.notifications.success')
        )
    } catch (error) {
        validationErrors.value = parseFormError(error)
        toast.error(
            t('admin.items.update-failed'),
            t('common.notifications.error')
        )
        console.error(error)
    } finally {
        isSaving.value = false
    }
}
</script>

<template>
    <form class="flex flex-col gap-3 pt-3" @submit.prevent="onSubmit">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <FloatInputText
                v-model="basicInfoForm.name"
                :error="validationErrors.name"
                :id="`item-${item.id}-name`"
                :label="t('admin.items.fields.name')"
                input-class="w-full"
                required
            />

            <FloatInputText
                v-model="basicInfoForm.name_en"
                :error="validationErrors.name_en"
                :id="`item-${item.id}-name-en`"
                :label="t('admin.items.fields.name-en')"
                input-class="w-full"
            />
        </div>

        <Textarea
            v-model="basicInfoForm.desc"
            :aria-label="t('admin.items.fields.description')"
            :invalid="!!validationErrors.desc"
            :placeholder="t('admin.items.fields.description')"
            class="w-full"
            rows="5"
        />
        <small v-if="validationErrors.desc">
            {{ validationErrors.desc }}
        </small>

        <Select
            v-model="basicInfoForm.origin"
            :aria-label="t('admin.items.fields.origin')"
            :filter-fields="['name', 'name_en']"
            :invalid="!!validationErrors.origin_id"
            :options="itemStore.origins"
            :placeholder="t('admin.items.fields.origin')"
            class="w-full"
            filter
        >
            <template #value="{ value }">
                <span v-if="value">
                    {{
                        t('admin.items.fields.origin-value', {
                            name: value.name,
                            nameEn: value.name_en,
                        })
                    }}
                </span>
            </template>

            <template #option="{ option }">
                <span v-if="option">
                    {{ `${option.name} - ${option.name_en}` }}
                </span>
            </template>
        </Select>
        <small v-if="validationErrors.origin_id">
            {{ validationErrors.origin_id }}
        </small>

        <MultiSelect
            v-model="basicInfoForm.categories"
            :aria-label="t('admin.items.fields.categories')"
            :filter-fields="['name', 'name_en']"
            :invalid="!!categoryError"
            :option-label="getCategoryLabel"
            :options="itemStore.categories"
            :placeholder="t('admin.items.fields.categories')"
            class="w-full"
            data-key="id"
            display="chip"
            filter
        >
            <template #option="{ option }">
                <span v-if="option">
                    {{ getCategoryLabel(option) }}
                </span>
            </template>
        </MultiSelect>
        <small v-if="categoryError">{{ categoryError }}</small>

        <div class="flex justify-end pt-1">
            <Button
                :aria-label="t('common.actions.save')"
                :data-testid="`update-item-detail-${item.id}`"
                :disabled="!canSubmit"
                :label="t('common.actions.save')"
                :loading="isSaving"
                icon="pi pi-save"
                type="submit"
            />
        </div>
    </form>
</template>
