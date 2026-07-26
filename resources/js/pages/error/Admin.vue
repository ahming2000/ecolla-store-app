<script setup lang="ts">
import Admin from '@/layouts/Admin.vue'
import Error from '@/pages/error/common/Error.vue'
import { page as dashboardPage } from '@/routes/admin/dashboard'

defineOptions({ layout: Admin })

defineProps<{
    status?: string | number
}>()
</script>

<template>
    <Error
        :status="status"
        :home-page-redirect-link="dashboardPage.url()"
    />
</template>
