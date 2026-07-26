<script setup lang="ts">
import { SUPERVISOR } from '@/enums/AccessLevel'
import { parseFormError } from '@/libraries/axios/common/parser'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import { useItemStore } from '@/stores/item.store'
import type { AppPageProps, Item } from '@/types'
import { usePage } from '@inertiajs/vue3'
import ToggleSwitch from 'primevue/toggleswitch'
import { useToast } from 'primevue/usetoast'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    item: Item
}>()

const itemStore = useItemStore()
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())
const { locale: activeLanguage, t } = useI18n()

const isUpdating = ref(false)
const listingStatus = ref(Boolean(props.item.is_listed))

const canUpdateListing = computed(() => {
    return (page.props.auth.user?.access_level ?? -1) >= SUPERVISOR
})

const actionLabel = computed(() => {
    const translationKey = listingStatus.value
        ? 'admin.items.unlist-item'
        : 'admin.items.list-item'

    return t(translationKey, {
        name: getLocalizedName(props.item, activeLanguage.value),
    })
})

watch(
    () => props.item.is_listed,
    (isListed) => {
        listingStatus.value = Boolean(isListed)
    }
)

const onUpdateListing = async (isListed: boolean): Promise<void> => {
    const previousListingStatus = props.item.is_listed

    try {
        isUpdating.value = true
        await itemStore.onUpdateListStatus(props.item.id, isListed)
        toast.success(
            t(
                isListed
                    ? 'admin.items.listed-success'
                    : 'admin.items.unlisted-success'
            ),
            t('common.notifications.success')
        )
    } catch (error) {
        const hasListingValidationError = Boolean(
            parseFormError(error).is_listed
        )

        listingStatus.value = previousListingStatus

        if (!hasListingValidationError) {
            console.error(error)
        }

        toast.error(
            t(
                hasListingValidationError
                    ? 'admin.items.listing-requirements'
                    : 'admin.items.listing-update-failed'
            ),
            t('common.notifications.error')
        )
    } finally {
        isUpdating.value = false
    }
}
</script>

<template>
    <span :data-testid="`item-listing-toggle-${item.id}`">
        <ToggleSwitch
            v-model="listingStatus"
            :aria-label="actionLabel"
            :disabled="!canUpdateListing || isUpdating"
            @update:model-value="onUpdateListing"
        />
    </span>
</template>
