import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).not.toHaveURL(/\/admin\/login$/, {
        timeout: 15_000,
    })
    await page.goto('/admin')
    await expect(page).toHaveURL(/\/admin$/)
}

test('switches the dashboard sales reporting period', async ({ page }) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)

    await expect(
        page.getByRole('heading', { name: '销售摘要', exact: true })
    ).toBeVisible()
    await expect(page.getByTestId('dashboard-period-selector')).toBeVisible()

    for (const period of ['每日', '每周', '每月', '每年']) {
        await expect(
            page.getByRole('button', { name: period, exact: true })
        ).toBeVisible()
    }

    await expect(
        page.getByRole('button', { name: '每日', exact: true })
    ).toHaveAttribute('aria-pressed', 'true')

    await page.getByRole('button', { name: '每年', exact: true }).click()

    await expect(page).toHaveURL(/[?&]period=yearly(?:&|$)/)
    await expect(
        page.getByRole('button', { name: '每年', exact: true })
    ).toHaveAttribute('aria-pressed', 'true')

    const yearInput = page.getByLabel('年份', { exact: true })
    const previousYear = String(new Date().getFullYear() - 1)

    await expect(yearInput).toBeVisible()
    await yearInput.click()
    await page.getByText(previousYear, { exact: true }).click()
    await expect(page).toHaveURL(
        new RegExp(`[?&]date=${previousYear}-\\d{2}-\\d{2}(?:&|$)`)
    )

    await expect(
        page.getByTestId('dashboard-metric-completed-orders')
    ).toContainText('已完成订单')
    await expect(page.getByTestId('dashboard-metric-items-sold')).toContainText(
        '售出商品'
    )
    await expect(
        page.getByTestId('dashboard-metric-sales-revenue')
    ).toContainText('销售收入')
    await expect(
        page.getByTestId('dashboard-metric-canceled-order-value')
    ).toContainText('已取消或已退款订单金额')

    expect(runtimeErrors).toEqual([])
})

test('shows responsive and accessible dashboard visualizations', async ({
    page,
}, testInfo) => {
    const runtimeErrors = collectRuntimeErrors(page)
    const populatedReportingYear = new Date().getFullYear()

    await login(page)
    await page.goto(`/admin?period=yearly&date=${populatedReportingYear}-07-01`)

    await expect(
        page.getByRole('heading', { name: '销售趋势', exact: true })
    ).toBeVisible()
    await expect(page.getByTestId('dashboard-trend-point')).toHaveCount(12)
    await expect(page.getByTestId('dashboard-status-meter')).toHaveAttribute(
        'role',
        'meter'
    )
    await expect(page.getByTestId('dashboard-status-meter')).toHaveAttribute(
        'aria-valuemax',
        '100'
    )
    await expect(page.getByTestId('dashboard-status-meter')).toHaveAttribute(
        'aria-valuenow',
        '100'
    )
    await expect(page.getByTestId('dashboard-delivery-meter')).toHaveAttribute(
        'role',
        'meter'
    )
    await expect(
        page
            .getByTestId('dashboard-sales-trend')
            .getByRole('progressbar')
            .first()
    ).toHaveAttribute('aria-label', /已完成订单/)

    await page.getByTestId('dashboard-trend-data-table').click()
    await expect(
        page.getByRole('table', {
            name: '各时段的已完成订单数量与销售收入',
        })
    ).toBeVisible()

    for (const viewport of [
        { width: 360, height: 800 },
        { width: 768, height: 1024 },
        { width: 1024, height: 768 },
        { width: 1280, height: 800 },
        { width: 1440, height: 900 },
    ]) {
        await page.setViewportSize(viewport)

        await expect(page.getByTestId('dashboard-sales-trend')).toBeVisible()
        await expect(
            page.getByTestId('dashboard-status-distribution')
        ).toBeVisible()
        await expect(
            page.getByTestId('dashboard-delivery-distribution')
        ).toBeVisible()
        await expect
            .poll(() =>
                page.evaluate(
                    () =>
                        document.documentElement.scrollWidth <=
                        document.documentElement.clientWidth
                )
            )
            .toBe(true)

        if (viewport.width === 360 || viewport.width === 1280) {
            await testInfo.attach(
                `dashboard-visualizations-${viewport.width}px`,
                {
                    body: await page.screenshot({ fullPage: true }),
                    contentType: 'image/png',
                }
            )
        }
    }

    expect(runtimeErrors).toEqual([])
})
