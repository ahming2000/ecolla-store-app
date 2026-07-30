<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import SmartImage from '@/components/image/SmartImage.vue'
import {
    getAlternateLocalizedName,
    getLocalizedName,
} from '@/libraries/i18n/language'
import AddVariationButton from '@/pages/admin/item/variation/AddVariationButton.vue'
import DeleteVariationButton from '@/pages/admin/item/variation/DeleteVariationButton.vue'
import EditVariationButton from '@/pages/admin/item/variation/EditVariationButton.vue'
import type { Item } from '@/types'
import Badge from 'primevue/badge'
import DataView from 'primevue/dataview'
import { useI18n } from 'vue-i18n'

defineProps<{
    item: Item
}>()

const { locale: activeLanguage, t } = useI18n()
</script>

<template>
    <div
        :data-testid="`variation-list-${item.id}`"
        class="flex w-full flex-col gap-3"
    >
        <DataView :value="item.variations" class="w-full" layout="list">
            <template #list="{ items }">
                <div class="flex w-full flex-wrap gap-3">
                    <div
                        v-for="variation in items"
                        :key="variation.id"
                        class="flex gap-3 border rounded-xl p-5 w-full"
                    >
                        <div class="md:w-[10rem]">
                            <SmartImage
                                :alt="
                                    variation.image?.name ??
                                    t('common.alt.no-image-thumbnail')
                                "
                                :fallback-src="fallbackImage"
                                :image="variation.image"
                                image-class="aspect-square h-full w-full rounded object-contain"
                                preview
                                wrapper-class="block aspect-square overflow-hidden rounded"
                            />
                        </div>

                        <div class="flex flex-col justify-between gap-3">
                            <div class="flex flex-col gap-2">
                                <div>
                                    {{
                                        getLocalizedName(
                                            variation,
                                            activeLanguage
                                        )
                                    }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    {{
                                        getAlternateLocalizedName(
                                            variation,
                                            activeLanguage
                                        )
                                    }}
                                </div>

                                <div class="flex gap-2">
                                    <Badge
                                        :value="variation.price_text"
                                        :severity="
                                            variation.sale_price
                                                ? 'warn'
                                                : 'primary'
                                        "
                                        :line-through="
                                            variation.sale_price !== null
                                        "
                                    />

                                    <Badge
                                        v-show="variation.sale_price !== null"
                                        :value="variation.sale_price_text"
                                    />
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <EditVariationButton
                                    :item="item"
                                    :variation="variation"
                                />
                                <DeleteVariationButton
                                    :item="item"
                                    :variation="variation"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #empty>
                <div class="py-4 text-center text-sm text-surface-500">
                    {{ t('admin.items.no-variations') }}
                </div>
            </template>
        </DataView>

        <AddVariationButton :item="item" />
    </div>
</template>

<style scoped lang="postcss"></style>
