import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const timestamp = '2026-07-26T00:00:00.000000Z'

const origin = (id: number, name: string) => ({
    id,
    name,
    name_en: name,
    items_count: 1,
    created_at: timestamp,
    updated_at: timestamp,
})

const item = (
    id: number,
    name: string,
    originId: number,
    itemOrigin: ReturnType<typeof origin>
) => ({
    id,
    name,
    name_en: name,
    slug: name.toLowerCase().replaceAll(' ', '-'),
    desc: null,
    is_listed: true,
    view_count: 0,
    sold_count: 0,
    origin_id: originId,
    origin: itemOrigin,
    categories: [],
    variations: [
        {
            id,
            barcode: `FILTER-${id}`,
            name: 'Default',
            name_en: 'Default',
            price: 10,
            sale_price: null,
            weight: 100,
            stock: 10,
            image_id: null,
            item_id: id,
            final_price: 10,
            price_text: '10.00',
            sale_price_text: '',
            final_price_text: '10.00',
            created_at: timestamp,
            updated_at: timestamp,
        },
    ],
    images: [],
    all_images: [],
    cover_image: null,
    total_stock: 10,
    total_image_count: 0,
    created_at: timestamp,
    updated_at: timestamp,
})

test('keeps storefront filters in the URL and restores them after refresh', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)
    const firstOrigin = origin(901, 'Origin One')
    const secondOrigin = origin(902, 'Origin Two')

    await page.route('https://www.google.com/maps/**', async (route) => {
        await route.fulfill({
            body: '',
            contentType: 'text/html',
            status: 200,
        })
    })
    await page.route('**/ajax/category', async (route) => {
        await route.fulfill({ json: [], status: 200 })
    })
    await page.route('**/ajax/origin', async (route) => {
        await route.fulfill({
            json: [firstOrigin, secondOrigin],
            status: 200,
        })
    })
    await page.route('**/ajax/item', async (route) => {
        await route.fulfill({
            json: [
                item(901, 'Origin One Item', firstOrigin.id, firstOrigin),
                item(902, 'Origin Two Item', secondOrigin.id, secondOrigin),
            ],
            status: 200,
        })
    })

    await page.goto(`/item?origin=${secondOrigin.id}`)

    await expect(
        page.getByText('Origin Two Item', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('Origin One Item', { exact: true })
    ).toHaveCount(0)
    await expect(
        page.getByText(secondOrigin.name, { exact: true })
    ).toBeVisible()

    const keywordFilter = page.getByPlaceholder(
        '搜索名称、货号、规格、出产地或商品描述'
    )

    await keywordFilter.fill('Two')
    await expect
        .poll(() => new URL(page.url()).searchParams.get('keyword'))
        .toBe('Two')
    expect(new URL(page.url()).searchParams.getAll('origin')).toEqual([
        String(secondOrigin.id),
    ])

    await page.reload()

    await expect(keywordFilter).toHaveValue('Two')
    await expect(
        page.getByText(secondOrigin.name, { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('Origin Two Item', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('Origin One Item', { exact: true })
    ).toHaveCount(0)
    expect(runtimeErrors).toEqual([])
})
