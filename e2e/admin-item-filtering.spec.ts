import { expect, type Page, type Request, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const timestamp = '2026-07-26T00:00:00.000000Z'
const category = {
    id: 812_345,
    name: '后端筛选分类',
    name_en: 'Backend filter category',
    items_count: 1,
    created_at: timestamp,
    updated_at: timestamp,
}

const item = (id: number, name: string) => ({
    id,
    name,
    name_en: name,
    desc: null,
    is_listed: false,
    view_count: 0,
    sold_count: 0,
    origin_id: null,
    origin: null,
    categories: [category],
    variations: [],
    images: [],
    all_images: [],
    cover_image: '/images/ecolla.png',
    total_stock: 0,
    total_image_count: 0,
    created_at: timestamp,
    updated_at: timestamp,
})

const isItemIndexRequest = (request: Request): boolean => {
    return (
        request.method() === 'GET' &&
        new URL(request.url()).pathname === '/ajax/admin/item'
    )
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
}

test('sends every item listing filter to the backend', async ({ page }) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)
    const requestedUrls: URL[] = []

    await login(page)

    await page.route('**/ajax/admin/category', async (route) => {
        await route.fulfill({ json: [category], status: 200 })
    })
    await page.route('**/ajax/admin/origin', async (route) => {
        await route.fulfill({ json: [], status: 200 })
    })
    await page.route(/\/ajax\/admin\/item(?:\?.*)?$/, async (route) => {
        const url = new URL(route.request().url())

        requestedUrls.push(url)

        let resultName = 'Initial backend result'

        if (url.searchParams.get('page') === '2') {
            resultName = 'Paginated backend result'
        } else if (
            url.searchParams.get('sort_by') === 'sold_count' &&
            url.searchParams.get('sort_direction') === 'asc'
        ) {
            resultName = 'Sorted backend result'
        } else if (
            url.searchParams.get('out_of_stock') === '1' &&
            url.searchParams.get('not_listed') === '1'
        ) {
            resultName = 'Status backend result'
        } else if (
            url.searchParams.getAll('category_ids[]').includes(`${category.id}`)
        ) {
            resultName = 'Category backend result'
        } else if (url.searchParams.get('keyword') === 'needle') {
            resultName = 'Keyword backend result'
        }

        await route.fulfill({
            json: {
                current_page: Number(url.searchParams.get('page') ?? 1),
                data: [item(requestedUrls.length, resultName)],
                last_page: 2,
                per_page: 50,
                total: 51,
            },
            status: 200,
        })
    })

    await page.goto('/admin/item')

    await expect(page.getByText('Initial backend result')).toBeVisible()
    expect(requestedUrls[0]?.searchParams.get('sort_by')).toBe('created_at')
    expect(requestedUrls[0]?.searchParams.get('sort_direction')).toBe('desc')
    expect(requestedUrls[0]?.searchParams.get('page')).toBe('1')
    expect(requestedUrls[0]?.searchParams.get('per_page')).toBe('50')

    const keywordRequest = page.waitForRequest(
        (request) =>
            isItemIndexRequest(request) &&
            new URL(request.url()).searchParams.get('keyword') === 'needle'
    )

    await page
        .getByPlaceholder('搜索名称、货号、规格、出产地或商品描述')
        .fill('needle')
    await keywordRequest
    await expect(page.getByText('Keyword backend result')).toBeVisible()

    await page
        .getByPlaceholder('搜索名称、货号、规格、出产地或商品描述')
        .fill('')

    const categoryRequest = page.waitForRequest((request) => {
        if (!isItemIndexRequest(request)) {
            return false
        }

        return new URL(request.url()).searchParams
            .getAll('category_ids[]')
            .includes(`${category.id}`)
    })

    await page.getByText('显示全部商品', { exact: true }).click()
    await page
        .getByRole('option', { name: '后端筛选分类', exact: true })
        .click()
    await page.keyboard.press('Escape')
    await categoryRequest
    await expect(page.getByText('Category backend result')).toBeVisible()

    await page.getByLabel('仅显示无库存', { exact: true }).check()

    const statusRequest = page.waitForRequest((request) => {
        if (!isItemIndexRequest(request)) {
            return false
        }

        const url = new URL(request.url())

        return (
            url.searchParams.get('out_of_stock') === '1' &&
            url.searchParams.get('not_listed') === '1'
        )
    })

    await page.getByLabel('仅显示未上架', { exact: true }).check()
    await statusRequest
    await expect(page.getByText('Status backend result')).toBeVisible()

    const sortRequest = page.waitForRequest((request) => {
        if (!isItemIndexRequest(request)) {
            return false
        }

        return (
            new URL(request.url()).searchParams.get('sort_by') === 'sold_count'
        )
    })

    await page.getByText('创建时间', { exact: true }).click()
    await page.getByRole('option', { name: '销量', exact: true }).click()
    await sortRequest

    const orderRequest = page.waitForRequest((request) => {
        if (!isItemIndexRequest(request)) {
            return false
        }

        const url = new URL(request.url())

        return (
            url.searchParams.get('sort_by') === 'sold_count' &&
            url.searchParams.get('sort_direction') === 'asc'
        )
    })

    await page
        .locator('.p-inputgroup')
        .filter({ hasText: '销量' })
        .locator('.p-inputgroupaddon')
        .click()
    await orderRequest
    await expect(page.getByText('Sorted backend result')).toBeVisible()

    const paginationRequest = page.waitForRequest((request) => {
        return (
            isItemIndexRequest(request) &&
            new URL(request.url()).searchParams.get('page') === '2'
        )
    })

    await page.getByRole('button', { name: 'Page 2', exact: true }).click()
    await paginationRequest
    await expect(page.getByText('Paginated backend result')).toBeVisible()

    await expect
        .poll(() => new URL(page.url()).searchParams.get('page'))
        .toBe('2')

    const pageQuery = new URL(page.url()).searchParams

    expect(pageQuery.getAll('category_ids')).toEqual([`${category.id}`])
    expect(pageQuery.get('out_of_stock')).toBe('1')
    expect(pageQuery.get('not_listed')).toBe('1')
    expect(pageQuery.get('sort_by')).toBe('sold_count')
    expect(pageQuery.get('sort_direction')).toBe('asc')

    const restoredRequest = page.waitForRequest((request) => {
        if (!isItemIndexRequest(request)) {
            return false
        }

        const url = new URL(request.url())

        return (
            url.searchParams
                .getAll('category_ids[]')
                .includes(`${category.id}`) &&
            url.searchParams.get('out_of_stock') === '1' &&
            url.searchParams.get('not_listed') === '1' &&
            url.searchParams.get('sort_by') === 'sold_count' &&
            url.searchParams.get('sort_direction') === 'asc' &&
            url.searchParams.get('page') === '2'
        )
    })

    await page.reload()
    await restoredRequest

    await expect(page.getByText('Paginated backend result')).toBeVisible()
    await expect(page.getByLabel('仅显示无库存', { exact: true })).toBeChecked()
    await expect(page.getByLabel('仅显示未上架', { exact: true })).toBeChecked()

    expect(runtimeErrors).toEqual([])
})
