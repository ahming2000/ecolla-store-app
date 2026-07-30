<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import SmartImage from '@/components/image/SmartImage.vue'
import { getLocalizedName } from '@/libraries/i18n/language'
import DeleteItemButton from '@/pages/admin/item/detail/DeleteItemButton.vue'
import EditItemButton from '@/pages/admin/item/detail/EditItemButton.vue'
import ItemListingToggle from '@/pages/admin/item/detail/ItemListingToggle.vue'
import type { Item } from '@/types'
import Card from 'primevue/card'
import { useI18n } from 'vue-i18n'

defineProps<{
    item: Item
}>()

const { locale: activeLanguage, t } = useI18n()
</script>

<template>
    <Card :data-testid="`item-card-${item.id}`">
        <template #header>
            <div class="flex justify-center items-center">
                <SmartImage
                    :alt="getLocalizedName(item, activeLanguage)"
                    :fallback-src="fallbackImage"
                    image-class="aspect-square h-full w-full object-contain"
                    :src="item.cover_image ?? fallbackImage"
                    :thumbnail-src="item.cover_thumbnail"
                />
            </div>
        </template>

        <template #title>
            <div class="flex justify-between items-center">
                <div>
                    {{ getLocalizedName(item, activeLanguage) }}
                </div>

                <ItemListingToggle :item="item" />
            </div>
        </template>

        <template #content>
            <div
                class="flex justify-center text-sm"
                v-if="item.variations.length === 0"
            >
                {{ t('admin.items.no-variations') }}
            </div>
        </template>

        <template #footer>
            <div class="grid grid-cols-2 gap-1">
                <EditItemButton :item="item" />
                <DeleteItemButton :item="item" />
            </div>
        </template>
    </Card>
</template>
