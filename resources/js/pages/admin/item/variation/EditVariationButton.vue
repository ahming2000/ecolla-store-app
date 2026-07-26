<script setup lang="ts">
import UpsertVariationDialogButton from '@/pages/admin/item/variation/UpsertVariationDialogButton.vue'
import type { Item, Variation } from '@/types'

defineProps<{
    item: Item
    variation: Variation
}>()
</script>

<template>
    <UpsertVariationDialogButton :item="item" :variation="variation" />
</template>
