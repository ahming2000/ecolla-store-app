import axios from 'axios'

import type { FormErrors } from '@/types'

interface ValidationErrorResponse {
    errors?: Record<string, string[]>
    message?: string
}

export const parseFormError = (error: unknown): FormErrors => {
    if (!axios.isAxiosError<ValidationErrorResponse>(error)) {
        return {}
    }

    const responseData = error.response?.data
    const formErrors: FormErrors = Object.fromEntries(
        Object.entries(responseData?.errors ?? {}).map(([key, messages]) => [
            key,
            messages[0] ?? '',
        ])
    )

    if (responseData?.message) {
        formErrors.message = responseData.message
    }

    return formErrors
}
