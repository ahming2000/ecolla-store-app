import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface AdminItem {
    id: number
    name: string
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/)
}

const ajaxHeaders = async (page: Page): Promise<Record<string, string>> => {
    const csrfCookie = (await page.context().cookies()).find(
        (cookie) => cookie.name === 'XSRF-TOKEN'
    )

    expect(csrfCookie).toBeDefined()

    return {
        Accept: 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(csrfCookie?.value ?? ''),
        'X-Requested-With': 'XMLHttpRequest',
    }
}

test('updates item details from the edit dialog', async ({
    page,
}, testInfo) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)

    const headers = await ajaxHeaders(page)
    const originalName = `Detail test ${testInfo.project.name} ${Date.now()}`
    const updatedName = `${originalName} updated`
    const createResponse = await page.request.post('/ajax/admin/item', {
        data: { name: originalName },
        headers,
    })

    expect(createResponse.ok()).toBeTruthy()

    const item = (await createResponse.json()) as AdminItem

    try {
        await page.goto('/admin/item')

        const itemCard = page
            .getByText(originalName, { exact: true })
            .locator('xpath=ancestor::*[contains(@class, "p-card")]')
            .first()

        await itemCard
            .getByRole('button', { name: '编辑', exact: true })
            .click()

        const itemDialog = page.getByRole('dialog')
        const saveButton = itemDialog.getByTestId(
            `update-item-detail-${item.id}`
        )

        await expect(itemDialog).toBeVisible()
        await expect(saveButton).toBeVisible()

        await itemDialog
            .getByLabel('商品名称', { exact: true })
            .fill(updatedName)

        const updateResponsePromise = page.waitForResponse((response) => {
            return (
                response.request().method() === 'PUT' &&
                new URL(response.url()).pathname ===
                    `/ajax/admin/item/${item.id}`
            )
        })

        await saveButton.click()

        const updateResponse = await updateResponsePromise

        expect(updateResponse.ok()).toBeTruthy()
        await expect(page.getByText('商品资料更新成功！')).toBeVisible()
        await expect(page.getByText(updatedName, { exact: true })).toBeVisible()

        await testInfo.attach('updated-item-detail-dialog', {
            body: await itemDialog.screenshot(),
            contentType: 'image/png',
        })

        expect(runtimeErrors).toEqual([])
    } finally {
        const deleteResponse = await page.request.delete(
            `/ajax/admin/item/${item.id}`,
            { headers }
        )

        expect(deleteResponse.ok()).toBeTruthy()
    }
})
