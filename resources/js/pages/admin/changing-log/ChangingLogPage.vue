<script setup lang="ts">
import Admin from '@/layouts/Admin.vue'
import {
    DEFAULT_LANGUAGE,
    isSupportedLanguage,
} from '@/libraries/i18n/language'
import type { SupportedLanguage } from '@/types'
import { Head } from '@inertiajs/vue3'
import Accordion from 'primevue/accordion'
import AccordionContent from 'primevue/accordioncontent'
import AccordionHeader from 'primevue/accordionheader'
import AccordionPanel from 'primevue/accordionpanel'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

interface ChangeLogDetail {
    type: string
    desc: string[]
}

interface ChangeLogSubGroup {
    name: string
    date: string
    details: ChangeLogDetail[]
}

interface ChangeLog {
    groupName: string
    subGroups: ChangeLogSubGroup[]
}

interface ChangeLogNotes {
    versionLabel: string
    updateDate: string
    logs: ChangeLog[]
}

const props = defineProps<{
    notes: Record<SupportedLanguage, ChangeLogNotes>
}>()

const { locale, t } = useI18n()

const selectedNotes = computed(() => {
    const language = isSupportedLanguage(locale.value)
        ? locale.value
        : DEFAULT_LANGUAGE

    return props.notes[language]
})
</script>

<template>
    <Head :title="t('admin.change-log.title')" />

    <div class="container mx-auto my-3">
        <div class="text-center mb-2">
            {{ t('admin.change-log.current-version') }}
        </div>

        <div class="text-2xl text-center text-green-600 mb-2">
            {{ selectedNotes.versionLabel }}
        </div>

        <div class="text-center mb-2">
            {{
                t('admin.change-log.updated-at', {
                    date: selectedNotes.updateDate,
                })
            }}
        </div>

        <div class="bg-white mx-5">
            <Accordion :value="0">
                <AccordionPanel
                    v-for="(log, index) in selectedNotes.logs"
                    :key="log.groupName"
                    :value="index"
                >
                    <AccordionHeader>{{ log.groupName }}</AccordionHeader>

                    <AccordionContent>
                        <div
                            v-for="group in log.subGroups"
                            :key="group.name"
                            class="mb-3"
                        >
                            <div class="text-2xl">
                                {{
                                    t('admin.change-log.version-date', {
                                        name: group.name,
                                        date: group.date,
                                    })
                                }}
                            </div>

                            <div
                                v-for="detail in group.details"
                                :key="detail.type"
                                class="mb-2"
                            >
                                <div>{{ detail.type }}</div>

                                <ul>
                                    <li v-for="desc in detail.desc" :key="desc">
                                        <span class="ml-3 mr-2">•</span>
                                        <span>{{ desc }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionPanel>
            </Accordion>
        </div>
    </div>
</template>
