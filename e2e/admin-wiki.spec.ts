import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/)
}

test('searches the admin wiki and previews a guide screenshot', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await login(page)
    await page.goto('/admin/wiki')

    await expect(
        page.getByRole('heading', { name: '管理员使用手册', exact: true })
    ).toBeVisible()
    await expect(page.getByTestId('wiki-article')).toHaveCount(8)

    await page
        .getByPlaceholder('搜索功能、操作或关键词', { exact: true })
        .fill('付款收据')

    await expect(page.getByTestId('wiki-article')).toHaveCount(1)
    await expect(
        page.getByRole('heading', { name: '订单处理', exact: true })
    ).toBeVisible()
    await expect(
        page.getByRole('heading', { name: '销售仪表板', exact: true })
    ).toHaveCount(0)

    await page.getByRole('button', { name: '清除搜索', exact: true }).click()

    await expect(page.getByTestId('wiki-article')).toHaveCount(8)
    await page.getByTestId('wiki-screenshot-dashboard').click()

    const screenshotDialog = page.getByRole('dialog', {
        name: '销售仪表板截图',
    })

    await expect(screenshotDialog).toBeVisible()
    await expect(screenshotDialog.getByRole('img')).toBeVisible()
    expect(runtimeErrors).toEqual([])
})
