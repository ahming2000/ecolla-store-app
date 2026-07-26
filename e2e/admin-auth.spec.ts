import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

test('redirects a guest to the admin login page', async ({ page }) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await page.goto('/admin')

    await expect(page).toHaveURL(/\/admin\/login$/)
    await expect(page).toHaveTitle('登录 - e口乐零食店')
    await expect(page.getByLabel('账户 ID', { exact: true })).toBeVisible()
    await expect(page.getByLabel('密码', { exact: true })).toBeVisible()
    await expect(
        page.getByRole('button', { name: '登录', exact: true })
    ).toBeEnabled()
    expect(runtimeErrors).toEqual([])
})
