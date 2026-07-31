import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface StorefrontImage {
    id: number
    src: string | null
    variation_id?: number
}

interface StorefrontVariation {
    id: number
    image: StorefrontImage | null
    stock: number
}

interface StorefrontItem {
    all_images: StorefrontImage[]
    categories: Array<{ id: number }>
    slug: string
    total_stock: number
    variations: StorefrontVariation[]
}

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

    test('supports responsive item galleries, variation images, stock, and category navigation', async ({
        page,
    }, testInfo) => {
        test.setTimeout(60_000)

        const runtimeErrors = collectRuntimeErrors(page)
        const itemResponse = await page.request.get('/ajax/item')

        expect(itemResponse.ok()).toBeTruthy()

        const items = (await itemResponse.json()) as StorefrontItem[]
        const detailItem = items
            .filter((item) => {
                return (
                    item.categories.length > 0 &&
                    item.variations.some((variation) => {
                        return (
                            variation.image?.src &&
                            item.all_images.some((image) => {
                                return (
                                    image.variation_id === variation.id &&
                                    image.src === variation.image?.src
                                )
                            })
                        )
                    })
                )
            })
            .sort((firstItem, secondItem) => {
                return (
                    secondItem.variations.length - firstItem.variations.length
                )
            })[0]

        expect(detailItem).toBeDefined()

        if (!detailItem) {
            throw new Error('No storefront item with variation images found.')
        }

        await page.goto(`/item/${detailItem.slug}`)

        const variationWithImage = detailItem.variations.find((variation) => {
            return detailItem.all_images.some((image) => {
                return (
                    image.variation_id === variation.id &&
                    image.src === variation.image?.src
                )
            })
        })

        expect(variationWithImage).toBeDefined()

        if (!variationWithImage) {
            throw new Error('No variation image was available for testing.')
        }

        const expectedVariationImage = detailItem.all_images.find((image) => {
            return (
                image.variation_id === variationWithImage.id &&
                image.src === variationWithImage.image?.src
            )
        })

        expect(expectedVariationImage?.src).toBeTruthy()

        if (!expectedVariationImage?.src) {
            throw new Error(
                'The selected variation image was missing from the gallery.'
            )
        }

        await expect(
            page.getByTestId(`variation-stock-${variationWithImage.id}`)
        ).toContainText(String(variationWithImage.stock))

        await page
            .getByTestId(`variation-image-button-${variationWithImage.id}`)
            .click()
        await expect(
            page.getByTestId('item-image-preview').locator('img')
        ).toHaveAttribute('src', expectedVariationImage.src)

        await page.getByTestId('item-image-preview').click()

        const fullScreenGallery = page.getByTestId(
            'item-image-fullscreen-gallery'
        )

        await expect(fullScreenGallery).toBeVisible()
        await expect(
            page.getByTestId('item-image-fullscreen-preview')
        ).toHaveAttribute('src', expectedVariationImage.src)

        await page.keyboard.press('Escape')
        await expect(fullScreenGallery).toBeHidden()

        const responsiveViewports = [
            { height: 812, name: 'mobile', width: 375 },
            { height: 1024, name: 'tablet', width: 768 },
            { height: 768, name: 'laptop', width: 1024 },
            { height: 900, name: 'desktop', width: 1440 },
        ]

        for (const viewport of responsiveViewports) {
            await page.setViewportSize(viewport)
            await page.waitForTimeout(150)

            const measurements = await page.evaluate(() => {
                const gallery = document.querySelector<HTMLElement>(
                    '[data-testid="item-image-galleria"]'
                )
                const variationList = document.querySelector<HTMLElement>(
                    '[data-testid="variation-scroll-container"]'
                )
                const detailColumn = variationList?.parentElement
                const recommendationCarousel =
                    document.querySelector<HTMLElement>(
                        '[data-testid="recommended-items-carousel"]'
                    )

                return {
                    detailHeight:
                        detailColumn?.getBoundingClientRect().height ?? 0,
                    galleryHeight: gallery?.getBoundingClientRect().height ?? 0,
                    recommendationHeight:
                        recommendationCarousel?.getBoundingClientRect()
                            .height ?? 0,
                    variationOverflowY: variationList
                        ? getComputedStyle(variationList).overflowY
                        : '',
                }
            })

            expect(measurements.galleryHeight).toBeGreaterThan(0)

            if (viewport.width < 768) {
                expect(measurements.variationOverflowY).toBe('visible')
                expect(measurements.recommendationHeight).toBeLessThan(380)
            } else {
                expect(measurements.variationOverflowY).toBe('auto')
                expect(
                    Math.abs(
                        measurements.detailHeight - measurements.galleryHeight
                    )
                ).toBeLessThanOrEqual(1)
            }

            if (viewport.name === 'mobile' || viewport.name === 'desktop') {
                await testInfo.attach(`item detail - ${viewport.name}`, {
                    body: await page.screenshot({ fullPage: true }),
                    contentType: 'image/png',
                })
            }
        }

        await page.setViewportSize({ height: 812, width: 375 })

        const breadcrumbPosition = await page
            .getByTestId('item-breadcrumb-scroll')
            .evaluate((element) => ({
                clientWidth: element.clientWidth,
                scrollLeft: element.scrollLeft,
                scrollWidth: element.scrollWidth,
            }))

        expect(
            Math.abs(
                breadcrumbPosition.scrollLeft +
                    breadcrumbPosition.clientWidth -
                    breadcrumbPosition.scrollWidth
            )
        ).toBeLessThanOrEqual(1)

        const recommendationCarousel = page.getByTestId(
            'recommended-items-carousel'
        )

        await expect(recommendationCarousel).toHaveAttribute('data-page', '0')
        await recommendationCarousel
            .getByRole('button', { name: 'Next Page', exact: true })
            .click()
        await expect(recommendationCarousel).toHaveAttribute('data-page', '1')
        await expect(
            recommendationCarousel.locator(
                '[data-p-carousel-item-start="true"]'
            )
        ).toHaveAttribute('aria-label', '2')

        const category = detailItem.categories[0]

        await page.getByTestId(`item-category-link-${category.id}`).click()
        await expect(page).toHaveURL(
            new RegExp(`/item\\?category=${category.id}(?:&|$)`)
        )

        const itemWithSoldOutVariation = items.find((item) =>
            item.variations.some((variation) => variation.stock <= 0)
        )

        expect(itemWithSoldOutVariation).toBeDefined()

        if (itemWithSoldOutVariation) {
            await page.goto(`/item/${itemWithSoldOutVariation.slug}`)

            const soldOutVariation = itemWithSoldOutVariation.variations.find(
                (variation) => variation.stock <= 0
            )

            expect(soldOutVariation).toBeDefined()

            if (soldOutVariation) {
                await expect(
                    page
                        .getByTestId(
                            `variation-quantity-${soldOutVariation.id}`
                        )
                        .locator('input')
                ).toBeDisabled()
            }
        }

        const soldOutItem = items.find((item) => item.total_stock <= 0)

        expect(soldOutItem).toBeDefined()

        if (soldOutItem) {
            await page.goto(`/item/${soldOutItem.slug}`)
            await expect(page.getByTestId('add-to-cart-button')).toBeDisabled()
        }

        expect(runtimeErrors).toEqual([])
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

        await expect(page).toHaveTitle('页面不存在 - e口乐零食店')
        await expect(page.getByTestId('error-page')).toHaveCSS(
            'background-color',
            'oklch(0.985 0 none)'
        )
        await expect(page.getByTestId('error-card')).toHaveCSS(
            'background-color',
            'rgb(255, 255, 255)'
        )

        const homeLink = page.getByRole('link', {
            name: '主页',
            exact: true,
        })

        await expect(homeLink).toHaveCSS(
            'background-color',
            /^oklch\(0\.656 0\.241 354\.308(?:014)?\)$/
        )
        await homeLink.click()
        await expect(page).toHaveURL(/\/$/)
        await expect(page).toHaveTitle('e口乐官方网站 - e口乐零食店')
        expect(runtimeErrors).toEqual([])
    })
})
