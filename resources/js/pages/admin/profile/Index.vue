<script setup lang="ts">
import FloatInputText from '@/components/input/FloatInputText.vue'
import Admin from '@/layouts/Admin.vue'
import type { AppPageProps } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

const form = useForm({
    old_password: '',
    password: '',
    password_confirmation: '',
})

const authUser = computed(() => {
    return usePage<AppPageProps>().props.auth.user
})

const { t } = useI18n()

const onUpdatePassword = () => {
    // TODO submit for changing the password
    console.log(form)
}
</script>

<template>
    <Head :title="t('admin.profile.title')" />

    <div class="container mx-auto my-3">
        <div class="md:mx-24 lg:mx-48 xl:mx-80 space-y-3">
            <div class="text-3xl font-bold">
                {{ t('admin.profile.title') }}
            </div>

            <Card>
                <template #title>{{ t('admin.profile.account-id') }}</template>

                <template #content>
                    <div class="space-x-1">
                        <span>
                            {{ t('admin.profile.account-id-description') }}
                        </span>
                        <span class="font-bold">
                            {{ authUser?.username ?? '—' }}
                        </span>
                    </div>

                    <div class="text-gray-500">
                        {{ t('admin.profile.account-id-help') }}
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <form @submit.prevent="onUpdatePassword">
                        <div class="flex justify-between items-center mb-7">
                            <div class="text-3xl font-bold">
                                {{ t('admin.profile.change-password') }}
                            </div>

                            <Button
                                icon="pi pi-save"
                                :label="t('common.actions.save')"
                                type="submit"
                            />
                        </div>

                        <div class="space-y-6">
                            <FloatInputText
                                v-model="form.old_password"
                                input-class="w-full"
                                id="old_password"
                                :label="t('admin.profile.old-password')"
                                type="password"
                            />

                            <FloatInputText
                                v-model="form.password"
                                input-class="w-full"
                                id="password"
                                :label="t('admin.profile.new-password')"
                                type="password"
                            />

                            <FloatInputText
                                v-model="form.password_confirmation"
                                input-class="w-full"
                                id="old_password"
                                :label="t('admin.profile.confirm-password')"
                                type="password"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>
