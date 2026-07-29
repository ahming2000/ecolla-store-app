<script setup lang="ts">
import { getLocalizedName } from '@/libraries/i18n/language'
import type { OrderedItem } from '@/types'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import { useI18n } from 'vue-i18n'

defineProps<{
    items: OrderedItem[]
}>()

const { locale: activeLanguage, t } = useI18n()
</script>

<template>
    <div class="col-span-2">
        <div class="text-2xl">
            {{ t('admin.orders.item-details', { count: items.length }) }}
        </div>

        <DataTable :value="items" data-key="id">
            <Column
                :header="t('admin.orders.columns.barcode')"
                field="barcode"
            />
            <Column :header="t('admin.orders.columns.name')">
                <template #body="{ data }">
                    {{ getLocalizedName(data, activeLanguage) }}
                </template>
            </Column>
            <Column
                :header="t('admin.orders.columns.quantity')"
                field="quantity"
            />

            <Column :header="t('admin.orders.columns.price')">
                <template #body="{ data }">
                    <div
                        :class="{
                            'line-through': data.sale_price,
                            'text-gray-200': data.sale_price,
                        }"
                    >
                        {{ data.price }}
                    </div>

                    <div v-if="data.sale_price">
                        {{ data.sale_price }}
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
