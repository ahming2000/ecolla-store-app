<script setup lang="ts">
import logoImage from '@/assets/images/branding/ecolla.png'
import type { MenuItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import Badge from 'primevue/badge'
import Menubar from 'primevue/menubar'
import Toast from 'primevue/toast'
import { useI18n } from 'vue-i18n'

withDefaults(
    defineProps<{
        brandName?: string
        brandHref?: string
        menuItems?: MenuItem[]
    }>(),
    {
        brandName: 'e口乐',
        brandHref: '/',
        menuItems: () => [],
    }
)

const { t } = useI18n()
</script>

<template>
    <Toast />

    <Menubar :model="menuItems">
        <template #start>
            <Link :href="brandHref" class="flex items-center">
                <img
                    :src="logoImage"
                    style="width: 30px; height: 30px"
                    :alt="t('common.alt.logo')"
                    loading="lazy"
                />

                <span class="ml-1">{{ brandName }}</span>
            </Link>
        </template>

        <template #item="{ item, props, hasSubmenu, root }">
            <slot
                v-if="item.slot === 'language-switcher'"
                name="language-switcher"
            ></slot>

            <a v-else class="flex items-center" v-bind="props.action">
                <span :class="item.icon" />

                <span class="ml-2">{{ item.label }}</span>

                <Badge
                    v-if="item.badge !== undefined"
                    :class="{ 'ml-auto': !root, 'ml-2': root }"
                    :value="item.badge"
                />

                <span
                    v-if="item.shortcut"
                    class="ml-auto border border-surface rounded bg-emphasis text-muted-color text-xs p-1"
                >
                    {{ item.shortcut }}
                </span>

                <i
                    v-if="hasSubmenu"
                    :class="[
                        'pi pi-angle-down',
                        {
                            'pi-angle-down ml-2': root,
                            'pi-angle-right ml-auto': !root,
                        },
                    ]"
                ></i>
            </a>
        </template>
    </Menubar>

    <slot name="header"></slot>

    <slot></slot>

    <slot name="footer"></slot>
</template>
