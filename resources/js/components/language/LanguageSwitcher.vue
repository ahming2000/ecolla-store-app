<script setup lang="ts">
import {
    isSupportedLanguage,
    SHOP_LANGUAGE_STORAGE_KEY,
    storeLanguage,
} from '@/libraries/i18n/language'
import { update as updateLanguage } from '@/routes/admin/lang'
import type { SupportedLanguage } from '@/types'
import { router } from '@inertiajs/vue3'
import Select from 'primevue/select'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        persistence?: 'local' | 'server'
        storageKey?: string
    }>(),
    {
        persistence: 'local',
        storageKey: SHOP_LANGUAGE_STORAGE_KEY,
    }
)

const { locale: activeLanguage, t } = useI18n()
const isLoading = ref(false)

const languageOptions = computed(() => [
    {
        label: t('common.language.english'),
        value: 'en' satisfies SupportedLanguage,
    },
    {
        label: t('common.language.chinese'),
        value: 'zh' satisfies SupportedLanguage,
    },
])

const applyLanguage = (language: SupportedLanguage): void => {
    activeLanguage.value = language
    document.documentElement.lang = language
}

const onLanguageChange = (value: unknown): void => {
    if (!isSupportedLanguage(value) || value === activeLanguage.value) {
        return
    }

    if (props.persistence === 'local') {
        storeLanguage(props.storageKey, value)
        applyLanguage(value)

        return
    }

    router.put(
        updateLanguage.url(),
        { lang: value },
        {
            preserveScroll: true,
            onStart: () => {
                isLoading.value = true
            },
            onSuccess: () => {
                applyLanguage(value)
            },
            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}
</script>

<template>
    <div class="flex items-center gap-2 px-3 py-1">
        <i class="pi pi-globe text-gray-500" aria-hidden="true"></i>

        <Select
            class="w-28 sm:w-32"
            :aria-label="t('common.language.label')"
            :loading="isLoading"
            :model-value="activeLanguage"
            option-label="label"
            option-value="value"
            :options="languageOptions"
            size="small"
            @update:model-value="onLanguageChange"
        />
    </div>
</template>
