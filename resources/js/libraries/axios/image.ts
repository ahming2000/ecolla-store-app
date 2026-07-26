import type { ImageUploadOption } from '@/enums/ImageUploadOption'
import ajax from '@/libraries/axios/common/ajax'
import { upload as uploadImageRoute } from '@/routes/image'
import type { Image } from '@/types'

export const uploadImage = async (
    file: File,
    option: ImageUploadOption
): Promise<Image> => {
    const formData = new FormData()

    formData.append('image', file, file.name)
    formData.append('option', option)

    return await ajax.post<Image>(uploadImageRoute.url(), formData)
}
