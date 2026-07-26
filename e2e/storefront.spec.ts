import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

test.describe('storefront', () => {
    test.beforeEach(async ({ page }) => {
        await page.route('https://www.google.com/maps/**', async (route) => {
            await route.fulfill({
                body: '',
                contentType: 'text/html',
                status: 200,
            })
        })
    })

    test('loads the landing page and navigates to the item catalog', async ({
        page,
    }) => {
        const runtimeErrors = collectRuntimeErrors(page)
        const response = await page.goto('/')

        expect(response?.ok()).toBeTruthy()
        await expect(page).toHaveTitle('e口乐官方网站 - e口乐零食店')
        await expect(page.getByText('欢迎来到', { exact: false })).toBeVisible()

        const favicon = page.locator('link[rel="icon"]')

        await expect(favicon).toHaveAttribute('type', 'image/png')
        await expect(favicon).toHaveAttribute('sizes', '200x200')
        await expect(favicon).toHaveAttribute(
            'href',
            /\/resources\/js\/assets\/images\/branding\/ecolla\.png$/
        )

        const faviconHref = await favicon.getAttribute('href')
        const faviconResponse = await page.request.get(
            new URL(faviconHref!, page.url()).href
        )

        expect(faviconResponse.ok()).toBeTruthy()
        expect(faviconResponse.headers()['content-type']).toContain('image/png')

        const itemImages = page.getByTestId('shop-item-image')
        const itemImageCount = await itemImages.count()

        expect(itemImageCount).toBeGreaterThan(0)
        await expect(itemImages.first()).toBeVisible()

        await expect
            .poll(async () => {
                const imageStates = await itemImages.evaluateAll((images) =>
                    images.map((image) => {
                        const productImage = image as HTMLImageElement

                        return {
                            isLoaded:
                                productImage.complete &&
                                productImage.naturalWidth > 0 &&
                                productImage.naturalHeight > 0,
                            source: productImage.currentSrc,
                        }
                    })
                )

                return imageStates.every(
                    ({ isLoaded, source }) =>
                        isLoaded && source.includes('/images/example-items/')
                )
            })
            .toBe(true)

        await page
            .getByRole('button', { name: '点击浏览商品', exact: true })
            .click()

        await expect(page).toHaveURL(/\/item$/)
        await expect(page).toHaveTitle('商品 - e口乐零食店')
        await expect(
            page.getByPlaceholder('搜索名称、货号、规格、出产地或商品描述')
        ).toBeVisible()
        expect(runtimeErrors).toEqual([])
    })

    test('shows an empty cart with checkout disabled', async ({ page }) => {
        const runtimeErrors = collectRuntimeErrors(page)

        await page.goto('/cart')

        await expect(page.getByText('购物车（0 件）')).toBeVisible()
        await expect(page.getByText('您的购物车为空')).toBeVisible()
        await expect(
            page.getByRole('button', { name: '清空', exact: true })
        ).toBeDisabled()
        await expect(
            page.getByRole('button', { name: '前往付款', exact: true })
        ).toBeDisabled()
        expect(runtimeErrors).toEqual([])
    })

    test('updates storefront routes when the shopper selects English', async ({
        page,
    }) => {
        test.setTimeout(60_000)

        const runtimeErrors = collectRuntimeErrors(page)

        await page.goto('/cart')

        const languageSwitcher = page.getByRole('combobox', {
            name: '语言',
        })

        await languageSwitcher.press('ArrowDown')
        await page.getByRole('option', { name: 'English', exact: true }).click()

        await expect(page.locator('html')).toHaveAttribute('lang', 'en')
        await expect(
            page.getByText('Cart (0 items)', { exact: true })
        ).toBeVisible()
        await expect(
            page.getByText('Your cart is empty', { exact: true })
        ).toBeVisible()
        await expect(
            page.getByText('Order summary', { exact: true })
        ).toBeVisible()
        await expect(
            page.getByText('Self pickup', { exact: true }).first()
        ).toBeVisible()

        await page.waitForLoadState('networkidle')
        await page.goto('/item')

        await expect(
            page.getByPlaceholder(
                'Search name, SKU, variation, origin, or item description'
            )
        ).toBeVisible()

        const firstItemImage = page.getByTestId('shop-item-image').first()

        await expect(firstItemImage).toBeVisible()
        await firstItemImage.click()

        await expect(page).toHaveURL(/\/item\/[a-z0-9]+(?:-[a-z0-9]+)*$/)
        expect(new URL(page.url()).pathname).not.toMatch(/\/item\/\d+$/)
        await expect(
            page.getByRole('button', { name: 'Add to cart', exact: true })
        ).toBeVisible()
        await expect(
            page.getByText('Item description', { exact: true })
        ).toBeVisible()

        await page.goto('/payment-method')

        await expect(
            page.getByText('Payment methods', { exact: true }).first()
        ).toBeVisible()
        await expect(
            page.getByText('These are the payment methods we accept.', {
                exact: true,
            })
        ).toBeVisible()

        const paymentMethodIcons = page.locator('.p-card img')

        await expect(paymentMethodIcons).toHaveCount(5)
        await expect
            .poll(async () =>
                paymentMethodIcons.evaluateAll((images) =>
                    images.every((image) => {
                        const paymentMethodIcon = image as HTMLImageElement

                        return (
                            paymentMethodIcon.complete &&
                            paymentMethodIcon.naturalWidth > 0 &&
                            paymentMethodIcon.currentSrc.includes(
                                '/resources/js/assets/images/payment-methods/icons/'
                            )
                        )
                    })
                )
            )
            .toBe(true)

        await page.getByText('View QR code', { exact: true }).first().click()

        const paymentMethodQrCode = page.getByRole('dialog').locator('img')

        await expect(paymentMethodQrCode).toBeVisible()
        await expect
            .poll(async () =>
                paymentMethodQrCode.evaluate((image) => {
                    const qrCode = image as HTMLImageElement

                    return (
                        qrCode.complete &&
                        qrCode.naturalWidth > 0 &&
                        qrCode.currentSrc.includes(
                            '/resources/js/assets/images/payment-methods/qr-codes/'
                        )
                    )
                })
            )
            .toBe(true)

        await page.waitForLoadState('networkidle')
        await page.goto('/checkout')

        await expect(page.getByText('Checkout', { exact: true })).toBeVisible()
        await expect(
            page.getByText('Items in your cart', { exact: true })
        ).toBeVisible()
        await expect(
            page.getByText('Details (Self pickup)', { exact: true })
        ).toBeVisible()
        await expect(
            page.getByLabel('Phone number', { exact: true })
        ).toBeVisible()

        await page.waitForLoadState('networkidle')
        await page.goto('/')

        await expect(
            page.getByText('Welcome to', { exact: true })
        ).toBeVisible()
        await expect(
            page.getByRole('button', { name: 'Browse items', exact: true })
        ).toBeVisible()
        expect(runtimeErrors).toEqual([])
    })

    test('uses the local fallback when a seeded item image fails', async ({
        page,
    }) => {
        await page.route('**/images/example-items/*.png', async (route) => {
            await route.abort()
        })

        await page.goto('/')

        const itemImages = page.getByTestId('shop-item-image')
        const itemImageCount = await itemImages.count()

        expect(itemImageCount).toBeGreaterThan(0)
        await expect(itemImages.first()).toHaveAttribute(
            'src',
            /\/resources\/js\/assets\/images\/branding\/ecolla\.png$/
        )

        const fallbackStates = await itemImages.evaluateAll((images) =>
            images.map((image) => {
                const productImage = image as HTMLImageElement

                return {
                    isLoaded:
                        productImage.complete &&
                        productImage.naturalWidth > 0 &&
                        productImage.naturalHeight > 0,
                    source: productImage.currentSrc,
                }
            })
        )

        expect(
            fallbackStates.every(
                ({ isLoaded, source }) =>
                    isLoaded &&
                    source.endsWith(
                        '/resources/js/assets/images/branding/ecolla.png'
                    )
            )
        ).toBe(true)
    })

    test('renders the shop error page and returns home', async ({ page }) => {
        const runtimeErrors = collectRuntimeErrors(page)
        const response = await page.goto('/page-that-does-not-exist')

        expect(response?.status()).toBe(404)
        await expect(
            page.getByRole('heading', { name: '404', exact: true })
        ).toBeVisible()
        await expect(
            page.getByText('页面不存在', { exact: true })
        ).toBeVisible()

        await page.getByRole('button', { name: '主页', exact: true }).click()

        await expect(page).toHaveURL(/\/$/)
        await expect(page).toHaveTitle('e口乐官方网站 - e口乐零食店')
        expect(runtimeErrors).toEqual([])
    })
})
