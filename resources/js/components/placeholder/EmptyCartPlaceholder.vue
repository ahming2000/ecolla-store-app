<script setup lang="ts">
import emptyCartImage from '@/assets/images/placeholders/cart-empty.png'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
    <div class="flex justify-center align-center py-10">
        <div class="flex flex-col">
            <div class="mb-5" :style="{ width: '150px', height: '150px' }">
                <img :src="emptyCartImage" :alt="t('shop.cart.empty.alt')" />
            </div>

            <div class="flex justify-center text-xl">
                {{ t('shop.cart.empty.description') }}
            </div>
        </div>
    </div>
</template>
