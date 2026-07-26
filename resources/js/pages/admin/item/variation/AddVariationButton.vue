<script setup lang="ts">
import UpsertVariationDialogButton from '@/pages/admin/item/variation/UpsertVariationDialogButton.vue'
import type { Item } from '@/types'

defineProps<{
    item: Item
}>()
</script>

<template>
    <div class="w-full">
        <UpsertVariationDialogButton :item="item" />
    </div>
</template>

<style scoped lang="postcss"></style>
