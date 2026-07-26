import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface StorefrontVariation {
    id: number
    barcode: string
    name: string
    stock: number
}

interface StorefrontItem {
    id: number
    name: string
    slug: string
    variations: StorefrontVariation[]
}

const projectVariationIndex = (projectName: string): number => {
    return (
        {
            chromium: 0,
            firefox: 1,
            webkit: 2,
        }[projectName] ?? 0
    )
}

const waitForPost = (page: Page, pathname: string) => {
    return page.waitForResponse((response) => {
        return (
            response.request().method() === 'POST' &&
            new URL(response.url()).pathname === pathname
        )
    })
}

test.beforeEach(async ({ page }) => {
    await page.route('https://www.google.com/maps/**', async (route) => {
        await route.fulfill({
            body: '',
            contentType: 'text/html',
            status: 200,
        })
    })
    await page.route('**/_boost/browser-logs', async (route) => {
        await route.fulfill({ status: 204 })
    })
})

test('adds an item, restores the cart, and completes checkout', async ({
    page,
}, testInfo) => {
    test.setTimeout(90_000)

    const runtimeErrors = collectRuntimeErrors(page)
    const itemsResponse = await page.request.get('/ajax/item')

    expect(itemsResponse.ok()).toBeTruthy()

    const items = (await itemsResponse.json()) as StorefrontItem[]
    const availableVariations = items.flatMap((item) =>
        item.variations
            .filter((variation) => variation.stock >= 2)
            .map((variation) => ({ item, variation }))
    )
    const selected =
        availableVariations[projectVariationIndex(testInfo.project.name)]

    expect(selected).toBeDefined()

    if (!selected) {
        throw new Error('No in-stock storefront variation is available.')
    }

    await page.goto(`/item/${selected.item.slug}`)

    const variationCard = page.getByTestId(
        `variation-card-${selected.variation.id}`
    )
    const quantityInput = variationCard.getByRole('spinbutton')

    await expect(variationCard).toContainText(selected.variation.name)
    await quantityInput.fill('1')

    const cartVerification = waitForPost(page, '/ajax/cart/verify')

    await page.getByRole('button', { name: '加入购物车', exact: true }).click()
    await expect(page.getByText('成功加入购物车！')).toBeVisible()
    await cartVerification

    await page.goto('/cart')
    await expect(page.getByText('购物车（1 件）')).toBeVisible()
    await expect(
        page.getByText(selected.item.name, { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText(selected.variation.name, { exact: true })
    ).toBeVisible()

    await page.waitForLoadState('networkidle')
    await page.reload()
    await expect(page.getByText('购物车（1 件）')).toBeVisible()
    await expect(
        page.getByText(selected.item.name, { exact: true })
    ).toBeVisible()

    await page.waitForLoadState('networkidle')
    await page.getByRole('button', { name: '前往付款', exact: true }).click()
    await expect(page).toHaveURL(/\/checkout$/)
    await expect(page.getByText('购物车的商品', { exact: true })).toBeVisible()
    await page.getByLabel('电话号码', { exact: true }).fill('0123456789')

    await page.getByRole('button', { name: '上传收据', exact: true }).click()

    const uploadDialog = page.getByTestId('image-uploader-dialog')
    const fileChooserPromise = page.waitForEvent('filechooser')

    await uploadDialog
        .getByRole('button', { name: '浏览', exact: true })
        .click()

    const fileChooser = await fileChooserPromise

    await fileChooser.setFiles(
        'public/images/example-items/assorted-drinks.png'
    )
    await expect(
        uploadDialog.getByTestId('image-transformation-preview')
    ).toBeVisible()

    const receiptUpload = waitForPost(page, '/ajax/image/upload')

    await uploadDialog.getByTestId('confirm-image-upload').click()
    expect((await receiptUpload).ok()).toBeTruthy()
    await expect(uploadDialog).toBeHidden()
    await expect(page.getByText('assorted-drinks.webp')).toBeVisible()

    const checkoutRequest = waitForPost(page, '/ajax/cart/checkout')

    await page.getByRole('button', { name: '提交订单', exact: true }).click()

    const checkoutResponse = await checkoutRequest

    expect(checkoutResponse.ok()).toBeTruthy()
    expect(checkoutResponse.request().postDataJSON()).toMatchObject({
        cart: {
            items: [
                {
                    item: { id: selected.item.id },
                    variation: {
                        id: selected.variation.id,
                        barcode: selected.variation.barcode,
                    },
                    quantity: 1,
                },
            ],
        },
        checkoutForm: {
            cus_phone: '0123456789',
        },
    })

    await expect(page).toHaveURL(/\/checkout-successful\/\d+$/)
    await expect(
        page.getByRole('heading', {
            name: '感谢您的订单！',
            exact: true,
        })
    ).toBeVisible()
    await expect(page.getByText(/^您的订单编号是 ECOLLA\d+\。$/)).toBeVisible()

    await page.goto('/cart')
    await expect(page.getByText('购物车（0 件）')).toBeVisible()
    await expect(page.getByText('您的购物车为空')).toBeVisible()
    await page.waitForLoadState('networkidle')
    expect(runtimeErrors).toEqual([])
})
