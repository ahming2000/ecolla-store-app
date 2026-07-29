<script setup lang="ts">
import type { FreeShippingSettings } from '@/types'
import Message from 'primevue/message'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    freeShipping?: FreeShippingSettings
}>()

const { t } = useI18n()

const threshold = computed(() => {
    return `RM ${props.freeShipping?.threshold.toFixed(2)}`
})
</script>

<template>
    <div v-if="freeShipping?.isActivated" class="container mx-auto px-3 pt-3">
        <Message
            class="w-full"
            data-testid="free-shipping-notice"
            icon="pi pi-gift"
            severity="warn"
        >
            <span class="block w-full text-center">
                {{
                    t('shop.free-shipping-notice', {
                        amount: threshold,
                    })
                }}
            </span>
        </Message>
    </div>
</template>
