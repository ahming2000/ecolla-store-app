import { expect, type Page, test } from '@playwright/test'
import { collectRuntimeErrors } from './support/runtime-errors'

interface TestImage {
    id: number
    name: string
    mime_type: string
    size: number
    url: string
    data_uri: null
    thumbnail_id: number | null
    thumbnail?: TestImageThumbnail | null
    src: string
    created_at: string
    updated_at: string
}

interface TestImageThumbnail {
    id: number
    name: string
    mime_type: 'image/webp'
    size: number
    url: string
    data_uri: null
    thumbnail_id: null
    src: string
    created_at: string
    updated_at: string
}

interface TestVariation {
    id: number
    barcode: string
    name: string
    name_en: string
    price: number
    sale_price: number | null
    weight: number
    stock: number
    image_id: number | null
    item_id: number
    image: TestImage | null
    final_price: number
    price_text: string
    sale_price_text: string
    final_price_text: string
    weight_text: string
    created_at: string
    updated_at: string
}

const itemId = 999_997
const originalVariationId = 777
const createdVariationId = 778
const uploadedImageId = 779
const createdVariationImageId = 780
const timestamp = '2026-07-26T00:00:00.000000Z'
const uploadedImage: TestImage = {
    id: uploadedImageId,
    name: 'variation-photo.jpeg',
    mime_type: 'image/jpeg',
    size: 1024,
    url: '/images/ecolla-shop.jpeg',
    data_uri: null,
    thumbnail_id: uploadedImageId + 1000,
    thumbnail: {
        id: uploadedImageId + 1000,
        name: 'variation-photo-thumbnail.webp',
        mime_type: 'image/webp',
        size: 512,
        url: '/images/example-items/assorted-drinks.png',
        data_uri: null,
        thumbnail_id: null,
        src: '/images/example-items/assorted-drinks.png',
        created_at: timestamp,
        updated_at: timestamp,
    },
    src: '/images/ecolla-shop.jpeg',
    created_at: timestamp,
    updated_at: timestamp,
}
const createdVariationImage: TestImage = {
    ...uploadedImage,
    id: createdVariationImageId,
    name: 'created-variation-photo.jpeg',
}

const variation = (
    id: number,
    values: Partial<TestVariation> = {}
): TestVariation => {
    const price = values.price ?? 12.5
    const salePrice = values.sale_price ?? null

    return {
        id,
        barcode: values.barcode ?? `SKU-${id}`,
        name: values.name ?? '原规格',
        name_en: values.name_en ?? 'Original variation',
        price,
        sale_price: salePrice,
        weight: values.weight ?? 0.25,
        stock: values.stock ?? 8,
        image_id: null,
        item_id: itemId,
        image: null,
        final_price: salePrice ?? price,
        price_text: `RM ${price.toFixed(2)}`,
        sale_price_text: salePrice === null ? '' : `RM ${salePrice.toFixed(2)}`,
        final_price_text: `RM ${(salePrice ?? price).toFixed(2)}`,
        weight_text: `${(values.weight ?? 0.25).toFixed(3)} kg`,
        created_at: timestamp,
        updated_at: timestamp,
        ...values,
    }
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/)
}

test('does not display variation prices on an item card', async ({ page }) => {
    const runtimeErrors = collectRuntimeErrors(page)
    const expensiveVariation = variation(originalVariationId, {
        price: 18.9,
    })
    const cheapestVariation = variation(createdVariationId, {
        price: 12.5,
        sale_price: 9.9,
    })

    await login(page)

    await page.route(/\/ajax\/admin\/item(?:\?.*)?$/, async (route) => {
        if (route.request().method() === 'GET') {
            await route.fulfill({
                json: {
                    current_page: 1,
                    data: [
                        {
                            id: itemId,
                            name: '价格显示测试商品',
                            name_en: 'Price display test item',
                            desc: null,
                            is_listed: false,
                            view_count: 0,
                            sold_count: 0,
                            origin_id: null,
                            origin: null,
                            categories: [],
                            variations: [expensiveVariation, cheapestVariation],
                            images: [],
                            all_images: [],
                            cover_image: '/images/ecolla.png',
                            total_stock: 16,
                            total_image_count: 1,
                            created_at: timestamp,
                            updated_at: timestamp,
                        },
                    ],
                    last_page: 1,
                    per_page: 50,
                    total: 1,
                },
                status: 200,
            })

            return
        }

        await route.continue()
    })

    await page.goto('/admin/item')

    const itemCard = page.getByTestId(`item-card-${itemId}`)

    await expect(
        itemCard.getByText('价格显示测试商品', { exact: true })
    ).toBeVisible()
    await expect(
        itemCard.getByText(cheapestVariation.final_price_text, { exact: true })
    ).toHaveCount(0)
    await expect(
        itemCard.getByText(expensiveVariation.final_price_text, { exact: true })
    ).toHaveCount(0)
    await expect(itemCard.getByText(expensiveVariation.name)).toHaveCount(0)
    await expect(itemCard.getByText(cheapestVariation.name)).toHaveCount(0)

    expect(runtimeErrors).toEqual([])
})

test('isolates variation drafts and completes create, update, and delete', async ({
    page,
}) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)
    const variations = [variation(originalVariationId)]
    let updateRequestCount = 0
    let createRequestCount = 0
    let deleteRequestCount = 0
    let imageUploadRequestCount = 0
    let photoAttachRequestCount = 0
    const creationRequestSequence: string[] = []

    const item = {
        id: itemId,
        name: '规格功能测试商品',
        name_en: 'Variation workflow test',
        desc: null,
        is_listed: false,
        view_count: 0,
        sold_count: 0,
        origin_id: null,
        origin: null,
        categories: [],
        variations,
        images: [],
        all_images: [],
        cover_image: '/images/ecolla.png',
        total_stock: 8,
        total_image_count: 1,
        created_at: timestamp,
        updated_at: timestamp,
    }

    await login(page)

    await page.route(/\/ajax\/admin\/item(?:\?.*)?$/, async (route) => {
        if (route.request().method() === 'GET') {
            await route.fulfill({
                json: {
                    current_page: 1,
                    data: [item],
                    last_page: 1,
                    per_page: 50,
                    total: 1,
                },
                status: 200,
            })

            return
        }

        await route.continue()
    })

    await page.route(
        new RegExp(
            `/ajax/admin/item/${itemId}/variation(?:/${originalVariationId})?$`
        ),
        async (route) => {
            const method = route.request().method()

            if (method === 'PUT') {
                updateRequestCount++
                const requestData = route.request().postDataJSON() as {
                    barcode: string
                    name: string
                    name_en: string
                    price: number
                    sale_price: number | null
                    weight: number
                    stock: number
                }
                const updatedVariation = variation(originalVariationId, {
                    ...requestData,
                    updated_at: '2026-07-26T01:00:00.000000Z',
                })

                variations.splice(0, 1, updatedVariation)
                await route.fulfill({ json: updatedVariation, status: 200 })

                return
            }

            if (method === 'POST') {
                createRequestCount++
                creationRequestSequence.push('create')
                const requestData = route.request().postDataJSON() as {
                    barcode: string
                    name: string
                    name_en: string
                    price: number
                    sale_price: number | null
                    weight: number
                    stock: number
                }
                const createdVariation = variation(createdVariationId, {
                    ...requestData,
                })

                variations.push(createdVariation)
                await route.fulfill({ json: createdVariation, status: 201 })

                return
            }

            if (method === 'DELETE') {
                deleteRequestCount++
                variations.splice(
                    variations.findIndex(
                        (currentVariation) =>
                            currentVariation.id === originalVariationId
                    ),
                    1
                )
                await route.fulfill({ status: 204 })

                return
            }

            await route.continue()
        }
    )

    await page.route(
        `**/ajax/admin/item/${itemId}/variation/${createdVariationId}`,
        async (route) => {
            if (route.request().method() === 'DELETE') {
                deleteRequestCount++
                variations.splice(
                    variations.findIndex(
                        (currentVariation) =>
                            currentVariation.id === createdVariationId
                    ),
                    1
                )
                await route.fulfill({ status: 204 })

                return
            }

            await route.continue()
        }
    )

    await page.route('**/ajax/image/upload', async (route) => {
        if (route.request().method() === 'POST') {
            imageUploadRequestCount++

            if (imageUploadRequestCount === 2) {
                creationRequestSequence.push('upload')
            }

            await route.fulfill({
                json:
                    imageUploadRequestCount === 1
                        ? uploadedImage
                        : createdVariationImage,
                status: 200,
            })

            return
        }

        await route.continue()
    })

    await page.route(
        `**/ajax/admin/item/${itemId}/variation/${originalVariationId}/image`,
        async (route) => {
            if (route.request().method() === 'POST') {
                photoAttachRequestCount++
                expect(route.request().postDataJSON()).toEqual({
                    image_id: uploadedImageId,
                })

                const updatedVariation = variation(originalVariationId, {
                    ...variations[0],
                    image_id: uploadedImageId,
                    image: uploadedImage,
                })

                variations.splice(0, 1, updatedVariation)
                await route.fulfill({ json: updatedVariation, status: 200 })

                return
            }

            await route.continue()
        }
    )

    await page.route(
        `**/ajax/admin/item/${itemId}/variation/${createdVariationId}/image`,
        async (route) => {
            if (route.request().method() === 'POST') {
                photoAttachRequestCount++
                creationRequestSequence.push('attach')
                expect(route.request().postDataJSON()).toEqual({
                    image_id: createdVariationImageId,
                })

                const createdVariationIndex = variations.findIndex(
                    (currentVariation) =>
                        currentVariation.id === createdVariationId
                )
                const updatedVariation = variation(createdVariationId, {
                    ...variations[createdVariationIndex],
                    image_id: createdVariationImageId,
                    image: createdVariationImage,
                })

                variations.splice(createdVariationIndex, 1, updatedVariation)
                await route.fulfill({ json: updatedVariation, status: 200 })

                return
            }

            await route.continue()
        }
    )

    await page.goto('/admin/item')

    const itemCard = page.getByTestId(`item-card-${itemId}`)

    await itemCard.getByRole('button', { name: '编辑', exact: true }).click()
    await page.getByRole('tab', { name: '规格', exact: true }).click()

    const variationList = page.getByTestId(`variation-list-${itemId}`)
    const addVariationButton = page.getByTestId(`add-variation-${itemId}`)

    await expect
        .poll(async () => {
            const variationListBox = await variationList.boundingBox()
            const addVariationButtonBox = await addVariationButton.boundingBox()

            return Math.abs(
                (variationListBox?.width ?? Infinity) -
                    (addVariationButtonBox?.width ?? -Infinity)
            )
        })
        .toBeLessThanOrEqual(1)

    await expect(
        variationList.getByText('原规格', { exact: true })
    ).toBeVisible()
    await page.getByTestId(`edit-variation-${originalVariationId}`).click()

    const editDialog = page.getByRole('dialog', { name: '编辑规格' })
    const saveButton = page.getByTestId(`save-variation-${originalVariationId}`)
    const photoEditor = editDialog.getByTestId(
        `variation-photo-editor-${originalVariationId}`
    )
    const barcodeInput = editDialog.locator(
        `#variation-${originalVariationId}-barcode`
    )

    await expect(photoEditor).toBeVisible()

    const photoEditorBox = await photoEditor.boundingBox()
    const barcodeInputBox = await barcodeInput.boundingBox()

    expect(photoEditorBox).not.toBeNull()
    expect(barcodeInputBox).not.toBeNull()
    expect(photoEditorBox?.y ?? Infinity).toBeLessThan(
        barcodeInputBox?.y ?? -Infinity
    )

    const fileChooserPromise = page.waitForEvent('filechooser')

    await page
        .getByTestId(`upload-variation-photo-${originalVariationId}`)
        .click()
    const fileChooser = await fileChooserPromise

    await fileChooser.setFiles(
        'public/images/example-items/assorted-drinks.png'
    )
    await expect(photoEditor.locator('img')).toHaveAttribute('src', /^blob:/)
    expect(imageUploadRequestCount).toBe(0)
    expect(photoAttachRequestCount).toBe(0)

    await editDialog
        .locator(`#variation-${originalVariationId}-name`)
        .fill('编辑后的规格')
    await expect(
        variationList.getByText('原规格', { exact: true })
    ).toBeVisible()

    await editDialog
        .locator(`#variation-${originalVariationId}-barcode`)
        .fill('')
    await expect(saveButton).toBeDisabled()
    await expect(
        variationList.getByText('原规格', { exact: true })
    ).toBeVisible()

    await editDialog
        .locator(`#variation-${originalVariationId}-barcode`)
        .fill('SKU-UPDATED')
    await saveButton.click()

    await expect(editDialog).toBeHidden()
    await expect(
        variationList.getByText('编辑后的规格', { exact: true })
    ).toBeVisible()
    await expect(page.getByText('规格更新成功！')).toBeVisible()
    expect(updateRequestCount).toBe(1)
    expect(imageUploadRequestCount).toBe(1)
    expect(photoAttachRequestCount).toBe(1)
    await expect(variationList.locator('img').first()).toHaveAttribute(
        'src',
        uploadedImage.thumbnail?.src ?? ''
    )
    await variationList.locator('.p-image-preview-mask').first().click()
    await expect(page.locator('.p-image-mask img')).toHaveAttribute(
        'src',
        uploadedImage.src
    )
    await page.keyboard.press('Escape')

    await page.getByTestId(`add-variation-${itemId}`).click()

    const createDialog = page.getByRole('dialog', { name: '创建规格' })
    const createButton = page.getByTestId(`create-variation-${itemId}`)
    const createPhotoEditor = createDialog.getByTestId(
        `variation-photo-editor-new-${itemId}`
    )

    await expect(createButton).toBeDisabled()
    await expect(createPhotoEditor).toBeVisible()

    const createFileChooserPromise = page.waitForEvent('filechooser')

    await createDialog
        .getByTestId(`upload-variation-photo-new-${itemId}`)
        .click()
    const createFileChooser = await createFileChooserPromise

    await createFileChooser.setFiles(
        'public/images/example-items/assorted-drinks.png'
    )
    await expect(createPhotoEditor.locator('img')).toHaveAttribute(
        'src',
        /^blob:/
    )
    expect(imageUploadRequestCount).toBe(1)
    expect(photoAttachRequestCount).toBe(1)

    await createDialog
        .locator(`#variation-new-${itemId}-barcode`)
        .fill('SKU-CREATED')
    await createDialog.locator(`#variation-new-${itemId}-name`).fill('新规格')
    await createDialog
        .locator(`#variation-new-${itemId}-name-en`)
        .fill('New variation')
    await createDialog.locator(`#variation-new-${itemId}-stock`).fill('5')
    await expect(createButton).toBeEnabled()
    await createButton.click()

    await expect(createDialog).toBeHidden()
    await expect(
        variationList.getByText('新规格', { exact: true })
    ).toBeVisible()
    await expect(page.getByText('规格创建成功！')).toBeVisible()
    expect(createRequestCount).toBe(1)
    expect(imageUploadRequestCount).toBe(2)
    expect(photoAttachRequestCount).toBe(2)
    expect(creationRequestSequence).toEqual(['create', 'upload', 'attach'])
    await expect(
        variationList.locator(`img[alt="${createdVariationImage.name}"]`)
    ).toBeVisible()

    await page.getByTestId(`delete-variation-${createdVariationId}`).click()

    const deleteDialog = page.getByRole('dialog', { name: '删除规格' })

    await expect(
        deleteDialog.getByText('确定要删除“新规格”吗？此操作无法撤销。')
    ).toBeVisible()
    await page
        .getByTestId(`confirm-delete-variation-${createdVariationId}`)
        .click()

    await expect(deleteDialog).toBeHidden()
    await expect(
        variationList.getByText('新规格', { exact: true })
    ).toHaveCount(0)
    await expect(page.getByText('规格删除成功！')).toBeVisible()
    expect(deleteRequestCount).toBe(1)

    await page.getByTestId(`delete-variation-${originalVariationId}`).click()
    await page
        .getByTestId(`confirm-delete-variation-${originalVariationId}`)
        .click()

    await expect(
        variationList.getByText('编辑后的规格', { exact: true })
    ).toHaveCount(0)
    await expect(page.getByTestId(`add-variation-${itemId}`)).toBeVisible()
    expect(deleteRequestCount).toBe(2)
    expect(runtimeErrors).toEqual([])
})
