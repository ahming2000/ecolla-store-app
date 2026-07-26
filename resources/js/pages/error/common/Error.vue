<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        status?: string | number
        homePageRedirectLink?: string
    }>(),
    {
        status: undefined,
        homePageRedirectLink: '/',
    }
)

const { t } = useI18n()

const errorKey = computed(() => {
    const status = props.status?.toString()

    return ['401', '403', '404'].includes(status ?? '') ? status : 'default'
})

const statusTitle = computed(() => {
    return t(`common.errors.${errorKey.value}.title`)
})

const statusDesc = computed(() => {
    return t(`common.errors.${errorKey.value}.description`)
})

const onRedirectToHomeClick = () => {
    router.visit(props.homePageRedirectLink)
}

const onWhatsAppContactingClick = () => {
    window.open('https://wa.link/fcfum1', '_blank')
}
</script>

<template>
    <div class="bg-gradient-to-r from-purple-300 to-blue-200">
        <div
            class="w-9/12 m-auto py-16 min-h-screen flex items-center justify-center"
        >
            <div class="bg-white shadow overflow-hidden sm:rounded-lg pb-8">
                <div class="border-t border-gray-200 text-center pt-8">
                    <h1 class="text-9xl font-bold text-purple-400">
                        {{ status }}
                    </h1>

                    <h1 class="text-6xl font-medium py-8">
                        {{ statusTitle }}
                    </h1>

                    <p class="text-2xl pb-8 px-12 font-medium">
                        {{ statusDesc }}
                    </p>

                    <Button
                        :label="t('common.actions.home')"
                        :pt="{
                            root: 'bg-gradient-to-r from-purple-400 to-blue-500 hover:from-pink-500 hover:to-orange-500 text-white font-semibold px-6 py-3 rounded-md mr-6',
                        }"
                        @click="onRedirectToHomeClick"
                    />

                    <Button
                        :label="t('common.actions.contact')"
                        :pt="{
                            root: 'bg-gradient-to-r from-red-400 to-red-500 hover:from-red-500 hover:to-red-500 text-white font-semibold px-6 py-3 rounded-md',
                        }"
                        @click="onWhatsAppContactingClick"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
