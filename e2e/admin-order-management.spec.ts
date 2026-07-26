import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface AdminOrder {
    id: number
    reference_num: string
    delivery_mode: string
    status: string
    tracking_no: string | null
    shipping_fee: number
    payment_method_id: number
    receipt_image_id: number
    note: string | null
    cus_name: string
    cus_phone: string
    cus_address: string | null
    created_at: string
    updated_at: string
    created_at_display: string
    subtotal: string
    items: Array<{
        id: number
        name: string
        name_en: string
        barcode: string
        price: number
        sale_price: number | null
        quantity: number
        created_at: string
        updated_at: string
    }>
    payment_method: {
        id: number
        name: string
    }
    receipt_image: {
        id: number
        name: string
        mime_type: string
        size: number
        url: string
        data_uri: null
        src: string
        created_at: string
        updated_at: string
    }
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await page.waitForLoadState('networkidle')
    await page.goto('/admin')
    await expect(page).toHaveURL(/\/admin$/)
}

const paginatedOrders = (
    data: unknown[],
    currentPage = 1,
    total = data.length
) => ({
    current_page: currentPage,
    data,
    last_page: Math.max(1, Math.ceil(total / 50)),
    per_page: 50,
    total,
})

const listingOrder = (id: number, referenceNumber: string) => ({
    id,
    reference_num: referenceNumber,
    delivery_mode: '外送',
    status: '处理中',
    tracking_no: null,
    shipping_fee: 3,
    payment_method_id: 1,
    receipt_image_id: 1,
    note: null,
    cus_name: 'Listing Customer',
    cus_phone: '0123456789',
    cus_address: '1 Listing Street',
    created_at: '2026-07-26T04:00:00+00:00',
    updated_at: '2026-07-26T04:00:00+00:00',
    created_at_display: '2026/07/26 12:00',
    subtotal: '24.00',
    items: [],
    payment_method: {
        id: 1,
        name: 'Online Banking',
    },
    receipt_image: null,
})

test('manages order fulfilment and exposes receipt and download actions', async ({
    page,
}, testInfo) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)
    const updatedStatuses: string[] = []
    const order: AdminOrder = {
        id: 91001,
        reference_num: 'ECOLLA-UI-TEST',
        delivery_mode: '外送',
        status: '处理中',
        tracking_no: 'TRACK-OLD',
        shipping_fee: 3,
        payment_method_id: 1,
        receipt_image_id: 1,
        note: 'Ring the bell',
        cus_name: 'Playwright Customer',
        cus_phone: '0123456789',
        cus_address: '1 Browser Street',
        created_at: '2026-07-26T04:00:00+00:00',
        updated_at: '2026-07-26T04:00:00+00:00',
        created_at_display: '2026/07/26 12:00',
        subtotal: '24.00',
        items: [
            {
                id: 1,
                name: '测试商品',
                name_en: 'Test item',
                barcode: 'SKU-TEST',
                price: 10,
                sale_price: 8,
                quantity: 3,
                created_at: '2026-07-26T04:00:00+00:00',
                updated_at: '2026-07-26T04:00:00+00:00',
            },
        ],
        payment_method: {
            id: 1,
            name: 'Online Banking',
        },
        receipt_image: {
            id: 1,
            name: 'ecolla.png',
            mime_type: 'image/png',
            size: 100,
            url: '/images/ecolla.png',
            data_uri: null,
            src: '/images/ecolla.png',
            created_at: '2026-07-26T04:00:00+00:00',
            updated_at: '2026-07-26T04:00:00+00:00',
        },
    }

    await login(page)

    await page.route(/\/ajax\/admin\/order\/?(?:\?.*)?$/, async (route) => {
        await route.fulfill({ json: paginatedOrders([order]) })
    })
    await page.route(
        `**/ajax/admin/order/${order.id}/tracking-number`,
        async (route) => {
            expect(route.request().method()).toBe('PATCH')

            const data = route.request().postDataJSON() as {
                tracking_no: string | null
            }

            order.tracking_no = data.tracking_no

            await route.fulfill({
                json: {
                    id: order.id,
                    status: order.status,
                    tracking_no: order.tracking_no,
                },
            })
        }
    )
    await page.route(
        `**/ajax/admin/order/${order.id}/status`,
        async (route) => {
            expect(route.request().method()).toBe('PATCH')

            const data = route.request().postDataJSON() as {
                status: string
                tracking_no: string | null
            }

            expect(data.tracking_no).toBe('TRACK-NEW')
            updatedStatuses.push(data.status)
            order.status = data.status
            order.tracking_no = data.tracking_no

            await route.fulfill({
                json: {
                    id: order.id,
                    status: order.status,
                    tracking_no: order.tracking_no,
                },
            })
        }
    )
    await page.route(`**/admin/order/${order.id}/download`, async (route) => {
        await route.fulfill({
            body: '%PDF-1.7\n%%EOF',
            headers: {
                'Content-Disposition':
                    'attachment; filename="ECOLLA-UI-TEST.pdf"',
                'Content-Type': 'application/pdf',
            },
        })
    })
    await page.route('**/images/ecolla.png', async (route) => {
        await route.fulfill({
            contentType: 'image/png',
            path: 'resources/js/assets/images/branding/ecolla.png',
        })
    })

    await page.goto('/admin/order')
    await page.waitForLoadState('networkidle')
    await page.getByRole('button', { name: '详情', exact: true }).click()

    const dialog = page.getByRole('dialog', {
        name: order.reference_num,
    })

    await expect(dialog).toBeVisible()
    await dialog
        .getByTestId(`order-tracking-number-${order.id}`)
        .fill('TRACK-NEW')
    await expect(page.getByText('邮寄追踪 ID 已更新。')).toBeVisible({
        timeout: 5_000,
    })

    await dialog.getByTestId(`order-status-${order.id}`).click()
    await page.getByRole('option', { name: '准备就绪', exact: true }).click()

    await expect(page.getByText('订单状态已更新。')).toBeVisible()
    await expect(dialog.getByTestId(`order-status-${order.id}`)).toContainText(
        '准备就绪'
    )

    await dialog.getByTestId(`order-status-${order.id}`).click()
    await page.getByRole('option', { name: '已退款', exact: true }).click()

    await expect.poll(() => updatedStatuses).toContain('已退款')
    await expect(dialog.getByTestId(`order-status-${order.id}`)).toContainText(
        '已退款'
    )

    const downloadPromise = page.waitForEvent('download')

    await dialog.getByTestId(`download-order-${order.id}`).click()

    const download = await downloadPromise

    expect(download.suggestedFilename()).toBe('ECOLLA-UI-TEST.pdf')

    await testInfo.attach('restored-order-detail-dialog', {
        body: await dialog.screenshot(),
        contentType: 'image/png',
    })

    await dialog.getByTestId(`view-order-receipt-${order.id}`).click()

    const receiptDialog = page.getByRole('dialog', {
        name: `订单 ${order.reference_num} 的收据`,
    })
    const receiptImage = receiptDialog.getByRole('img', {
        name: `订单 ${order.reference_num} 上传的收据`,
    })

    await expect(receiptDialog).toBeVisible()
    await expect(receiptImage).toHaveAttribute('src', '/images/ecolla.png')
    await expect
        .poll(() =>
            receiptImage.evaluate(
                (image: HTMLImageElement) => image.naturalWidth
            )
        )
        .toBeGreaterThan(0)

    await testInfo.attach('order-receipt-dialog', {
        body: await receiptDialog.screenshot(),
        contentType: 'image/png',
    })

    await page.keyboard.press('Escape')
    await expect(receiptDialog).toBeHidden()

    expect(runtimeErrors).toEqual([])
})

test('sends order filters and pagination to the backend', async ({ page }) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)
    const requestedUrls: URL[] = []
    const today = new Date()
    const selectedOrderDate = [
        today.getFullYear(),
        String(today.getMonth() + 1).padStart(2, '0'),
        String(today.getDate()).padStart(2, '0'),
    ].join('-')

    await login(page)

    await page.route(/\/ajax\/admin\/order\/?(?:\?.*)?$/, async (route) => {
        const url = new URL(route.request().url())
        const currentPage = Number(url.searchParams.get('page') ?? 1)

        requestedUrls.push(url)

        let referenceNumber = 'INITIAL-BACKEND-ORDER'

        if (currentPage === 2) {
            referenceNumber = 'PAGINATED-BACKEND-ORDER'
        } else if (url.searchParams.has('order_date')) {
            referenceNumber = 'DATE-FILTERED-BACKEND-ORDER'
        } else if (url.searchParams.has('delivery_mode')) {
            referenceNumber = 'MODE-FILTERED-BACKEND-ORDER'
        }

        await route.fulfill({
            json: paginatedOrders(
                [listingOrder(requestedUrls.length, referenceNumber)],
                currentPage,
                51
            ),
            status: 200,
        })
    })

    await page.goto('/admin/order')

    await expect(page.getByText('INITIAL-BACKEND-ORDER')).toBeVisible()
    expect(requestedUrls[0]?.searchParams.get('page')).toBe('1')
    expect(requestedUrls[0]?.searchParams.get('per_page')).toBe('50')

    const deliveryModeRequest = page.waitForRequest((request) => {
        const url = new URL(request.url())

        return (
            request.method() === 'GET' &&
            url.pathname === '/ajax/admin/order' &&
            url.searchParams.get('delivery_mode') === '外送' &&
            url.searchParams.get('page') === '1'
        )
    })

    await page
        .getByRole('combobox', { name: '全部订单模式', exact: true })
        .click()
    await page.getByRole('option', { name: '外送', exact: true }).click()
    await deliveryModeRequest
    await expect(page.getByText('MODE-FILTERED-BACKEND-ORDER')).toBeVisible()

    const dateFilterRequest = page.waitForRequest((request) => {
        const url = new URL(request.url())

        return (
            request.method() === 'GET' &&
            url.pathname === '/ajax/admin/order' &&
            url.searchParams.get('order_date') === selectedOrderDate &&
            url.searchParams.get('delivery_mode') === '外送' &&
            url.searchParams.get('page') === '1'
        )
    })

    await page.getByRole('combobox', { name: '订单日期', exact: true }).click()
    await page.locator('td[data-p-today="true"] .p-datepicker-day').click()
    await dateFilterRequest
    await expect(page.getByText('DATE-FILTERED-BACKEND-ORDER')).toBeVisible()

    const paginationRequest = page.waitForRequest((request) => {
        const url = new URL(request.url())

        return (
            request.method() === 'GET' &&
            url.pathname === '/ajax/admin/order' &&
            url.searchParams.get('order_date') === selectedOrderDate &&
            url.searchParams.get('delivery_mode') === '外送' &&
            url.searchParams.get('page') === '2'
        )
    })

    await page.getByRole('button', { name: 'Page 2', exact: true }).click()
    await paginationRequest
    await expect(page.getByText('PAGINATED-BACKEND-ORDER')).toBeVisible()

    await expect
        .poll(() => new URL(page.url()).searchParams.get('page'))
        .toBe('2')

    const pageQuery = new URL(page.url()).searchParams

    expect(pageQuery.get('order_date')).toBe(selectedOrderDate)
    expect(pageQuery.get('delivery_mode')).toBe('外送')

    const restoredRequest = page.waitForRequest((request) => {
        const url = new URL(request.url())

        return (
            request.method() === 'GET' &&
            url.pathname === '/ajax/admin/order' &&
            url.searchParams.get('order_date') === selectedOrderDate &&
            url.searchParams.get('delivery_mode') === '外送' &&
            url.searchParams.get('page') === '2'
        )
    })

    await page.reload()
    await restoredRequest

    await expect(page.getByText('PAGINATED-BACKEND-ORDER')).toBeVisible()

    expect(runtimeErrors).toEqual([])
})
