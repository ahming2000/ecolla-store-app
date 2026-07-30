import type { AppPageProps } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed, type ComputedRef } from 'vue'
import { useI18n } from 'vue-i18n'

const FALLBACK_TIME_ZONE = 'Asia/Kuala_Lumpur'

type DateTimeValue = Date | string | null | undefined

interface UserDateTimeFormatter {
    formatDateTime: (value: DateTimeValue) => string
    timeZone: ComputedRef<string>
}

const isValidTimeZone = (timeZone: string): boolean => {
    try {
        new Intl.DateTimeFormat('en', { timeZone }).format()

        return true
    } catch {
        return false
    }
}

export const resolveTimeZone = (
    timeZone: string | null | undefined
): string => {
    return timeZone && isValidTimeZone(timeZone) ? timeZone : FALLBACK_TIME_ZONE
}

const parseDateTime = (value: DateTimeValue): Date | null => {
    if (value === null || value === undefined || value === '') {
        return null
    }

    const dateTime = value instanceof Date ? value : new Date(value)

    return Number.isNaN(dateTime.getTime()) ? null : dateTime
}

export const formatDateTimeInTimeZone = (
    value: DateTimeValue,
    timeZone: string,
    locale: string
): string => {
    const dateTime = parseDateTime(value)

    if (!dateTime) {
        return ''
    }

    const parts = new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        hour: '2-digit',
        hourCycle: 'h23',
        minute: '2-digit',
        month: '2-digit',
        timeZone: resolveTimeZone(timeZone),
        year: 'numeric',
    })
        .formatToParts(dateTime)
        .reduce<Record<string, string>>((dateParts, part) => {
            if (part.type !== 'literal') {
                dateParts[part.type] = part.value
            }

            return dateParts
        }, {})

    if (
        !parts.year ||
        !parts.month ||
        !parts.day ||
        !parts.hour ||
        !parts.minute
    ) {
        return ''
    }

    return `${parts.year}/${parts.month}/${parts.day} ${parts.hour}:${parts.minute}`
}

export const useUserDateTimeFormatter = (): UserDateTimeFormatter => {
    const page = usePage<AppPageProps>()
    const { locale } = useI18n()
    const timeZone = computed(() =>
        resolveTimeZone(page.props.auth.user?.timezone)
    )

    return {
        formatDateTime: (value: DateTimeValue): string =>
            formatDateTimeInTimeZone(value, timeZone.value, locale.value),
        timeZone,
    }
}
