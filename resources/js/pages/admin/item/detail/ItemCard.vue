<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import { getLocalizedName } from '@/libraries/i18n/language'
import DeleteItemButton from '@/pages/admin/item/detail/DeleteItemButton.vue'
import EditItemButton from '@/pages/admin/item/detail/EditItemButton.vue'
import ItemListingToggle from '@/pages/admin/item/detail/ItemListingToggle.vue'
import type { Item } from '@/types'
import Card from 'primevue/card'
import Image from 'primevue/image'
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
                <Image
                    :src="item.cover_image ?? fallbackImage"
                    :alt="getLocalizedName(item, activeLanguage)"
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

            <div class="text-sm" v-else>
                <div class="mb-1">{{ t('admin.items.variations') }}</div>

                <div
                    class="flex justify-between gap-3"
                    v-for="variation in item.variations"
                >
                    <div class="font-bold truncate">
                        {{ getLocalizedName(variation, activeLanguage) }}
                    </div>

                    <div>
                        <div
                            :class="{
                                'line-through': variation.sale_price,
                                'text-gray-200': variation.sale_price,
                            }"
                        >
                            RM {{ variation.price }}
                        </div>

                        <div v-if="variation.sale_price">
                            RM {{ variation.sale_price }}
                        </div>
                    </div>
                </div>
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
