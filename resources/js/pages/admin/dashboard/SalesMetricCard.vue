<script setup lang="ts">
import Card from 'primevue/card'
import { computed } from 'vue'

type MetricTone = 'blue' | 'emerald' | 'amber' | 'rose'

const props = defineProps<{
    label: string
    value: string
    description: string
    icon: string
    tone: MetricTone
    testId: string
}>()

const iconClasses = computed(() => {
    return {
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
        emerald:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        amber: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        rose: 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
    }[props.tone]
})
</script>

<template>
    <Card :data-testid="testId" class="h-full">
        <template #content>
            <article class="flex h-full items-start justify-between gap-4">
                <div class="min-w-0">
                    <p
                        class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        {{ label }}
                    </p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight">
                        {{ value }}
                    </p>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ description }}
                    </p>
                </div>

                <span
                    :class="iconClasses"
                    class="inline-flex size-11 shrink-0 items-center justify-center rounded-full"
                    aria-hidden="true"
                >
                    <i :class="icon" class="text-lg"></i>
                </span>
            </article>
        </template>
    </Card>
</template>
