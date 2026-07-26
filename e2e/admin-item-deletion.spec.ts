import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const item = {
    id: 999_999,
    name: '删除功能测试商品',
    name_en: 'Item deletion test',
    desc: null,
    is_listed: false,
    view_count: 0,
    sold_count: 0,
    origin_id: null,
    origin: null,
    categories: [],
    variations: [],
    images: [],
    all_images: [],
    cover_image: '/images/no-image-thumbnail.png',
    total_stock: 0,
    total_image_count: 0,
    created_at: '2026-07-26T00:00:00.000000Z',
    updated_at: '2026-07-26T00:00:00.000000Z',
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
}

test('confirms deletion and removes the item from the list', async ({
    page,
}) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)
    let isDeleted = false
    let deleteRequestCount = 0

    await login(page)

    await page.route(/\/ajax\/admin\/item(?:\?.*)?$/, async (route) => {
        if (route.request().method() === 'GET') {
            const items = isDeleted ? [] : [item]

            await route.fulfill({
                json: {
                    current_page: 1,
                    data: items,
                    last_page: 1,
                    per_page: 50,
                    total: items.length,
                },
                status: 200,
            })

            return
        }

        await route.continue()
    })
    await page.route(`**/ajax/admin/item/${item.id}`, async (route) => {
        if (route.request().method() === 'DELETE') {
            deleteRequestCount++
            isDeleted = true
            await route.fulfill({ status: 204 })

            return
        }

        await route.continue()
    })

    await page.goto('/admin/item')

    const deleteButton = page.getByTestId(`delete-item-${item.id}`)

    await expect(deleteButton).toBeEnabled()
    await deleteButton.click()

    const dialog = page.getByRole('dialog', { name: '删除商品' })

    await expect(dialog).toBeVisible()
    await expect(
        dialog.getByText(`确定要删除“${item.name}”吗？此操作无法撤销。`)
    ).toBeVisible()

    await dialog.getByRole('button', { name: '取消', exact: true }).click()
    await expect(dialog).toBeHidden()
    await expect(deleteButton).toBeVisible()

    await deleteButton.click()
    await dialog.getByRole('button', { name: '删除', exact: true }).click()

    await expect(dialog).toBeHidden()
    await expect(deleteButton).toHaveCount(0)
    await expect(page.getByText('商品删除成功！')).toBeVisible()
    expect(deleteRequestCount).toBe(1)

    await page.reload()
    await expect(deleteButton).toHaveCount(0)
    expect(runtimeErrors).toEqual([])
})
