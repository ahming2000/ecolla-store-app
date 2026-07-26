import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface AdminItem {
    id: number
    name: string
    name_en: string | null
    is_listed: boolean
    origin: {
        name: string
        name_en: string
    } | null
}

interface AdminItemPage {
    data: AdminItem[]
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
}

const selectLanguage = async (
    page: Page,
    language: 'en' | 'zh'
): Promise<void> => {
    const currentLanguage = await page.locator('html').getAttribute('lang')

    if (currentLanguage === language) {
        return
    }

    const languageSwitcher = page.getByRole('combobox', {
        name: currentLanguage === 'en' ? 'Language' : '语言',
    })

    await languageSwitcher.press('ArrowDown')
    await page
        .getByRole('option', {
            name: language === 'en' ? 'English' : '中文',
            exact: true,
        })
        .click()
    await expect(page.locator('html')).toHaveAttribute('lang', language)
    await page.waitForLoadState('networkidle')
}

test('displays every admin route and bilingual data in the selected language', async ({
    page,
}) => {
    test.setTimeout(90_000)

    const runtimeErrors = collectRuntimeErrors(page)
    let isLoggedIn = false

    try {
        await login(page)
        isLoggedIn = true
        await selectLanguage(page, 'en')

        const routes = [
            ['/admin', 'Dashboard'],
            ['/admin/item', 'Item management'],
            ['/admin/order', 'Orders'],
            ['/admin/wiki', 'Admin handbook'],
            ['/admin/profile', 'Profile'],
            ['/admin/user', 'Staff account management'],
            ['/admin/setting', 'Website settings'],
            ['/admin/changing-log', 'Change log'],
        ] as const

        for (const [path, title] of routes) {
            await page.goto(path)
            await expect(page.locator('html')).toHaveAttribute('lang', 'en')
            await expect(page).toHaveTitle(`${title} - e口乐零食店`)
            await page.waitForLoadState('networkidle')
        }

        const itemsResponse = await page.request.get('/ajax/admin/item', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        expect(itemsResponse.ok()).toBeTruthy()

        const { data: items } = (await itemsResponse.json()) as AdminItemPage
        const item = items.find(
            (candidate) =>
                candidate.name_en && candidate.name_en !== candidate.name
        )

        expect(item).toBeDefined()

        if (!item?.name_en) {
            throw new Error('No bilingual item is available for this test.')
        }

        await page.goto('/admin/item')

        const itemCard = page.getByTestId(`item-card-${item.id}`)

        await expect(
            itemCard.getByText(item.name_en, { exact: true })
        ).toBeVisible()
        await expect(
            itemCard.getByText(item.name, { exact: true })
        ).toHaveCount(0)
        await expect(
            itemCard.getByTestId(`delete-item-${item.id}`)
        ).toHaveAttribute('aria-label', `Delete “${item.name_en}”`)
        await expect(
            itemCard
                .getByTestId(`item-listing-toggle-${item.id}`)
                .getByRole('switch')
        ).toHaveAttribute(
            'aria-label',
            `${item.is_listed ? 'Unlist' : 'List'} “${item.name_en}”`
        )

        if (item.origin && item.origin.name_en !== item.origin.name) {
            await page.goto('/admin/setting')
            await expect(
                page.getByText(item.origin.name_en, { exact: true })
            ).toBeVisible()
        }

        await selectLanguage(page, 'zh')
        await page.goto('/admin/item')
        await expect(page.locator('html')).toHaveAttribute('lang', 'zh')
        await expect(
            itemCard.getByText(item.name, { exact: true })
        ).toBeVisible()
        await expect(
            itemCard.getByText(item.name_en, { exact: true })
        ).toHaveCount(0)
        expect(runtimeErrors).toEqual([])
    } finally {
        if (isLoggedIn && !page.isClosed()) {
            await page.goto('/admin')
            await selectLanguage(page, 'zh')
        }
    }
})
