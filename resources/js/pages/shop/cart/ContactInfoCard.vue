<script setup lang="ts">
import Notification from '@/libraries/primevue/toast/Notification'
import Card from 'primevue/card'
import { useToast } from 'primevue/usetoast'
import { useI18n } from 'vue-i18n'

const toast = Notification.init(useToast())
const { t } = useI18n()

const copyPhone = (): void => {
    navigator.clipboard.writeText('+60129862365')
    toast.info(t('shop.cart.contact.phone-copied'))
}
</script>

<template>
    <Card>
        <template #title>{{ t('shop.cart.contact.title') }}</template>

        <template #content>
            <div class="space-y-1">
                <a
                    class="block space-x-1"
                    href="https://www.facebook.com/Ecolla-e%E5%8F%A3%E4%B9%90-2347940035424278"
                    target="_blank"
                >
                    <i class="pi pi-facebook"></i>
                    <span class="ml-1">
                        {{ t('shop.cart.contact.facebook') }}
                    </span>
                </a>

                <a
                    class="block space-x-1"
                    href="https://wa.link/2e1z4h"
                    target="_blank"
                >
                    <i class="pi pi-whatsapp"></i>
                    <span>{{ t('shop.cart.contact.whatsapp') }}</span>
                </a>

                <div class="cursor-pointer space-x-1" @click="copyPhone">
                    <i class="pi pi-clone"></i>
                    <span>012-9862365</span>
                </div>
            </div>
        </template>
    </Card>
</template>
