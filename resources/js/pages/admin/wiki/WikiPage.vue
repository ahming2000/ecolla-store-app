<script setup lang="ts">
import changeLogImage from '@/assets/images/admin-wiki/change-log.png'
import dashboardImage from '@/assets/images/admin-wiki/dashboard.png'
import itemEditorImage from '@/assets/images/admin-wiki/item-editor.png'
import itemsImage from '@/assets/images/admin-wiki/items.png'
import ordersImage from '@/assets/images/admin-wiki/orders.png'
import profileImage from '@/assets/images/admin-wiki/profile.png'
import settingsImage from '@/assets/images/admin-wiki/settings.png'
import staffImage from '@/assets/images/admin-wiki/staff.png'
import Admin from '@/layouts/Admin.vue'
import { page as changingLogPage } from '@/routes/admin/changing-log'
import { page as dashboardPage } from '@/routes/admin/dashboard'
import { page as itemPage } from '@/routes/admin/item'
import { page as orderPage } from '@/routes/admin/order'
import { page as profilePage } from '@/routes/admin/profile'
import { page as settingPage } from '@/routes/admin/setting'
import { page as userPage } from '@/routes/admin/user'
import { Head, Link } from '@inertiajs/vue3'
import Dialog from 'primevue/dialog'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

const articleDefinitions = [
    {
        id: 'dashboard',
        icon: 'pi pi-chart-line',
        category: 'insights',
        image: dashboardImage,
        href: dashboardPage,
    },
    {
        id: 'catalog',
        icon: 'pi pi-box',
        category: 'catalog',
        image: itemsImage,
        href: itemPage,
    },
    {
        id: 'item-editor',
        icon: 'pi pi-pencil',
        category: 'catalog',
        image: itemEditorImage,
        href: itemPage,
    },
    {
        id: 'orders',
        icon: 'pi pi-shopping-bag',
        category: 'operations',
        image: ordersImage,
        href: orderPage,
    },
    {
        id: 'staff',
        icon: 'pi pi-users',
        category: 'administration',
        image: staffImage,
        href: userPage,
    },
    {
        id: 'settings',
        icon: 'pi pi-cog',
        category: 'administration',
        image: settingsImage,
        href: settingPage,
    },
    {
        id: 'profile',
        icon: 'pi pi-user',
        category: 'account',
        image: profileImage,
        href: profilePage,
    },
    {
        id: 'change-log',
        icon: 'pi pi-history',
        category: 'reference',
        image: changeLogImage,
        href: changingLogPage,
    },
] as const

type ArticleDefinition = (typeof articleDefinitions)[number]
type ArticleRoute = ReturnType<typeof dashboardPage>

type WikiArticle = Omit<ArticleDefinition, 'href'> & {
    access: string
    caption: string
    categoryLabel: string
    href: ArticleRoute
    points: string[]
    summary: string
    title: string
}

const { locale, t } = useI18n()
const query = ref('')
const isScreenshotVisible = ref(false)
const selectedArticle = ref<WikiArticle | null>(null)

const articles = computed<WikiArticle[]>(() =>
    articleDefinitions.map((article) => {
        const translationKey = `admin.wiki.articles.${article.id}`

        return {
            ...article,
            access: t(`${translationKey}.access`),
            caption: t(`${translationKey}.caption`),
            categoryLabel: t(`admin.wiki.categories.${article.category}`),
            href: article.href(),
            points: [1, 2, 3].map((point) =>
                t(`${translationKey}.points.${point}`)
            ),
            summary: t(`${translationKey}.summary`),
            title: t(`${translationKey}.title`),
        }
    })
)

const normalizedQuery = computed(() =>
    query.value.trim().toLocaleLowerCase(locale.value)
)

const visibleArticles = computed(() => {
    if (!normalizedQuery.value) {
        return articles.value
    }

    return articles.value.filter((article) => {
        const searchableText = [
            article.title,
            article.summary,
            article.categoryLabel,
            ...article.points,
        ]
            .join(' ')
            .toLocaleLowerCase(locale.value)

        return searchableText.includes(normalizedQuery.value)
    })
})

const openScreenshot = (article: WikiArticle): void => {
    selectedArticle.value = article
    isScreenshotVisible.value = true
}
</script>

<template>
    <Head :title="t('admin.wiki.title')" />

    <main class="min-h-screen">
        <section
            class="border-b border-pink-100 bg-gradient-to-br from-white via-pink-50 to-amber-50 px-4 py-10 dark:border-zinc-800 dark:from-zinc-950 dark:via-zinc-900 dark:to-pink-950/30 sm:px-6 lg:py-14"
        >
            <div class="container mx-auto max-w-7xl">
                <div class="max-w-3xl">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full bg-pink-100 px-3 py-1 text-sm font-semibold text-pink-700 dark:bg-pink-950 dark:text-pink-200"
                    >
                        <i class="pi pi-book" aria-hidden="true"></i>
                        <span>{{ t('admin.wiki.eyebrow') }}</span>
                    </div>

                    <h1
                        class="text-4xl font-bold tracking-tight text-zinc-950 dark:text-white sm:text-5xl"
                    >
                        {{ t('admin.wiki.title') }}
                    </h1>

                    <p
                        class="mt-4 max-w-2xl text-base leading-7 text-zinc-600 dark:text-zinc-300 sm:text-lg"
                    >
                        {{ t('admin.wiki.description') }}
                    </p>
                </div>

                <div class="mt-8 max-w-2xl">
                    <label
                        class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200"
                        for="wiki-search"
                    >
                        {{ t('admin.wiki.search-label') }}
                    </label>

                    <div class="relative">
                        <i
                            class="pi pi-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400"
                            aria-hidden="true"
                        ></i>
                        <input
                            id="wiki-search"
                            v-model="query"
                            class="w-full rounded-2xl border border-zinc-200 bg-white py-4 pl-11 pr-12 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-pink-400 focus:ring-4 focus:ring-pink-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-pink-500 dark:focus:ring-pink-950"
                            :placeholder="t('admin.wiki.search-placeholder')"
                            type="search"
                        />
                        <button
                            v-if="query"
                            :aria-label="t('admin.wiki.clear-search')"
                            class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-pink-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                            type="button"
                            @click="query = ''"
                        >
                            <i class="pi pi-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <p
                        class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400"
                        aria-live="polite"
                    >
                        {{
                            t('admin.wiki.result-count', {
                                visible: visibleArticles.length,
                                total: articles.length,
                            })
                        }}
                    </p>
                </div>
            </div>
        </section>

        <div
            class="container mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[17rem_minmax(0,1fr)] lg:py-12"
        >
            <aside class="hidden self-start lg:sticky lg:top-5 lg:block">
                <nav
                    class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-950"
                    :aria-label="t('admin.wiki.contents')"
                >
                    <p
                        class="px-3 pb-2 pt-1 text-xs font-bold uppercase tracking-[0.18em] text-zinc-400"
                    >
                        {{ t('admin.wiki.contents') }}
                    </p>

                    <a
                        v-for="article in articles"
                        :key="article.id"
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-zinc-600 transition hover:bg-pink-50 hover:text-pink-700 dark:text-zinc-300 dark:hover:bg-pink-950/40 dark:hover:text-pink-200"
                        :href="`#${article.id}`"
                    >
                        <i
                            :class="article.icon"
                            class="text-zinc-400 transition group-hover:text-pink-500"
                            aria-hidden="true"
                        ></i>
                        <span>{{ article.title }}</span>
                    </a>
                </nav>
            </aside>

            <div class="min-w-0">
                <div v-if="visibleArticles.length" class="flex flex-col gap-6">
                    <article
                        v-for="(article, index) in visibleArticles"
                        :id="article.id"
                        :key="article.id"
                        class="scroll-mt-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950"
                        data-testid="wiki-article"
                    >
                        <div class="p-5 sm:p-7">
                            <header
                                class="flex flex-col gap-4 border-b border-zinc-100 pb-5 dark:border-zinc-800 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="flex min-w-0 items-start gap-4">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-pink-100 text-xl text-pink-600 dark:bg-pink-950 dark:text-pink-300"
                                    >
                                        <i
                                            :class="article.icon"
                                            aria-hidden="true"
                                        ></i>
                                    </div>

                                    <div>
                                        <p
                                            class="mb-1 text-xs font-bold uppercase tracking-[0.16em] text-pink-600 dark:text-pink-300"
                                        >
                                            {{ article.categoryLabel }}
                                        </p>
                                        <h2
                                            class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-white"
                                        >
                                            {{ article.title }}
                                        </h2>
                                    </div>
                                </div>

                                <span
                                    class="w-fit shrink-0 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400"
                                >
                                    {{
                                        t('admin.wiki.guide-number', {
                                            number: String(index + 1).padStart(
                                                2,
                                                '0'
                                            ),
                                        })
                                    }}
                                </span>
                            </header>

                            <div
                                class="mt-6 grid items-start gap-7 xl:grid-cols-[minmax(0,1fr)_23rem]"
                            >
                                <div>
                                    <p
                                        class="text-base leading-7 text-zinc-600 dark:text-zinc-300"
                                    >
                                        {{ article.summary }}
                                    </p>

                                    <ol class="mt-6 flex flex-col gap-4">
                                        <li
                                            v-for="(
                                                point, pointIndex
                                            ) in article.points"
                                            :key="point"
                                            class="flex gap-3"
                                        >
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-xs font-bold text-white dark:bg-white dark:text-zinc-950"
                                            >
                                                {{ pointIndex + 1 }}
                                            </span>
                                            <span
                                                class="pt-0.5 text-sm leading-6 text-zinc-700 dark:text-zinc-300"
                                            >
                                                {{ point }}
                                            </span>
                                        </li>
                                    </ol>
                                </div>

                                <figure class="min-w-0">
                                    <button
                                        :aria-label="
                                            t('admin.wiki.open-screenshot', {
                                                title: article.title,
                                            })
                                        "
                                        class="group block w-full overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 text-left shadow-sm focus:outline-none focus:ring-4 focus:ring-pink-200 dark:border-zinc-700 dark:bg-zinc-900 dark:focus:ring-pink-950"
                                        :data-testid="`wiki-screenshot-${article.id}`"
                                        type="button"
                                        @click="openScreenshot(article)"
                                    >
                                        <img
                                            class="aspect-[16/10] w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                                            :alt="
                                                t('admin.wiki.screenshot-alt', {
                                                    title: article.title,
                                                })
                                            "
                                            :loading="
                                                index === 0 ? 'eager' : 'lazy'
                                            "
                                            :src="article.image"
                                        />
                                        <span
                                            class="flex items-center justify-between gap-3 border-t border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200"
                                        >
                                            <span>{{
                                                t('admin.wiki.view-screenshot')
                                            }}</span>
                                            <i
                                                class="pi pi-expand text-pink-500"
                                                aria-hidden="true"
                                            ></i>
                                        </span>
                                    </button>
                                    <figcaption
                                        class="mt-2 text-xs leading-5 text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{ article.caption }}
                                    </figcaption>
                                </figure>
                            </div>
                        </div>

                        <footer
                            class="flex flex-col gap-4 border-t border-zinc-100 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/60 sm:flex-row sm:items-center sm:justify-between sm:px-7"
                        >
                            <p
                                class="flex items-start gap-2 text-sm text-zinc-500 dark:text-zinc-400"
                            >
                                <i
                                    class="pi pi-shield mt-0.5 text-pink-500"
                                    aria-hidden="true"
                                ></i>
                                <span>{{ article.access }}</span>
                            </p>

                            <Link
                                class="inline-flex items-center gap-2 self-start rounded-xl bg-pink-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-pink-600 focus:outline-none focus:ring-4 focus:ring-pink-200 dark:focus:ring-pink-950 sm:self-auto"
                                :href="article.href"
                            >
                                <span>{{ t('admin.wiki.open-module') }}</span>
                                <i
                                    class="pi pi-arrow-right text-xs"
                                    aria-hidden="true"
                                ></i>
                            </Link>
                        </footer>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-950"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-xl text-zinc-400 dark:bg-zinc-900"
                    >
                        <i class="pi pi-search" aria-hidden="true"></i>
                    </div>
                    <h2
                        class="mt-4 text-xl font-bold text-zinc-900 dark:text-white"
                    >
                        {{ t('admin.wiki.no-results.title') }}
                    </h2>
                    <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                        {{ t('admin.wiki.no-results.description') }}
                    </p>
                    <button
                        class="mt-5 rounded-xl bg-pink-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-pink-600 focus:outline-none focus:ring-4 focus:ring-pink-200 dark:focus:ring-pink-950"
                        type="button"
                        @click="query = ''"
                    >
                        {{ t('admin.wiki.clear-search') }}
                    </button>
                </div>
            </div>
        </div>

        <Dialog
            v-model:visible="isScreenshotVisible"
            modal
            :header="
                selectedArticle
                    ? t('admin.wiki.screenshot-title', {
                          title: selectedArticle.title,
                      })
                    : ''
            "
            :style="{ width: 'min(96vw, 80rem)' }"
        >
            <figure v-if="selectedArticle">
                <img
                    class="max-h-[75vh] w-full rounded-xl border border-zinc-200 object-contain object-top dark:border-zinc-700"
                    :alt="
                        t('admin.wiki.screenshot-alt', {
                            title: selectedArticle.title,
                        })
                    "
                    :src="selectedArticle.image"
                />
                <figcaption
                    class="mt-3 text-sm leading-6 text-zinc-500 dark:text-zinc-400"
                >
                    {{ selectedArticle.caption }}
                </figcaption>
            </figure>
        </Dialog>
    </main>
</template>
