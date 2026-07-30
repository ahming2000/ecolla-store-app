import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const cart = {
    deliveryMode: 'self-pickup',
    items: [
        {
            item: {
                id: 901,
                name: 'Cover fallback item',
                name_en: 'Cover fallback item',
                slug: 'cover-fallback-item',
                cover_image: '/images/example-items/assorted-drinks.png',
                cover_thumbnail: '/images/example-items/savory-snacks.png',
            },
            variation: {
                id: 901,
                barcode: 'CART-IMAGE-901',
                name: 'Broken variation image',
                name_en: 'Broken variation image',
                final_price: 12,
                image: {
                    src: '/images/example-items/missing-variation.png',
                    thumbnail: {
                        src: '/images/example-items/missing-variation-thumbnail.webp',
                    },
                },
                stock: 10,
                weight: 0.25,
            },
            quantity: 1,
        },
        {
            item: {
                id: 902,
                name: 'Local fallback item',
                name_en: 'Local fallback item',
                slug: 'local-fallback-item',
                cover_image: '/images/example-items/missing-cover.png',
                cover_thumbnail:
                    '/images/example-items/missing-cover-thumbnail.webp',
            },
            variation: {
                id: 902,
                barcode: 'CART-IMAGE-902',
                name: 'Broken item images',
                name_en: 'Broken item images',
                final_price: 15,
                image: {
                    src: '/images/example-items/missing-second-variation.png',
                    thumbnail: {
                        src: '/images/example-items/missing-second-variation-thumbnail.webp',
                    },
                },
                stock: 10,
                weight: 0.5,
            },
            quantity: 1,
        },
    ],
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
    await page.route('**/ajax/cart/verify', async (route) => {
        await route.fulfill({
            body: JSON.stringify(cart),
            contentType: 'application/json',
            status: 200,
        })
    })
    await page.addInitScript((storedCart) => {
        window.sessionStorage.setItem('cart', JSON.stringify(storedCart))
    }, cart)
})

test('falls back from variation images to item covers and then the local image', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)

    for (const viewport of [
        { height: 844, width: 390 },
        { height: 800, width: 1280 },
    ]) {
        await page.setViewportSize(viewport)
        await page.goto('/cart')

        const cartImages = page.getByTestId('cart-item-image')

        await expect(cartImages).toHaveCount(2)
        await expect
            .poll(async () =>
                cartImages.evaluateAll((images) =>
                    images.map((image) => {
                        const cartImage = image as HTMLImageElement

                        return {
                            isLoaded:
                                cartImage.complete &&
                                cartImage.naturalWidth > 0 &&
                                cartImage.naturalHeight > 0,
                            source: cartImage.currentSrc,
                        }
                    })
                )
            )
            .toEqual([
                {
                    isLoaded: true,
                    source: expect.stringContaining(
                        '/images/example-items/savory-snacks.png'
                    ),
                },
                {
                    isLoaded: true,
                    source: expect.stringMatching(
                        /\/(?:resources\/js\/assets\/images\/branding\/ecolla\.png|build\/assets\/ecolla-[^/]+\.png)$/
                    ),
                },
            ])

        await page.goto('/checkout')

        const checkoutImages = page.getByTestId('checkout-cart-item-image')

        await expect(checkoutImages).toHaveCount(2)
        await expect
            .poll(async () =>
                checkoutImages.evaluateAll((images) =>
                    images.map((image) => {
                        const cartImage = image as HTMLImageElement

                        return {
                            isLoaded:
                                cartImage.complete &&
                                cartImage.naturalWidth > 0 &&
                                cartImage.naturalHeight > 0,
                            source: cartImage.currentSrc,
                        }
                    })
                )
            )
            .toEqual([
                {
                    isLoaded: true,
                    source: expect.stringContaining(
                        '/images/example-items/savory-snacks.png'
                    ),
                },
                {
                    isLoaded: true,
                    source: expect.stringMatching(
                        /\/(?:resources\/js\/assets\/images\/branding\/ecolla\.png|build\/assets\/ecolla-[^/]+\.png)$/
                    ),
                },
            ])
    }

    expect(runtimeErrors).toEqual([])
})
