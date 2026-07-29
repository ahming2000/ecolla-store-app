import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface LocalizedSettingOption {
    id: number
    name: string
    name_en: string | null
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel(/^(Account ID|账户 ID)$/).fill('admin')
    await page.getByLabel(/^(Password|密码)$/).fill('password')
    await page.getByRole('button', { name: /^(Login|登录)$/ }).click()
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
}

const waitForSettingUpdate = (
    page: Page,
    path: '/admin/setting/shipping' | '/admin/setting/free-shipping'
) => {
    return page.waitForResponse((response) => {
        return (
            response.request().method() === 'PATCH' &&
            new URL(response.url()).pathname === path
        )
    })
}

test('updates shipping rules and localizes category and origin labels', async ({
    page,
}) => {
    test.setTimeout(90_000)

    const runtimeErrors = collectRuntimeErrors(page)
    let initialLanguage: 'en' | 'zh' = 'zh'
    let originalFee = ''
    let originalThreshold = ''
    let originalDescription = ''
    let originalFreeShippingEnabled = false
    let settingsCaptured = false

    try {
        await login(page)
        initialLanguage =
            (await page.locator('html').getAttribute('lang')) === 'en'
                ? 'en'
                : 'zh'
        await selectLanguage(page, 'en')
        await page.goto('/admin/setting')

        const shippingFeeInput = page.getByRole('spinbutton', {
            name: 'Shipping fee',
        })
        const thresholdInput = page.getByRole('spinbutton', {
            name: 'Free-shipping order threshold',
        })
        const descriptionInput = page.getByRole('textbox', {
            name: 'Free-shipping note',
        })
        const freeShippingToggle = page.getByRole('switch', {
            name: 'Enable free shipping',
        })

        originalFee = await shippingFeeInput.inputValue()
        originalThreshold = await thresholdInput.inputValue()
        originalDescription = await descriptionInput.inputValue()
        originalFreeShippingEnabled = await freeShippingToggle.isChecked()
        settingsCaptured = true

        const updatedFee = originalFee === '6.75' ? '7.25' : '6.75'
        const updatedThreshold =
            originalThreshold === '66.00' ? '76.00' : '66.00'
        const updatedDescription = `Free shipping browser test ${Date.now()}`

        await shippingFeeInput.fill(updatedFee)
        const shippingUpdate = waitForSettingUpdate(
            page,
            '/admin/setting/shipping'
        )
        await page.getByTestId('save-shipping-fee').click()
        await shippingUpdate
        await expect(page.getByText('Shipping fee updated.')).toBeVisible()

        if (!(await freeShippingToggle.isChecked())) {
            await freeShippingToggle.click()
        }
        await thresholdInput.fill(updatedThreshold)
        await descriptionInput.fill(updatedDescription)
        const freeShippingUpdate = waitForSettingUpdate(
            page,
            '/admin/setting/free-shipping'
        )
        await page.getByTestId('save-free-shipping').click()
        await freeShippingUpdate
        await expect(
            page.getByText('Free-shipping settings updated.')
        ).toBeVisible()

        await page.reload()
        await expect(shippingFeeInput).toHaveValue(updatedFee)
        await expect(thresholdInput).toHaveValue(updatedThreshold)
        await expect(descriptionInput).toHaveValue(updatedDescription)
        await expect(freeShippingToggle).toBeChecked()

        const expectedFreeShippingNotice = `Special event! Spend RM ${Number(updatedThreshold).toFixed(2)} or more to get free shipping.`
        const expectedChineseFreeShippingNotice = `限时优惠！消费满 RM ${Number(updatedThreshold).toFixed(2)} 即可免运！`

        await page.goto('/')
        await expect(page.getByTestId('free-shipping-notice')).toHaveCount(0)
        await selectLanguage(page, 'zh')

        await page.goto('/item')
        await expect(page.getByTestId('free-shipping-notice')).toContainText(
            expectedChineseFreeShippingNotice
        )
        await selectLanguage(page, 'en')

        for (const path of ['/item', '/cart', '/checkout', '/payment-method']) {
            await page.goto(path)
            await expect(
                page.getByTestId('free-shipping-notice')
            ).toContainText(expectedFreeShippingNotice)
        }

        const freeShippingNotice = page.getByTestId('free-shipping-notice')
        await freeShippingNotice.getByRole('button').click()
        await expect(freeShippingNotice).toHaveCount(0)

        await page.goto('/cart')
        await expect(page.getByTestId('configured-shipping-fee')).toContainText(
            `RM ${updatedFee}`
        )
        await expect(page.getByTestId('free-shipping-description')).toHaveText(
            updatedDescription
        )

        const [categoriesResponse, originsResponse] = await Promise.all([
            page.request.get('/ajax/admin/category'),
            page.request.get('/ajax/admin/origin'),
        ])
        const categories =
            (await categoriesResponse.json()) as LocalizedSettingOption[]
        const origins =
            (await originsResponse.json()) as LocalizedSettingOption[]
        const category = categories.find(
            (option) => option.name_en && option.name_en !== option.name
        )
        const origin = origins.find(
            (option) => option.name_en && option.name_en !== option.name
        )

        expect(category).toBeDefined()
        expect(origin).toBeDefined()

        if (!category?.name_en || !origin?.name_en) {
            throw new Error(
                'Bilingual categories and origins are required for this test.'
            )
        }

        await page.goto('/admin/setting')
        await expect(
            page
                .getByTestId('category-settings')
                .getByText(category.name_en, { exact: true })
        ).toBeVisible()
        await expect(
            page
                .getByTestId('origin-settings')
                .getByText(origin.name_en, { exact: true })
        ).toBeVisible()

        await selectLanguage(page, 'zh')
        await expect(
            page
                .getByTestId('category-settings')
                .getByText(category.name, { exact: true })
        ).toBeVisible()
        await expect(
            page
                .getByTestId('origin-settings')
                .getByText(origin.name, { exact: true })
        ).toBeVisible()
        expect(runtimeErrors).toEqual([])
    } finally {
        if (settingsCaptured && !page.isClosed()) {
            await page.goto('/admin/setting')
            await selectLanguage(page, 'en')

            const shippingFeeInput = page.getByRole('spinbutton', {
                name: 'Shipping fee',
            })
            await shippingFeeInput.fill(originalFee)
            const shippingUpdate = waitForSettingUpdate(
                page,
                '/admin/setting/shipping'
            )
            await page.getByTestId('save-shipping-fee').click()
            await shippingUpdate

            const thresholdInput = page.getByRole('spinbutton', {
                name: 'Free-shipping order threshold',
            })
            const descriptionInput = page.getByRole('textbox', {
                name: 'Free-shipping note',
            })
            const freeShippingToggle = page.getByRole('switch', {
                name: 'Enable free shipping',
            })

            if (
                (await freeShippingToggle.isChecked()) !==
                originalFreeShippingEnabled
            ) {
                await freeShippingToggle.click()
            }
            await thresholdInput.fill(originalThreshold)
            await descriptionInput.fill(originalDescription)
            const freeShippingUpdate = waitForSettingUpdate(
                page,
                '/admin/setting/free-shipping'
            )
            await page.getByTestId('save-free-shipping').click()
            await freeShippingUpdate
            await selectLanguage(page, initialLanguage)
        }
    }
})
