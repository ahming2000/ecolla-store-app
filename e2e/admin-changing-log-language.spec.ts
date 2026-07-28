import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

test('displays changelog content in the selected language', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await page.goto('/admin/changing-log')

    await expect(page.getByText('当前版本', { exact: true })).toBeVisible()
    await expect(page.getByText('v4.1.0 正式版', { exact: true })).toBeVisible()
    await expect(
        page.getByText('添加了用户在个人资料页面更新密码的功能', {
            exact: true,
        })
    ).toBeVisible()
    await expect(
        page.getByText('v3.0 未发布版本', { exact: true })
    ).toBeVisible()

    await page.getByRole('combobox', { name: '语言' }).press('ArrowDown')
    await page.getByRole('option', { name: 'English', exact: true }).click()

    await expect(
        page.getByText('Current version', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('v4.1.0 Public Release', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('v4.1.0 Public Release (2026/07/28)', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText(
            'Added the ability for users to update their password from their profile.',
            {
                exact: true,
            }
        )
    ).toBeVisible()
    await expect(
        page.getByText('v3.0 Never Released', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('添加了用户在个人资料页面更新密码的功能', {
            exact: true,
        })
    ).toHaveCount(0)

    await page.getByRole('combobox', { name: 'Language' }).press('ArrowDown')
    await page.getByRole('option', { name: '中文', exact: true }).click()

    await expect(page.getByText('当前版本', { exact: true })).toBeVisible()
    await expect(
        page.getByText('v4.1.0 正式版（2026/07/28）', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('添加了用户在个人资料页面更新密码的功能', {
            exact: true,
        })
    ).toBeVisible()
    expect(runtimeErrors).toEqual([])
})
