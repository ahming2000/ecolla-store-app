<script setup lang="ts">
import logoImage from '@/assets/images/branding/ecolla.png'
import FloatInputText from '@/components/input/FloatInputText.vue'
import Admin from '@/layouts/Admin.vue'
import { login } from '@/routes'
import { Head, useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

defineProps<{
    canResetPassword?: boolean
    status?: string
}>()

const form = useForm({
    username: '',
    password: '',
    remember: false,
})

const { t } = useI18n()

const submit = () => {
    form.submit(login(), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head :title="t('admin.auth.login.title')" />

    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
    >
        <div>
            <img
                :src="logoImage"
                style="width: 80px; height: 80px"
                :alt="t('common.alt.logo')"
                loading="lazy"
            />
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <FloatInputText
                    id="username"
                    class="mt-5"
                    input-class="w-full"
                    :label="t('admin.auth.login.username')"
                    v-model="form.username"
                    :error="form.errors.username"
                    required
                    autofocus
                    autocomplete="username"
                />

                <FloatInputText
                    id="password"
                    type="password"
                    class="mt-7"
                    input-class="w-full"
                    :label="t('admin.auth.login.password')"
                    v-model="form.password"
                    :error="form.errors.password"
                    required
                    autocomplete="current-password"
                />

                <div class="flex items-center mt-2">
                    <Checkbox
                        input-id="remember"
                        name="remember"
                        v-model="form.remember"
                        :binary="true"
                    />
                    <label for="remember" class="ml-2">
                        {{ t('admin.auth.login.remember') }}
                    </label>
                </div>

                <div class="flex justify-end">
                    <Button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :label="t('admin.auth.login.title')"
                        icon="pi pi-sign-in"
                        :disabled="form.processing"
                    />
                </div>
            </form>
        </div>
    </div>
</template>
