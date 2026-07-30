<script setup lang="ts">
import { page as itemPage } from '@/routes/shop/item'
import type { Item } from '@/types'
import { Link } from '@inertiajs/vue3'
import Breadcrumb from 'primevue/breadcrumb'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        class?: string
        item: Item
    }>(),
    {
        class: '',
    }
)

const { locale, t } = useI18n()
const breadcrumbScrollElement = ref<HTMLElement | null>(null)
let breadcrumbResizeObserver: ResizeObserver | null = null

const breadcrumbItems = computed(() => {
    const items = []

    if (props.item.origin) {
        const originName =
            locale.value === 'en' && props.item.origin.name_en
                ? props.item.origin.name_en
                : props.item.origin.name

        items.push({
            icon: 'pi pi-globe',
            label: t('shop.item.breadcrumbs.origin', {
                name: originName,
            }),
            route: itemPage.url({
                query: { origin: props.item.origin.id },
            }),
        })
    }

    items.push({
        icon: 'pi pi-shopping-bag',
        label:
            locale.value === 'en' && props.item.name_en
                ? props.item.name_en
                : props.item.name,
    })

    return items
})

const scrollToBack = (): void => {
    if (
        !breadcrumbScrollElement.value ||
        !window.matchMedia('(max-width: 767px)').matches
    ) {
        return
    }

    breadcrumbScrollElement.value.scrollLeft =
        breadcrumbScrollElement.value.scrollWidth
}

watch([breadcrumbItems, locale], async () => {
    await nextTick()
    scrollToBack()
})

onMounted(async () => {
    await nextTick()
    scrollToBack()

    if (!breadcrumbScrollElement.value) {
        return
    }

    breadcrumbResizeObserver = new ResizeObserver(scrollToBack)
    breadcrumbResizeObserver.observe(breadcrumbScrollElement.value)
    window.addEventListener('resize', scrollToBack)
})

onBeforeUnmount(() => {
    breadcrumbResizeObserver?.disconnect()
    window.removeEventListener('resize', scrollToBack)
})
</script>

<template>
    <div
        ref="breadcrumbScrollElement"
        :class="props.class"
        class="overflow-x-auto overscroll-x-contain md:overflow-x-visible"
        data-testid="item-breadcrumb-scroll"
    >
        <Breadcrumb
            class="min-w-max"
            :home="{
                icon: 'pi pi-list',
                label: t('shop.item.breadcrumbs.list'),
                route: itemPage.url(),
            }"
            :model="breadcrumbItems"
        >
            <template #item="{ item }">
                <Link v-if="item.route" :href="item.route">
                    <i :class="item.icon"></i>
                    <span class="ms-1">{{ item.label }}</span>
                </Link>

                <template v-else>
                    <i :class="item.icon"></i>
                    <span class="ms-1">{{ item.label }}</span>
                </template>
            </template>
        </Breadcrumb>
    </div>
</template>
