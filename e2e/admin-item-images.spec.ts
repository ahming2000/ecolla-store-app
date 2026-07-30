import {
    expect,
    type Locator,
    type Page,
    type Request,
    test,
} from '@playwright/test'
import { readFile } from 'node:fs/promises'
import path from 'node:path'
import { collectRuntimeErrors } from './support/runtime-errors'

interface AdminItem {
    id: number
    name: string
}

interface UploadedImage {
    id: number
    name: string
    thumbnail?: {
        mime_type: string
        src: string
    } | null
}

interface AdminItemState extends AdminItem {
    images: UploadedImage[]
    [key: string]: unknown
}

interface ViewportScenario {
    height: number
    name: string
    width: number
}

const viewportScenarios: ViewportScenario[] = [
    { name: 'compact phone', width: 360, height: 640 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'laptop', width: 1280, height: 720 },
    { name: 'desktop', width: 1536, height: 864 },
]

const expectedItemDialogSize = (
    viewport: ViewportScenario
): { height: number; width: number } => {
    if (viewport.width < 640) {
        return {
            height: viewport.height - 16,
            width: viewport.width - 16,
        }
    }

    return {
        height: Math.min(viewport.height * 0.88, 768),
        width:
            viewport.width < 1024
                ? viewport.width - 48
                : Math.min(viewport.width * 0.88, 1024),
    }
}

const expectDialogSize = async (
    dialog: Locator,
    expectedWidth: number,
    expectedHeight: number
): Promise<void> => {
    await expect
        .poll(async () => {
            return Math.round((await dialog.boundingBox())?.width ?? 0)
        })
        .toBe(Math.round(expectedWidth))
    await expect
        .poll(async () => {
            return Math.round((await dialog.boundingBox())?.height ?? 0)
        })
        .toBe(Math.round(expectedHeight))
}

const expectDialogInsideViewport = async (
    dialog: Locator,
    viewport: ViewportScenario
): Promise<void> => {
    const box = await dialog.boundingBox()

    expect(box, `${viewport.name} dialog bounds`).not.toBeNull()
    expect(box?.x ?? -1).toBeGreaterThanOrEqual(0)
    expect(box?.y ?? -1).toBeGreaterThanOrEqual(0)
    expect((box?.x ?? Infinity) + (box?.width ?? Infinity)).toBeLessThanOrEqual(
        viewport.width
    )
    expect(
        (box?.y ?? Infinity) + (box?.height ?? Infinity)
    ).toBeLessThanOrEqual(viewport.height)
}

const expectSameWidth = async (
    firstElement: Locator,
    secondElement: Locator
): Promise<void> => {
    await expect
        .poll(async () => {
            const firstBox = await firstElement.boundingBox()
            const secondBox = await secondElement.boundingBox()

            return Math.abs(
                (firstBox?.width ?? Infinity) - (secondBox?.width ?? -Infinity)
            )
        })
        .toBeLessThanOrEqual(1)
}

const login = async (page: Page): Promise<void> => {
    await page.goto('/admin/login')
    await page.getByLabel('账户 ID', { exact: true }).fill('admin')
    await page.getByLabel('密码', { exact: true }).fill('password')
    await page.getByRole('button', { name: '登录', exact: true }).click()
    await expect(page).toHaveURL(/\/admin$/, { timeout: 15_000 })
}

const ajaxHeaders = async (page: Page): Promise<Record<string, string>> => {
    const csrfCookie = (await page.context().cookies()).find(
        (cookie) => cookie.name === 'XSRF-TOKEN'
    )

    expect(csrfCookie).toBeDefined()

    return {
        Accept: 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(csrfCookie?.value ?? ''),
        'X-Requested-With': 'XMLHttpRequest',
    }
}

test('uploads and removes an item image in the edit dialog', async ({
    page,
}, testInfo) => {
    test.setTimeout(60_000)

    const runtimeErrors = collectRuntimeErrors(page)
    const uploadRequests: Request[] = []

    page.on('request', (request) => {
        if (
            request.url().endsWith('/ajax/image/upload') &&
            request.method() === 'POST'
        ) {
            uploadRequests.push(request)
        }
    })

    await login(page)

    const headers = await ajaxHeaders(page)
    const itemName = `Image management ${testInfo.project.name} ${Date.now()}`
    const createResponse = await page.request.post('/ajax/admin/item', {
        data: { name: itemName },
        headers,
    })

    expect(createResponse.ok()).toBeTruthy()

    const item = (await createResponse.json()) as AdminItem
    let uploadedImage: UploadedImage | null = null

    try {
        await page.goto('/admin/item')

        const itemCard = page.locator('.p-card').filter({
            has: page.getByText(itemName, { exact: true }),
        })

        await itemCard
            .getByRole('button', { name: '编辑', exact: true })
            .click()

        const itemDialog = page.getByTestId(`edit-item-dialog-${item.id}`)
        const itemTabs = page.getByTestId(`item-edit-tabs-${item.id}`)
        const itemTabList = itemTabs.getByRole('tablist')
        const itemTabPanels = page.getByTestId(
            `item-edit-tab-panels-${item.id}`
        )

        await expect(itemDialog).toBeVisible()

        for (const viewport of viewportScenarios) {
            await page.setViewportSize(viewport)

            const expectedSize = expectedItemDialogSize(viewport)

            await expectDialogSize(
                itemDialog,
                expectedSize.width,
                expectedSize.height
            )
            await expectDialogInsideViewport(itemDialog, viewport)
            await expectSameWidth(itemTabs, itemTabList)
            await expectSameWidth(itemTabs, itemTabPanels)

            if (
                testInfo.project.name === 'chromium' &&
                ['compact phone', 'desktop'].includes(viewport.name)
            ) {
                const screenshotPath = testInfo.outputPath(
                    `item-dialog-${viewport.name.replace(' ', '-')}.png`
                )

                await page.screenshot({ path: screenshotPath })
                await testInfo.attach(`item dialog - ${viewport.name}`, {
                    contentType: 'image/png',
                    path: screenshotPath,
                })
            }
        }

        await page.setViewportSize({ width: 1280, height: 720 })
        await page.getByRole('tab', { name: '照片', exact: true }).click()
        await page.getByTestId(`add-item-image-${item.id}`).click()

        const uploadDialog = page.getByTestId('image-uploader-dialog')
        const uploadPreviewArea = page.getByTestId('image-upload-preview-area')

        await expect(uploadDialog).toBeVisible()
        await expect(uploadPreviewArea).toBeVisible()
        await expectDialogSize(uploadDialog, 512, 576)

        const uploadDialogSizeBeforeUpload = await uploadDialog.boundingBox()
        const previewSizeBeforeUpload = await uploadPreviewArea.boundingBox()
        const uploadInput = uploadDialog.locator('input[type="file"]')
        const originalOption = uploadDialog.getByLabel('原图', { exact: true })
        const whiteEdgeOption = uploadDialog.getByLabel('白边', {
            exact: true,
        })
        const sampleImagePath = path.resolve(
            'public/images/example-items/assorted-drinks.png'
        )
        const sampleImage = await readFile(sampleImagePath)
        const oversizedSampleImage = Buffer.concat([
            sampleImage,
            Buffer.alloc(Math.max(0, 1_700_001 - sampleImage.length)),
        ])

        expect(oversizedSampleImage.length).toBeGreaterThan(1_700_000)

        await originalOption.check()
        await expect(originalOption).toBeChecked()
        await uploadInput.setInputFiles({
            buffer: oversizedSampleImage,
            mimeType: 'image/png',
            name: 'wide-product.png',
        })

        await expectDialogSize(uploadDialog, 512, 576)
        expect(await uploadDialog.boundingBox()).toEqual(
            uploadDialogSizeBeforeUpload
        )
        expect(await uploadPreviewArea.boundingBox()).toEqual(
            previewSizeBeforeUpload
        )

        const uploadedThumbnail = uploadPreviewArea.locator('img')
        const transformationPreview = uploadDialog.getByTestId(
            'image-transformation-preview'
        )

        await expect(uploadedThumbnail).toBeVisible()
        await expect(transformationPreview).toBeVisible()
        expect(uploadRequests).toHaveLength(0)

        const originalPreviewBox = await transformationPreview.boundingBox()

        expect(originalPreviewBox).not.toBeNull()
        expect(
            Math.abs(
                (originalPreviewBox?.width ?? 0) -
                    (originalPreviewBox?.height ?? 0)
            )
        ).toBeGreaterThan(1)

        await whiteEdgeOption.check()
        await expect(whiteEdgeOption).toBeChecked()
        await expect(originalOption).not.toBeChecked()

        await expect
            .poll(async () => {
                const box = await transformationPreview.boundingBox()

                return Math.abs((box?.width ?? 0) - (box?.height ?? 0))
            })
            .toBeLessThanOrEqual(1)

        expect(uploadRequests).toHaveLength(0)

        const uploadedThumbnailBox = await uploadedThumbnail.boundingBox()
        const uploadPreviewBox = await uploadPreviewArea.boundingBox()

        expect(uploadedThumbnailBox).not.toBeNull()
        expect(uploadPreviewBox).not.toBeNull()
        expect(uploadedThumbnailBox?.width ?? Infinity).toBeLessThanOrEqual(
            uploadPreviewBox?.width ?? 0
        )
        expect(uploadedThumbnailBox?.height ?? Infinity).toBeLessThanOrEqual(
            uploadPreviewBox?.height ?? 0
        )

        if (testInfo.project.name === 'chromium') {
            const screenshotPath = testInfo.outputPath(
                'image-upload-dialog.png'
            )

            await page.screenshot({ path: screenshotPath })
            await testInfo.attach('image upload dialog', {
                contentType: 'image/png',
                path: screenshotPath,
            })
        }

        const confirmUploadButton = page.getByTestId('confirm-image-upload')

        await expect(confirmUploadButton).toBeEnabled()

        const [uploadResponse, attachResponse] = await Promise.all([
            page.waitForResponse(
                (response) =>
                    response.url().endsWith('/ajax/image/upload') &&
                    response.request().method() === 'POST'
            ),
            page.waitForResponse(
                (response) =>
                    new URL(response.url()).pathname.match(
                        new RegExp(`^/ajax/admin/item/${item.id}/image/\\d+$`)
                    ) !== null && response.request().method() === 'POST'
            ),
            confirmUploadButton.click(),
        ])

        const uploadResponseBody = (await uploadResponse.json()) as
            UploadedImage | Record<string, unknown>

        expect(
            uploadResponse.ok(),
            JSON.stringify(uploadResponseBody)
        ).toBeTruthy()
        expect(uploadRequests).toHaveLength(1)

        uploadedImage = uploadResponseBody as UploadedImage

        expect(uploadedImage.thumbnail?.mime_type).toBe('image/webp')
        expect(attachResponse.ok()).toBeTruthy()
        const attachedItem = (await attachResponse.json()) as AdminItemState

        await expect(uploadDialog).toBeHidden()
        await expect(
            page.getByTestId(`item-image-${uploadedImage.id}`)
        ).toBeVisible()
        const storedImage = page
            .getByTestId(`item-image-${uploadedImage.id}`)
            .locator('img')

        await expect(storedImage).toHaveAttribute(
            'src',
            uploadedImage.thumbnail?.src ?? ''
        )
        await expect(storedImage).toHaveAttribute('loading', 'lazy')
        await expect
            .poll(async () => {
                return await storedImage.evaluate(
                    (image: HTMLImageElement) =>
                        image.naturalWidth - image.naturalHeight
                )
            })
            .toBe(0)
        await expect(page.getByText('商品照片上传成功！')).toBeVisible()

        await page.route(
            `**/ajax/admin/item/${item.id}/image/${uploadedImage.id}`,
            async (route) => {
                if (route.request().method() !== 'DELETE') {
                    await route.continue()

                    return
                }

                await route.fulfill({
                    contentType: 'application/json',
                    json: {
                        ...attachedItem,
                        images: [],
                    },
                    status: 200,
                })
            }
        )

        await page.getByTestId(`remove-item-image-${uploadedImage.id}`).click()

        const removeDialog = page.getByRole('dialog', {
            name: '删除商品照片',
        })

        await expect(removeDialog).toBeVisible()
        await expect(
            removeDialog.getByText(
                `确定要删除“${uploadedImage.name}”吗？此操作无法撤销。`
            )
        ).toBeVisible()

        const [removeResponse] = await Promise.all([
            page.waitForResponse(
                (response) =>
                    response
                        .url()
                        .endsWith(
                            `/ajax/admin/item/${item.id}/image/${uploadedImage?.id}`
                        ) && response.request().method() === 'DELETE'
            ),
            page.getByTestId('confirm-remove-item-image').click(),
        ])

        expect(removeResponse.ok()).toBeTruthy()

        await expect(removeDialog).toBeHidden()
        await expect(
            page.getByTestId(`item-image-${uploadedImage.id}`)
        ).toHaveCount(0)
        await expect(page.getByText('商品照片删除成功！')).toBeVisible()
        expect(runtimeErrors).toEqual([])
    } finally {
        if (uploadedImage) {
            await page.request.post(
                `/ajax/admin/item/${item.id}/image/${uploadedImage.id}`,
                { headers }
            )
            await page.request.delete(
                `/ajax/admin/item/${item.id}/image/${uploadedImage.id}`,
                { headers }
            )
        }

        const deleteItemResponse = await page.request.delete(
            `/ajax/admin/item/${item.id}`,
            { headers }
        )

        expect(deleteItemResponse.ok()).toBeTruthy()
    }
})
