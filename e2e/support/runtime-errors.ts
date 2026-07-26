import type { Page } from '@playwright/test'

export const collectRuntimeErrors = (page: Page): string[] => {
    const errors: string[] = []

    page.on('pageerror', (error) => {
        errors.push(error.message)
    })

    page.on('console', (message) => {
        if (
            (message.type() === 'error' &&
                !message.text().startsWith('Failed to load resource:')) ||
            (message.type() === 'warning' &&
                message.text().startsWith('[PrimeUI]'))
        ) {
            errors.push(message.text())
        }
    })

    page.on('response', (response) => {
        if (response.status() >= 500) {
            errors.push(`${response.status()} ${response.url()}`)
        }
    })

    return errors
}
