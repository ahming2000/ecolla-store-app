<script setup lang="ts">
import { getLocalizedName } from '@/libraries/i18n/language'
import EditItemBasicInfoForm from '@/pages/admin/item/detail/EditItemBasicInfoForm.vue'
import ItemListingToggle from '@/pages/admin/item/detail/ItemListingToggle.vue'
import EditItemImageManage from '@/pages/admin/item/image/EditItemImageManage.vue'
import MiscellaneousTab from '@/pages/admin/item/miscellaneous/MiscellaneousTab.vue'
import ManageVariationTab from '@/pages/admin/item/variation/ManageVariationTab.vue'
import type { Item } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanel from 'primevue/tabpanel'
import TabPanels from 'primevue/tabpanels'
import Tabs from 'primevue/tabs'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    item: Item
}>()

const visible = ref(false)
const { locale: activeLanguage, t } = useI18n()

const onEdit = () => {
    visible.value = true
}

const editDialogTitle = computed(() => {
    if (!!props.item.name) {
        return t('admin.items.edit-named-item', {
            name: getLocalizedName(props.item, activeLanguage.value),
        })
    }

    return t('admin.items.edit-item')
})
</script>

<template>
    <Button
        :label="t('common.actions.edit')"
        icon="pi pi-pen-to-square"
        @click="onEdit"
    />

    <Dialog
        v-model:visible="visible"
        :draggable="false"
        :pt="{
            content: {
                class: 'flex min-h-0 flex-1 flex-col overflow-hidden',
            },
        }"
        :data-testid="`edit-item-dialog-${item.id}`"
        class="h-[calc(100dvh-1rem)] w-[calc(100vw-1rem)] !max-h-[calc(100dvh-1rem)] sm:h-[min(88dvh,48rem)] sm:w-[calc(100vw-3rem)] lg:w-[min(88vw,64rem)]"
        modal
    >
        <template #header>
            <div class="flex min-w-0 items-center gap-2">
                <div class="truncate font-bold">
                    {{ editDialogTitle }}
                </div>

                <ItemListingToggle :item="item" />
            </div>
        </template>

        <Tabs
            :data-testid="`item-edit-tabs-${item.id}`"
            class="flex min-h-0 w-full flex-1 flex-col self-stretch"
            scrollable
            value="0"
        >
            <TabList
                :pt="{
                    content: {
                        class: 'flex w-full',
                    },
                    root: {
                        class: 'w-full',
                    },
                }"
                class="w-full shrink-0"
            >
                <Tab class="flex flex-1 justify-center" value="0">
                    {{ t('admin.items.tabs.basic') }}
                </Tab>
                <Tab class="flex flex-1 justify-center" value="1">
                    {{ t('admin.items.tabs.images') }}
                </Tab>
                <Tab class="flex flex-1 justify-center" value="2">
                    {{ t('admin.items.tabs.variations') }}
                </Tab>
                <Tab class="flex flex-1 justify-center" value="3">
                    {{ t('admin.items.tabs.miscellaneous') }}
                </Tab>
            </TabList>

            <TabPanels
                :data-testid="`item-edit-tab-panels-${item.id}`"
                class="min-h-0 w-full flex-1 overflow-y-auto"
            >
                <TabPanel class="w-full" value="0">
                    <EditItemBasicInfoForm :item="item" />
                </TabPanel>

                <TabPanel class="w-full" value="1">
                    <EditItemImageManage :item="item" />
                </TabPanel>

                <TabPanel class="w-full" value="2">
                    <ManageVariationTab :item="item" />
                </TabPanel>

                <TabPanel class="w-full" value="3">
                    <MiscellaneousTab :item="item" />
                </TabPanel>
            </TabPanels>
        </Tabs>
    </Dialog>
</template>
