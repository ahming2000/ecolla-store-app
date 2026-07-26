<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import Error from '@/pages/error/common/Error.vue'
import { page as landingPage } from '@/routes/shop/landing'

defineOptions({ layout: Shop })

defineProps<{
    status?: string | number
}>()
</script>

<template>
    <Error
        :status="status"
        :home-page-redirect-link="landingPage.url()"
    />
</template>
