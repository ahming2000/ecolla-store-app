import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

test.describe('order tracking', () => {
    test.beforeEach(async ({ page }) => {
        await page.route('https://www.google.com/maps/**', async (route) => {
            await route.fulfill({
                body: '',
                contentType: 'text/html',
                status: 200,
            })
        })
    })

    test('looks up an order and remains comfortable on mobile', async ({
        page,
    }) => {
        const runtimeErrors = collectRuntimeErrors(page)

        await page.route('**/ajax/order/track', async (route) => {
            expect(route.request().postDataJSON()).toEqual({
                reference_num: 'ECOLLA-TRACK-1001',
                phone: '0123456789',
            })

            await route.fulfill({
                contentType: 'application/json',
                json: {
                    reference_num: 'ECOLLA-TRACK-1001',
                    delivery_mode: '外送',
                    status: '准备就绪',
                    tracking_no: null,
                    shipping_fee: 4.5,
                    subtotal: 25,
                    total: 29.5,
                    note: 'Leave at the door',
                    created_at: '2026-07-30T08:00:00+08:00',
                    updated_at: '2026-07-30T09:30:00+08:00',
                    items: [
                        {
                            id: 1,
                            name: '促销商品',
                            name_en: 'Sale item',
                            barcode: 'SALE-001',
                            quantity: 2,
                            unit_price: 10,
                            line_total: 20,
                        },
                        {
                            id: 2,
                            name: '原价商品',
                            name_en: 'Regular item',
                            barcode: 'REGULAR-001',
                            quantity: 1,
                            unit_price: 5,
                            line_total: 5,
                        },
                    ],
                },
                status: 200,
            })
        })

        await page.setViewportSize({ height: 844, width: 390 })
        await page.goto('/track-order?reference=ECOLLA-TRACK-1001')

        await expect(
            page.getByRole('heading', { name: '追踪您的订单', exact: true })
        ).toBeVisible()
        await expect(page.getByLabel('订单编号')).toHaveValue(
            'ECOLLA-TRACK-1001'
        )

        await page.getByLabel('电话号码').fill('0123456789')
        await page
            .getByRole('button', { name: '追踪订单', exact: true })
            .click()

        await expect(page.getByTestId('order-tracking-result')).toBeVisible()
        await expect(page.getByText('订单 ECOLLA-TRACK-1001')).toBeVisible()
        await expect(page.getByText('准备就绪', { exact: true })).toBeVisible()
        await expect(page.getByTestId('order-tracking-number')).toHaveText(
            '暂未提供'
        )
        await expect(page.getByText('促销商品', { exact: true })).toBeVisible()
        await expect(page.getByText('RM 29.50', { exact: true })).toBeVisible()

        const horizontalOverflow = await page.evaluate(
            () => document.documentElement.scrollWidth - window.innerWidth
        )

        expect(horizontalOverflow).toBeLessThanOrEqual(1)
        expect(runtimeErrors).toEqual([])
    })

    test('shows a localized message when order details do not match', async ({
        page,
    }) => {
        await page.route('**/ajax/order/track', async (route) => {
            await route.fulfill({
                contentType: 'application/json',
                json: { message: 'Order details could not be matched.' },
                status: 404,
            })
        })

        await page.goto('/track-order')
        await page.getByLabel('订单编号').fill('ECOLLA-UNKNOWN')
        await page.getByLabel('电话号码').fill('0199999999')
        await page
            .getByRole('button', { name: '追踪订单', exact: true })
            .click()

        await expect(page.getByTestId('order-tracking-error')).toContainText(
            '无法匹配订单资料，请检查后再试。'
        )
        await expect(page.getByTestId('order-tracking-result')).toHaveCount(0)
    })
})
