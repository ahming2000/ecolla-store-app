<script setup lang="ts">
import { deleteUser } from '@/libraries/axios/admin/user'
import Notification from '@/libraries/primevue/toast/Notification'
import type { Identifier, User } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    user: User
}>()

const emits = defineEmits<{
    deleteUser: [userId: Identifier]
}>()

const { t } = useI18n()
const toast = Notification.init(useToast())

const isDeleting = ref(false)
const isDialogVisible = ref(false)

const onDelete = async (): Promise<void> => {
    try {
        isDeleting.value = true
        await deleteUser(props.user.id)

        emits('deleteUser', props.user.id)
        isDialogVisible.value = false
        toast.success(
            t('admin.users.deleted-success'),
            t('common.notifications.success')
        )
    } catch (error) {
        console.error(error)
        toast.axiosError(
            error,
            t('admin.users.delete-failed'),
            t('common.notifications.error')
        )
    } finally {
        isDeleting.value = false
    }
}
</script>

<template>
    <Button
        :aria-label="
            t('admin.users.delete-account', {
                username: user.username,
            })
        "
        :data-testid="`delete-user-${user.id}`"
        :label="t('common.actions.delete')"
        icon="pi pi-trash"
        outlined
        severity="danger"
        @click="isDialogVisible = true"
    />

    <Dialog
        v-model:visible="isDialogVisible"
        :closable="!isDeleting"
        :close-on-escape="!isDeleting"
        :header="t('admin.users.delete-title')"
        :style="{ width: '26rem' }"
        modal
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-start gap-3">
                <i
                    aria-hidden="true"
                    class="pi pi-exclamation-triangle mt-1 text-red-500"
                />
                <p class="m-0 text-sm">
                    {{
                        t('admin.users.delete-confirmation', {
                            username: user.username,
                        })
                    }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="isDeleting"
                    :label="t('common.actions.cancel')"
                    severity="secondary"
                    @click="isDialogVisible = false"
                />
                <Button
                    :data-testid="`confirm-delete-user-${user.id}`"
                    :label="t('common.actions.delete')"
                    :loading="isDeleting"
                    severity="danger"
                    @click="onDelete"
                />
            </div>
        </div>
    </Dialog>
</template>
