<script setup lang="ts">
import { deactivateUser, reactivateUser } from '@/libraries/axios/admin/user'
import Notification from '@/libraries/primevue/toast/Notification'
import type { User } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    user: User
}>()

const emits = defineEmits<{
    refreshUser: [user: User]
}>()

const { t } = useI18n()
const toast = Notification.init(useToast())

const isUpdating = ref(false)
const isDialogVisible = ref(false)

const isDeactivation = computed(() => props.user.is_enabled)
const actionLabel = computed(() =>
    t(
        isDeactivation.value
            ? 'admin.users.deactivate-account'
            : 'admin.users.reactivate-account',
        { username: props.user.username }
    )
)
const buttonLabel = computed(() =>
    t(isDeactivation.value ? 'common.actions.disable' : 'common.actions.enable')
)
const dialogTitle = computed(() =>
    t(
        isDeactivation.value
            ? 'admin.users.deactivate-title'
            : 'admin.users.reactivate-title'
    )
)
const confirmationMessage = computed(() =>
    t(
        isDeactivation.value
            ? 'admin.users.deactivate-confirmation'
            : 'admin.users.reactivate-confirmation',
        { username: props.user.username }
    )
)

const onUpdateStatus = async (): Promise<void> => {
    const shouldDeactivate = isDeactivation.value

    try {
        isUpdating.value = true
        const user = shouldDeactivate
            ? await deactivateUser(props.user.id)
            : await reactivateUser(props.user.id)

        emits('refreshUser', user)
        isDialogVisible.value = false
        toast.success(
            t(
                shouldDeactivate
                    ? 'admin.users.deactivated-success'
                    : 'admin.users.reactivated-success'
            ),
            t('common.notifications.success')
        )
    } catch (error) {
        console.error(error)
        toast.axiosError(
            error,
            t(
                shouldDeactivate
                    ? 'admin.users.deactivate-failed'
                    : 'admin.users.reactivate-failed'
            ),
            t('common.notifications.error')
        )
    } finally {
        isUpdating.value = false
    }
}
</script>

<template>
    <Button
        :aria-label="actionLabel"
        :data-testid="`${isDeactivation ? 'deactivate' : 'reactivate'}-user-${user.id}`"
        :label="buttonLabel"
        :icon="isDeactivation ? 'pi pi-ban' : 'pi pi-refresh'"
        :severity="isDeactivation ? 'warn' : 'success'"
        @click="isDialogVisible = true"
    />

    <Dialog
        v-model:visible="isDialogVisible"
        :closable="!isUpdating"
        :close-on-escape="!isUpdating"
        :header="dialogTitle"
        :style="{ width: '26rem' }"
        modal
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-start gap-3">
                <i
                    aria-hidden="true"
                    :class="
                        isDeactivation
                            ? 'pi pi-exclamation-triangle mt-1 text-amber-500'
                            : 'pi pi-refresh mt-1 text-green-500'
                    "
                />
                <p class="m-0 text-sm">
                    {{ confirmationMessage }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    :disabled="isUpdating"
                    :label="t('common.actions.cancel')"
                    severity="secondary"
                    @click="isDialogVisible = false"
                />
                <Button
                    :data-testid="`confirm-${isDeactivation ? 'deactivate' : 'reactivate'}-user-${user.id}`"
                    :label="buttonLabel"
                    :loading="isUpdating"
                    :severity="isDeactivation ? 'warn' : 'success'"
                    @click="onUpdateStatus"
                />
            </div>
        </div>
    </Dialog>
</template>
