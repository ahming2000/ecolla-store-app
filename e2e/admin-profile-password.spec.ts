import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel(/^(Account ID|账户 ID)$/).fill('admin')
    await page.getByLabel(/^(Password|密码)$/).fill('password')
    await page.getByRole('button', { name: /^(Login|登录)$/ }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
}

const selectEnglish = async (page: Page): Promise<void> => {
    if ((await page.locator('html').getAttribute('lang')) === 'en') {
        return
    }

    const languageSwitcher = page.getByRole('combobox', { name: '语言' })

    await languageSwitcher.press('ArrowDown')
    await page.getByRole('option', { name: 'English', exact: true }).click()
    await expect(page.locator('html')).toHaveAttribute('lang', 'en')
}

test('submits the profile password form and clears it after success', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)
    await selectEnglish(page)
    await page.goto('/admin/profile')

    const currentPage = (await page.evaluate(() => {
        const historyState = window.history.state as {
            page?: unknown
        } | null

        return historyState?.page ?? historyState
    })) as {
        component: string
        props: { errors?: Record<string, string> }
        url: string
    }

    expect(currentPage.component).toBe('admin/profile/ProfilePage')
    currentPage.props.errors = {}
    currentPage.url = '/admin/profile'

    await page.route('**/admin/profile/password', async (route) => {
        expect(route.request().method()).toBe('PATCH')
        expect(route.request().postDataJSON()).toEqual({
            old_password: 'password',
            password: 'updated-password',
            password_confirmation: 'updated-password',
        })

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            headers: {
                'X-Inertia': 'true',
                Vary: 'X-Inertia',
            },
            body: JSON.stringify(currentPage),
        })
    })

    const currentPassword = page.getByLabel('Current password', {
        exact: true,
    })
    const newPassword = page.getByLabel('New password', { exact: true })
    const passwordConfirmation = page.getByLabel('Confirm password', {
        exact: true,
    })

    await currentPassword.fill('password')
    await newPassword.fill('updated-password')
    await passwordConfirmation.fill('updated-password')
    await page.getByRole('button', { name: 'Save', exact: true }).click()

    await expect(
        page.getByText('Password updated successfully.', { exact: true })
    ).toBeVisible()
    await expect(currentPassword).toHaveValue('')
    await expect(newPassword).toHaveValue('')
    await expect(passwordConfirmation).toHaveValue('')
    expect(runtimeErrors).toEqual([])
})
