<script setup lang="ts">
import EmptyDataPlaceholder from '@/components/placeholder/EmptyDataPlaceholder.vue'
import Admin from '@/layouts/Admin.vue'
import DeleteUserButton from '@/pages/admin/user/DeleteUserButton.vue'
import UpsertUserDialogButton from '@/pages/admin/user/UpsertUserDialogButton.vue'
import UserStatusButton from '@/pages/admin/user/UserStatusButton.vue'
import type { Identifier, User } from '@/types'
import { Head } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

const props = withDefaults(
    defineProps<{
        users?: User[]
    }>(),
    {
        users: () => [],
    }
)

const users = ref(props.users ?? [])
const { t } = useI18n()

const refreshUser = (user: User): void => {
    if (!!users.value.find((u) => u.id === user.id)) {
        users.value = users.value.map((u) => {
            if (u.id === user.id) {
                return user
            }

            return u
        })
    } else {
        users.value.push(user)
    }
}

const removeUser = (userId: Identifier): void => {
    users.value = users.value.filter((user) => user.id !== userId)
}
</script>

<template>
    <Head :title="t('admin.users.title')" />

    <div class="container mx-auto my-3">
        <div class="mb-3 flex justify-between">
            <div class="text-3xl font-bold">
                {{ t('admin.users.title') }}
            </div>
            <UpsertUserDialogButton @refresh-user="refreshUser" />
        </div>

        <EmptyDataPlaceholder v-if="users.length === 0" />

        <div v-else class="space-y-3">
            <Card v-for="user in users" :key="user.id">
                <template #content>
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <div class="font-medium">{{ user.username }}</div>
                            <Tag
                                :severity="
                                    user.is_enabled ? 'success' : 'secondary'
                                "
                                :value="
                                    user.is_enabled
                                        ? t('admin.users.active')
                                        : t('admin.users.deactivated')
                                "
                            />
                        </div>

                        <div class="flex flex-wrap gap-1">
                            <UpsertUserDialogButton
                                :user="user"
                                @refresh-user="refreshUser"
                            />
                            <UserStatusButton
                                :user="user"
                                @refresh-user="refreshUser"
                            />
                            <DeleteUserButton
                                :user="user"
                                @delete-user="removeUser"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
