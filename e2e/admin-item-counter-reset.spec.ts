import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface AdminItem {
    id: number
    name: string
    view_count: number
    sold_count: number
}

interface AdminItemPage {
    data: AdminItem[]
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

test('resets item view and sold counts through the Others tab', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)

    const itemsResponse = await page.request.get('/ajax/admin/item', {
        headers: await ajaxHeaders(page),
    })

    expect(itemsResponse.ok()).toBeTruthy()

    const { data: items } = (await itemsResponse.json()) as AdminItemPage
    const item = items[0]

    expect(item).toBeDefined()

    if (!item) {
        throw new Error('No item is available for the counter reset test.')
    }

    const testItems = items.map((currentItem) => {
        if (currentItem.id !== item.id) {
            return currentItem
        }

        return {
            ...currentItem,
            view_count: 42,
            sold_count: 17,
        }
    })

    await page.route(/\/ajax\/admin\/item(?:\?.*)?$/, async (route) => {
        await route.fulfill({
            json: {
                current_page: 1,
                data: testItems,
                last_page: 1,
                per_page: 50,
                total: testItems.length,
            },
        })
    })
    await page.route(
        `**/ajax/admin/item/${item.id}/view-count/reset`,
        async (route) => {
            expect(route.request().method()).toBe('PATCH')
            await route.fulfill({
                json: {
                    id: item.id,
                    view_count: 0,
                    sold_count: 17,
                },
            })
        }
    )
    await page.route(
        `**/ajax/admin/item/${item.id}/sold-count/reset`,
        async (route) => {
            expect(route.request().method()).toBe('PATCH')
            await route.fulfill({
                json: {
                    id: item.id,
                    view_count: 0,
                    sold_count: 0,
                },
            })
        }
    )

    await page.goto('/admin/item')

    const itemCard = page.getByTestId(`item-card-${item.id}`)

    await itemCard.getByRole('button', { name: '编辑', exact: true }).click()
    await page.getByRole('tab', { name: '其他', exact: true }).click()

    const viewResetButton = page.getByTestId(`reset-item-view_count-${item.id}`)
    const viewRow = page.getByRole('row').filter({ has: viewResetButton })

    await expect(viewRow.getByRole('cell').nth(1)).toHaveText('42')
    await viewResetButton.click()

    const viewResetDialog = page.getByRole('dialog', {
        name: '重置浏览次数',
    })

    await expect(viewResetDialog).toBeVisible()
    await expect(
        viewResetDialog.getByText('确定要将浏览次数重置为 0 吗？')
    ).toBeVisible()
    await page.getByTestId('confirm-reset-item-view_count').click()

    await expect(viewResetDialog).toBeHidden()
    await expect(viewRow.getByRole('cell').nth(1)).toHaveText('0')
    await expect(viewResetButton).toBeDisabled()
    await expect(page.getByText('浏览次数重置成功！')).toBeVisible()

    const soldResetButton = page.getByTestId(`reset-item-sold_count-${item.id}`)
    const soldRow = page.getByRole('row').filter({ has: soldResetButton })

    await expect(soldRow.getByRole('cell').nth(1)).toHaveText('17')
    await soldResetButton.click()
    await page.getByTestId('confirm-reset-item-sold_count').click()

    await expect(soldRow.getByRole('cell').nth(1)).toHaveText('0')
    await expect(soldResetButton).toBeDisabled()
    await expect(page.getByText('销售量重置成功！')).toBeVisible()
    expect(runtimeErrors).toEqual([])
})
