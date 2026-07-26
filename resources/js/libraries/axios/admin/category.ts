import adminAjax from '@/libraries/axios/common/admin-ajax'
import { index as categoryIndex } from '@/routes/admin/ajax/category'
import type { Category } from '@/types'

export const getAdminCategories = async (): Promise<Category[]> => {
    return await adminAjax.get<Category[]>(categoryIndex.url())
}
