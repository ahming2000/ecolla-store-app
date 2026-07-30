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

const variation = (
    id: number,
    itemId: number,
    price: number,
    stock: number,
    salePrice: number | null = null
) => {
    const finalPrice = salePrice ?? price

    return {
        id,
        barcode: `FILTER-${id}`,
        name: 'Default',
        name_en: 'Default',
        price,
        sale_price: salePrice,
        weight: 100,
        stock,
        image_id: null,
        item_id: itemId,
        final_price: finalPrice,
        price_text: `RM ${price.toFixed(2)}`,
        sale_price_text: salePrice === null ? '' : `RM ${salePrice.toFixed(2)}`,
        final_price_text: `RM ${finalPrice.toFixed(2)}`,
        created_at: timestamp,
        updated_at: timestamp,
    }
}

const item = (
    id: number,
    name: string,
    originId: number,
    itemOrigin: ReturnType<typeof origin>,
    options: {
        variations?: ReturnType<typeof variation>[]
    } = {}
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
    variations: options.variations ?? [variation(id, id, 10, 10)],
    images: [],
    all_images: [],
    cover_image: null,
    total_stock: (options.variations ?? [variation(id, id, 10, 10)]).reduce(
        (total, itemVariation) => total + itemVariation.stock,
        0
    ),
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

test('item cards show the cheapest available price and sold-out state', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)
    const firstOrigin = origin(911, 'Card Origin')
    const availableItem = item(
        931,
        'Available Item',
        firstOrigin.id,
        firstOrigin,
        {
            variations: [
                variation(941, 931, 4, 0),
                variation(942, 931, 12, 3, 9.5),
                variation(943, 931, 10, 5),
            ],
        }
    )
    const soldOutItem = item(
        932,
        'Sold Out Item',
        firstOrigin.id,
        firstOrigin,
        {
            variations: [variation(944, 932, 5, 0)],
        }
    )

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
        await route.fulfill({ json: [firstOrigin], status: 200 })
    })
    await page.route('**/ajax/item', async (route) => {
        await route.fulfill({
            json: [availableItem, soldOutItem],
            status: 200,
        })
    })

    await page.goto('/item')

    const availableItemCard = page.getByTestId(
        `shop-item-card-${availableItem.id}`
    )
    const soldOutItemCard = page.getByTestId(`shop-item-card-${soldOutItem.id}`)

    await expect(
        availableItemCard.getByText('RM 9.50', { exact: true })
    ).toBeVisible()
    await expect(
        availableItemCard.getByText('RM 4.00', { exact: true })
    ).toHaveCount(0)
    await expect(
        soldOutItemCard.getByText('已售完', { exact: true })
    ).toBeVisible()
    await expect(page.getByText('RMundefined')).toHaveCount(0)
    expect(runtimeErrors).toEqual([])
})
