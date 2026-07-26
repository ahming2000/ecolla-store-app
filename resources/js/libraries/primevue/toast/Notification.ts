import axios from 'axios'
import type { ToastMessageOptions } from 'primevue/toast'
import type { ToastServiceMethods } from 'primevue/toastservice'

/**
 * Utility class for Primevue toast service, add default text for the message,
 * and make the function easier to understand.
 */
export default class Notification {
    constructor(private readonly toast: ToastServiceMethods) {}

    static init(toast: ToastServiceMethods): Notification {
        return new Notification(toast)
    }

    add(message: ToastMessageOptions): void {
        this.toast.add(message)
    }

    success(detail: string, title = 'Successful', life = 3000): void {
        this.toast.add({
            severity: 'success',
            summary: title,
            detail: detail,
            life: life,
        })
    }

    info(detail: string, title = 'Info', life = 3000): void {
        this.toast.add({
            severity: 'info',
            summary: title,
            detail: detail,
            life: life,
        })
    }

    warn(detail: string, title = 'Warning', life = 3000): void {
        this.toast.add({
            severity: 'warn',
            summary: title,
            detail: detail,
            life: life,
        })
    }

    error(detail: string, title = 'Error', life = 3000): void {
        this.toast.add({
            severity: 'error',
            summary: title,
            detail: detail,
            life: life,
        })
    }

    axiosError(
        error: unknown,
        defaultMessage = 'Something went wrong.',
        title = 'Error',
        severity: ToastMessageOptions['severity'] = 'error',
        life = 3000
    ): void {
        const responseMessage = axios.isAxiosError<{ message?: string }>(error)
            ? error.response?.data?.message
            : undefined

        this.toast.add({
            severity: severity,
            summary: title,
            detail: responseMessage ?? defaultMessage,
            life: life,
        })
    }
}
