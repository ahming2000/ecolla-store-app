<script setup lang="ts">
import logoImage from '@/assets/images/branding/ecolla.png'
import { Head, Link } from '@inertiajs/vue3'
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

const statusCode = computed(() => props.status?.toString() ?? '500')

const errorKey = computed(() => {
    return ['401', '403', '404'].includes(statusCode.value)
        ? statusCode.value
        : 'default'
})

const statusTitle = computed(() => {
    return t(`common.errors.${errorKey.value}.title`)
})

const statusDesc = computed(() => {
    return t(`common.errors.${errorKey.value}.description`)
})
</script>

<template>
    <main
        data-testid="error-page"
        class="relative isolate flex min-h-[calc(100vh-4rem)] items-center justify-center overflow-hidden bg-zinc-50 px-4 py-12 dark:bg-zinc-950 sm:px-6 lg:px-8"
    >
        <Head :title="statusTitle" />

        <div
            class="absolute -top-32 right-[-8rem] -z-10 size-80 rounded-full bg-pink-200/60 blur-3xl dark:bg-pink-950/40"
            aria-hidden="true"
        ></div>
        <div
            class="absolute -bottom-40 left-[-10rem] -z-10 size-96 rounded-full bg-rose-100/80 blur-3xl dark:bg-rose-950/30"
            aria-hidden="true"
        ></div>

        <section
            data-testid="error-card"
            class="relative w-full max-w-3xl overflow-hidden rounded-3xl border border-zinc-200 bg-white px-6 py-10 text-center shadow-xl shadow-zinc-900/5 dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-black/20 sm:px-10 sm:py-12"
            aria-labelledby="error-title"
        >
            <div
                class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-pink-300 via-pink-500 to-rose-400"
                aria-hidden="true"
            ></div>

            <img
                :src="logoImage"
                :alt="t('common.alt.logo')"
                class="mx-auto size-12 rounded-2xl bg-pink-50 p-1.5 ring-1 ring-pink-100 dark:bg-pink-950 dark:ring-pink-900"
            />

            <p
                class="mt-8 text-xs font-bold uppercase tracking-[0.24em] text-pink-600 dark:text-pink-300"
            >
                {{ t('common.notifications.error') }}
            </p>

            <h1
                class="mt-2 text-7xl font-black leading-none tracking-[-0.07em] text-pink-500 sm:text-8xl"
            >
                {{ statusCode }}
            </h1>

            <h2
                id="error-title"
                class="mt-6 text-2xl font-bold tracking-tight text-zinc-950 dark:text-white sm:text-3xl"
            >
                {{ statusTitle }}
            </h2>

            <p
                class="mx-auto mt-3 max-w-xl text-base leading-7 text-zinc-600 dark:text-zinc-300"
            >
                {{ statusDesc }}
            </p>

            <div
                class="mt-8 flex flex-col justify-center gap-3 sm:flex-row sm:items-center"
            >
                <Link
                    :href="homePageRedirectLink"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-pink-500 px-5 py-3 text-sm font-bold text-white shadow-sm shadow-pink-500/20 transition hover:bg-pink-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-pink-200 dark:focus-visible:ring-pink-950"
                >
                    <i class="pi pi-home text-sm" aria-hidden="true"></i>
                    <span>{{ t('common.actions.home') }}</span>
                </Link>

                <a
                    href="https://wa.link/fcfum1"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-white px-5 py-3 text-sm font-bold text-zinc-700 transition hover:border-pink-200 hover:bg-pink-50 hover:text-pink-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-pink-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-pink-900 dark:hover:bg-pink-950/50 dark:hover:text-pink-200 dark:focus-visible:ring-pink-950"
                >
                    <i class="pi pi-whatsapp text-sm" aria-hidden="true"></i>
                    <span>{{ t('common.actions.contact') }}</span>
                </a>
            </div>
        </section>
    </main>
</template>
