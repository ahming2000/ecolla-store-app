<script setup lang="ts">
import FloatInputText from '@/components/input/FloatInputText.vue'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const toast = Notification.init(useToast())
const store = useItemStore()
const { t } = useI18n()

const visible = ref(false)
const isLoading = ref(false)

const name = ref('')

const onCreate = async () => {
    try {
        isLoading.value = true
        await store.onCreateItem(name.value)
        visible.value = false
        name.value = ''
        toast.success(
            t('admin.items.created-success'),
            t('common.notifications.success')
        )
    } catch (e) {
        toast.axiosError(
            e,
            t('common.notifications.generic-error'),
            t('common.notifications.error')
        )
        console.error(e)
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <Button
        :label="t('common.actions.add')"
        icon="pi pi-plus"
        @click="visible = true"
    />

    <Dialog
        v-model:visible="visible"
        :header="t('admin.items.create-title')"
        modal
    >
        <FloatInputText
            id="name"
            input-class="w-full"
            class="mt-5"
            :label="t('admin.items.fields.name')"
            v-model="name"
        />

        <template #footer>
            <div class="flex justify-end">
                <Button
                    :label="t('admin.items.create-item')"
                    :loading="isLoading"
                    icon="pi pi-plus"
                    @click="onCreate"
                />
            </div>
        </template>
    </Dialog>
</template>
