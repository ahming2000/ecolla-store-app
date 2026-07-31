import { expect, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

test('displays changelog content in the selected language', async ({
    page,
}) => {
    const runtimeErrors = collectRuntimeErrors(page)

    await page.goto('/admin/changing-log')

    await expect(page.getByText('当前版本', { exact: true })).toBeVisible()
    await expect(page.getByText('v4.2.0 正式版', { exact: true })).toBeVisible()
    await expect(
        page.getByText(
            '商品详情页新增图片全屏预览、规格图片快捷定位、类别筛选链接、各规格库存显示及缺货购买限制',
            {
                exact: true,
            }
        )
    ).toBeVisible()
    await expect(
        page.getByText(
            '新增顾客订单查询，可使用订单编号和结账时填写的电话号码查看订单进度',
            {
                exact: true,
            }
        )
    ).toBeVisible()
    await expect(
        page.getByRole('button', { name: 'v4.1 正式版', exact: true })
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
        page.getByText('v4.2.0 Public Release', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText('v4.2.0 Public Release (2026/07/31)', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText(
            'Enhanced product pages with full-screen image previews, variation-image shortcuts, category filter links, stock details, and sold-out purchase controls.',
            {
                exact: true,
            }
        )
    ).toBeVisible()
    await expect(
        page.getByText('v3.0 Never Released', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText(
            '商品详情页新增图片全屏预览、规格图片快捷定位、类别筛选链接、各规格库存显示及缺货购买限制',
            {
                exact: true,
            }
        )
    ).toHaveCount(0)

    await page.getByRole('combobox', { name: 'Language' }).press('ArrowDown')
    await page.getByRole('option', { name: '中文', exact: true }).click()

    await expect(page.getByText('当前版本', { exact: true })).toBeVisible()
    await expect(
        page.getByText('v4.2.0 正式版（2026/07/31）', { exact: true })
    ).toBeVisible()
    await expect(
        page.getByText(
            '商品详情页新增图片全屏预览、规格图片快捷定位、类别筛选链接、各规格库存显示及缺货购买限制',
            {
                exact: true,
            }
        )
    ).toBeVisible()
    expect(runtimeErrors).toEqual([])
})
