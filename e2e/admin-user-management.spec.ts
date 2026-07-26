import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录' }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
})

test('deactivates, reactivates, and deletes a staff account', async ({
    page,
}, testInfo) => {
    await page.goto('/admin/user')

    const availableDeactivateButtons = page.locator(
        '[data-testid^="deactivate-user-"]:not([disabled])'
    )
    const availableUserCount = await availableDeactivateButtons.count()

    expect(availableUserCount).toBeGreaterThan(0)

    const availableDeactivateButton = availableDeactivateButtons.first()
    const deactivateTestId =
        await availableDeactivateButton.getAttribute('data-testid')
    const userId = Number(deactivateTestId?.replace('deactivate-user-', ''))
    expect(userId).toBeGreaterThan(0)
    const deactivateButton = page.getByTestId(`deactivate-user-${userId}`)

    await page.route(
        `**/ajax/admin/user/${userId}/deactivate`,
        async (route) => {
            await route.fulfill({
                contentType: 'application/json',
                json: {
                    access_level: 0,
                    created_at: '2026-07-26T00:00:00+08:00',
                    deleted_at: null,
                    id: userId,
                    is_enabled: false,
                    lang: 'zh',
                    timezone: 'Asia/Kuala_Lumpur',
                    updated_at: '2026-07-26T00:00:00+08:00',
                    username: 'deactivated-staff',
                },
                status: 200,
            })
        }
    )
    await page.route(
        `**/ajax/admin/user/${userId}/reactivate`,
        async (route) => {
            await route.fulfill({
                contentType: 'application/json',
                json: {
                    access_level: 0,
                    created_at: '2026-07-26T00:00:00+08:00',
                    deleted_at: null,
                    id: userId,
                    is_enabled: true,
                    lang: 'zh',
                    timezone: 'Asia/Kuala_Lumpur',
                    updated_at: '2026-07-26T00:00:00+08:00',
                    username: 'reactivated-staff',
                },
                status: 200,
            })
        }
    )
    await page.route(`**/ajax/admin/user/${userId}`, async (route) => {
        await route.fulfill({ status: 204 })
    })

    await expect(deactivateButton).toBeEnabled()
    await deactivateButton.click()

    const deactivateResponsePromise = page.waitForResponse(
        (response) =>
            response.request().method() === 'PATCH' &&
            new URL(response.url()).pathname ===
                `/ajax/admin/user/${userId}/deactivate`
    )

    await page.getByTestId(`confirm-deactivate-user-${userId}`).click()
    expect((await deactivateResponsePromise).ok()).toBeTruthy()
    await expect(deactivateButton).toHaveCount(0)
    const reactivateButton = page.getByTestId(`reactivate-user-${userId}`)
    await expect(reactivateButton).toBeVisible()
    await expect(
        page.getByTestId(`confirm-deactivate-user-${userId}`)
    ).toHaveCount(0)
    await page.screenshot({
        fullPage: true,
        path: testInfo.outputPath('deactivated-user.png'),
    })

    await expect(reactivateButton).toBeEnabled()
    await reactivateButton.click()

    const reactivateResponsePromise = page.waitForResponse(
        (response) =>
            response.request().method() === 'PATCH' &&
            new URL(response.url()).pathname ===
                `/ajax/admin/user/${userId}/reactivate`
    )

    await page.getByTestId(`confirm-reactivate-user-${userId}`).click()
    expect((await reactivateResponsePromise).ok()).toBeTruthy()
    await expect(page.getByTestId(`deactivate-user-${userId}`)).toBeEnabled()
    await expect(
        page.getByTestId(`confirm-reactivate-user-${userId}`)
    ).toHaveCount(0)
    await page.screenshot({
        fullPage: true,
        path: testInfo.outputPath('reactivated-user.png'),
    })

    await page.getByTestId(`delete-user-${userId}`).click()
    await expect(
        page.getByTestId(`confirm-delete-user-${userId}`)
    ).toBeVisible()
    await page.screenshot({
        fullPage: true,
        path: testInfo.outputPath('delete-confirmation.png'),
    })

    const deleteResponsePromise = page.waitForResponse(
        (response) =>
            response.request().method() === 'DELETE' &&
            new URL(response.url()).pathname === `/ajax/admin/user/${userId}`
    )

    await page.getByTestId(`confirm-delete-user-${userId}`).click()
    expect((await deleteResponsePromise).ok()).toBeTruthy()
    await expect(page.getByTestId(`delete-user-${userId}`)).toHaveCount(0)
})
