<script setup lang="ts">
import type { FreeShippingSettings } from '@/types'
import Message from 'primevue/message'

defineProps<{
    freeShipping?: FreeShippingSettings
}>()
</script>

<template>
    <div
        v-if="freeShipping?.isActivated && freeShipping.description"
        class="container mx-auto px-3 pt-3"
    >
        <Message
            closable
            class="w-full"
            data-testid="free-shipping-notice"
            icon="pi pi-gift"
            severity="warn"
        >
            <span class="block w-full text-center">
                {{ freeShipping.description }}
            </span>
        </Message>
    </div>
</template>
