import shopAjax from '@/libraries/axios/common/shop-ajax'
import { index as categoryIndex } from '@/routes/shop/ajax/category'
import type { Category } from '@/types'

export const getShopCategories = async (): Promise<Category[]> => {
    return await shopAjax.get<Category[]>(categoryIndex.url())
}
