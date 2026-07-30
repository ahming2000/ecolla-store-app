const MAX_REQUEST_IMAGE_SIZE = 1_700_000
const MAX_IMAGE_EDGE_LENGTH = 2400
const MIN_IMAGE_QUALITY = 0.55

const canvasToBlob = async (
    canvas: HTMLCanvasElement,
    mimeType: string,
    quality: number
): Promise<Blob> => {
    return await new Promise<Blob>((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (blob) {
                    resolve(blob)

                    return
                }

                reject(new Error('Unable to prepare the selected image.'))
            },
            mimeType,
            quality
        )
    })
}

const preparedFileName = (fileName: string, mimeType: string): string => {
    const baseName = fileName.replace(/\.[^.]+$/, '')
    const extensionByMimeType: Record<string, string> = {
        'image/jpeg': 'jpg',
        'image/png': 'png',
        'image/webp': 'webp',
    }
    const extension = extensionByMimeType[mimeType] ?? 'webp'

    return `${baseName}.${extension}`
}

export const prepareImageForUpload = async (file: File): Promise<File> => {
    if (file.size <= MAX_REQUEST_IMAGE_SIZE) {
        return file
    }

    if (file.type === 'image/gif') {
        throw new Error('Animated images cannot be optimized safely.')
    }

    const image = await createImageBitmap(file)
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')

    if (!context) {
        image.close()

        throw new Error('Unable to prepare the selected image.')
    }

    const outputMimeType = ['image/jpeg', 'image/png', 'image/webp'].includes(
        file.type
    )
        ? file.type
        : 'image/webp'
    let scale = Math.min(
        1,
        MAX_IMAGE_EDGE_LENGTH / Math.max(image.width, image.height)
    )
    let quality = 0.88

    try {
        for (let attempt = 0; attempt < 8; attempt += 1) {
            canvas.width = Math.max(1, Math.round(image.width * scale))
            canvas.height = Math.max(1, Math.round(image.height * scale))
            context.clearRect(0, 0, canvas.width, canvas.height)
            context.drawImage(image, 0, 0, canvas.width, canvas.height)

            const blob = await canvasToBlob(canvas, outputMimeType, quality)

            if (blob.size <= MAX_REQUEST_IMAGE_SIZE) {
                return new File(
                    [blob],
                    preparedFileName(file.name, outputMimeType),
                    {
                        type: outputMimeType,
                        lastModified: file.lastModified,
                    }
                )
            }

            if (quality > MIN_IMAGE_QUALITY) {
                quality = Math.max(MIN_IMAGE_QUALITY, quality - 0.1)
            } else {
                scale *= 0.8
            }
        }
    } finally {
        image.close()
    }

    throw new Error('Unable to reduce the selected image upload size.')
}
