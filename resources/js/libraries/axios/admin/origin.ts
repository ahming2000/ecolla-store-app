import adminAjax from '@/libraries/axios/common/admin-ajax'
import { index as originIndex } from '@/routes/admin/ajax/origin'
import type { Origin } from '@/types'

export const getAdminOrigins = async (): Promise<Origin[]> => {
    return await adminAjax.get<Origin[]>(originIndex.url())
}
