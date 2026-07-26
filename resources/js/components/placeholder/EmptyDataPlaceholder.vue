<script setup lang="ts">
import noResultsImage from '@/assets/images/placeholders/no-result.png'
import { useI18n } from 'vue-i18n'

withDefaults(
    defineProps<{
        text?: string
    }>(),
    {
        text: undefined,
    }
)

const { t } = useI18n()
</script>

<template>
    <div class="flex justify-center align-center py-10">
        <div class="flex flex-col">
            <div class="mb-5" :style="{ width: '150px', height: '150px' }">
                <img :src="noResultsImage" :alt="t('common.alt.no-results')" />
            </div>

            <div class="flex justify-center text-xl">
                {{ text ?? t('common.placeholders.no-results') }}
            </div>
        </div>
    </div>
</template>
