<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

withDefaults(
    defineProps<{
        label: string
        icon?: string
        href?: string
        openInNewTab?: boolean
        color?: string
        backgroundColor?: string
    }>(),
    {
        icon: undefined,
        href: '/',
        openInNewTab: false,
        color: '#FFF',
        backgroundColor: '#000',
    }
)
</script>

<template>
    <a
        v-if="openInNewTab"
        :href="href"
        :target="openInNewTab ? '_blank' : undefined"
    >
        <div
            class="flex justify-center items-center h-[100px] rounded-3xl font-bold"
            :style="{ backgroundColor, color }"
        >
            <i v-show="icon" :class="icon"></i>
            <span class="ml-2">{{ label }}</span>
        </div>
    </a>

    <Link v-else :href="href">
        <div
            class="flex justify-center items-center h-[100px] rounded-3xl font-bold"
            :style="{ backgroundColor, color }"
        >
            <i v-show="icon" :class="icon"></i>
            <span class="ml-2">{{ label }}</span>
        </div>
    </Link>
</template>
