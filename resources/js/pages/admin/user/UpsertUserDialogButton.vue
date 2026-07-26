<script setup lang="ts">
import LabeledInputText from '@/components/input/LabeledInputText.vue'
import {
    EDITOR,
    getAccessLevelLabel,
    getAccessLevelOptions,
    SUPERVISOR,
    VIEWER,
} from '@/enums/AccessLevel'
import { createUser, updateUser } from '@/libraries/axios/admin/user'
import { parseFormError } from '@/libraries/axios/common/parser'
import Notification from '@/libraries/primevue/toast/Notification'
import type { User } from '@/types'
import { useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import RadioButton from 'primevue/radiobutton'
import { useToast } from 'primevue/usetoast'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = withDefaults(
    defineProps<{
        user?: User | null
    }>(),
    {
        user: null,
    }
)

const emits = defineEmits<{
    refreshUser: [user: User]
}>()

const { t } = useI18n()
const toast = Notification.init(useToast())

const visible = ref(false)
const isLoading = ref(false)

const form = useForm({
    username: '',
    password: '',
    password_confirmation: '',
    access_level: props.user ? props.user.access_level : VIEWER,
})

const buttonIcon = computed(() => {
    return props.user ? 'pi pi-pencil' : 'pi pi-plus'
})

const buttonLabel = computed(() => {
    return props.user ? t('admin.users.edit') : t('admin.users.add-account')
})

const header = computed(() => {
    return props.user
        ? t('admin.users.edit-account', {
              username: props.user.username,
          })
        : t('admin.users.add-account')
})

const saveButtonIcon = computed(() => {
    return props.user ? 'pi pi-save' : 'pi pi-plus'
})

const saveButtonLabel = computed(() => {
    return props.user
        ? t('common.actions.save')
        : t('admin.users.create-account')
})

const onClick = () => {
    visible.value = true
}

const onSubmit = async () => {
    try {
        let user
        isLoading.value = true

        if (props.user) {
            user = await updateUser(form.data(), props.user.id)
            toast.success(
                t('admin.users.updated-success'),
                t('common.notifications.success')
            )
        } else {
            user = await createUser(form.data())
            toast.success(
                t('admin.users.created-success'),
                t('common.notifications.success')
            )
        }

        emits('refreshUser', user)
        visible.value = false
    } catch (e) {
        form.errors = parseFormError(e)
        console.error(e)
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <Button :icon="buttonIcon" :label="buttonLabel" @click="onClick" />

    <Dialog :header="header" class="w-[400px]" v-model:visible="visible" modal>
        <form class="space-y-2" @submit.prevent="onSubmit">
            <LabeledInputText
                v-if="!user"
                v-model="form.username"
                :label="t('admin.users.username')"
                id="username"
                name="username"
                input-class="w-full"
                :error="form.errors.username"
            />

            <LabeledInputText
                v-model="form.password"
                type="password"
                :label="t('admin.users.password')"
                id="password"
                name="password"
                input-class="w-full"
                :error="form.errors.password"
            />

            <LabeledInputText
                v-model="form.password_confirmation"
                type="password"
                :label="t('admin.users.confirm-password')"
                id="password_confirmation"
                name="password_confirmation"
                input-class="w-full"
                :error="form.errors.password_confirmation"
            />

            <div class="mt-4">
                <div class="text-lg font-bold">
                    {{ t('admin.users.access-level') }}
                </div>

                <div class="flex space-x-2">
                    <div
                        v-for="accessLevel in getAccessLevelOptions()"
                        class="flex items-center"
                    >
                        <RadioButton
                            v-model="form.access_level"
                            :input-id="getAccessLevelLabel(t, accessLevel)"
                            :name="getAccessLevelLabel(t, accessLevel)"
                            :value="accessLevel"
                        />

                        <label
                            :for="getAccessLevelLabel(t, accessLevel)"
                            class="ml-2"
                        >
                            {{ getAccessLevelLabel(t, accessLevel) }}
                        </label>
                    </div>
                </div>

                <div class="text-lg mt-2">
                    {{ t('admin.users.access-level-details') }}
                </div>

                <div class="text-sm h-[40px]">
                    <p v-if="form.access_level === VIEWER">
                        {{ t('admin.users.viewer-details') }}
                    </p>

                    <p v-if="form.access_level === EDITOR">
                        {{ t('admin.users.editor-details') }}
                    </p>

                    <p v-if="form.access_level === SUPERVISOR">
                        {{ t('admin.users.supervisor-details') }}
                    </p>
                </div>
            </div>

            <div class="flex justify-end">
                <Button
                    :icon="saveButtonIcon"
                    :label="saveButtonLabel"
                    :loading="isLoading"
                    type="submit"
                />
            </div>
        </form>
    </Dialog>
</template>
