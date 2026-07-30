<script setup lang="ts">
import { useUserDateTimeFormatter } from '@/libraries/date-time'
import { computed } from 'vue'

const props = defineProps<{
    value: string | null | undefined
}>()

const { formatDateTime } = useUserDateTimeFormatter()
const displayDateTime = computed(() => formatDateTime(props.value))
</script>

<template>
    <time :datetime="value ?? undefined">{{ displayDateTime }}</time>
</template>
