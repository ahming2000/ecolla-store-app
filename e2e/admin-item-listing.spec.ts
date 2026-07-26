import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface AdminItem {
    id: number
    name: string
    name_en: string | null
    desc: string | null
    is_listed: boolean
    origin: {
        name: string
        name_en: string
    } | null
    variations: {
        barcode: string
        name: string
        name_en: string | null
    }[]
}

interface AdminItemPage {
    data: AdminItem[]
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
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

const isListable = (item: AdminItem): boolean => {
    return Boolean(
        item.name.trim() &&
        item.name_en?.trim() &&
        item.desc?.trim() &&
        item.origin?.name.trim() &&
        item.origin.name_en.trim() &&
        item.variations.length > 0 &&
        item.variations.every(
            (variation) =>
                variation.barcode.trim() &&
                variation.name.trim() &&
                variation.name_en?.trim()
        )
    )
}

const listingSwitch = (page: Page, itemId: number) => {
    return page.getByTestId(`item-listing-toggle-${itemId}`).getByRole('switch')
}

test('lists and unlists a complete item through AJAX', async ({
    page,
}, testInfo) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)

    const headers = await ajaxHeaders(page)
    const itemsResponse = await page.request.get('/ajax/admin/item', {
        headers,
    })

    expect(itemsResponse.ok()).toBeTruthy()

    const { data: items } = (await itemsResponse.json()) as AdminItemPage
    const listableItems = items.filter(isListable)
    const projectItemIndex =
        {
            chromium: 0,
            firefox: 1,
            webkit: 2,
        }[testInfo.project.name] ?? 0
    const item = listableItems[projectItemIndex]

    expect(item).toBeDefined()

    if (!item) {
        throw new Error('No complete item is available for the listing test.')
    }

    const originalListingStatus = item.is_listed
    const updatedListingStatus = !originalListingStatus

    try {
        await page.goto('/admin/item')

        const toggle = listingSwitch(page, item.id)

        await expect(toggle).toBeChecked({
            checked: originalListingStatus,
        })
        await toggle.click()
        await expect(toggle).toBeChecked({
            checked: updatedListingStatus,
        })
        await expect(
            page.getByText(
                updatedListingStatus ? '商品上架成功！' : '商品下架成功！'
            )
        ).toBeVisible()

        await page.reload()
        await expect(listingSwitch(page, item.id)).toBeChecked({
            checked: updatedListingStatus,
        })

        await listingSwitch(page, item.id).click()
        await expect(listingSwitch(page, item.id)).toBeChecked({
            checked: originalListingStatus,
        })
        expect(runtimeErrors).toEqual([])
    } finally {
        const restoreResponse = await page.request.patch(
            `/ajax/admin/item/${item.id}/listing`,
            {
                data: { is_listed: originalListingStatus },
                headers,
            }
        )

        expect(restoreResponse.ok()).toBeTruthy()
    }
})

test('keeps an incomplete item unlisted and explains the requirements', async ({
    page,
}, testInfo) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)

    const headers = await ajaxHeaders(page)
    const itemName = `Incomplete listing ${testInfo.project.name} ${Date.now()}`
    const createResponse = await page.request.post('/ajax/admin/item', {
        data: { name: itemName },
        headers,
    })

    expect(createResponse.ok()).toBeTruthy()

    const item = (await createResponse.json()) as AdminItem

    try {
        await page.goto('/admin/item')

        const toggle = listingSwitch(page, item.id)

        await expect(toggle).not.toBeChecked()
        await toggle.click()
        await expect(toggle).not.toBeChecked()
        await expect(
            page.getByText(
                '上架前，请填写商品的中英文名称、描述、出产地，并至少添加一个已填写货号及中英文名称的规格。'
            )
        ).toBeVisible()

        await page.reload()
        await expect(listingSwitch(page, item.id)).not.toBeChecked()
        expect(runtimeErrors).toEqual([])
    } finally {
        const deleteResponse = await page.request.delete(
            `/ajax/admin/item/${item.id}`,
            { headers }
        )

        expect(deleteResponse.ok()).toBeTruthy()
    }
})
