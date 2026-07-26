import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
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
