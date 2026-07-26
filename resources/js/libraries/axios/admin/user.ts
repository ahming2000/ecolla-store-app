import type { AccessLevel } from '@/enums/AccessLevel'
import adminAjax from '@/libraries/axios/common/admin-ajax'
import {
    create as createUserRoute,
    deactivate as deactivateUserRoute,
    destroy as destroyUserRoute,
    reactivate as reactivateUserRoute,
    update as updateUserRoute,
} from '@/routes/admin/ajax/user'
import type { User } from '@/types'

export interface UserFormData {
    username: string
    password: string
    password_confirmation: string
    access_level: AccessLevel
}

export const createUser = async (data: UserFormData): Promise<User> => {
    return await adminAjax.post<User>(createUserRoute.url(), data)
}

export const updateUser = async (
    data: UserFormData,
    id: number
): Promise<User> => {
    return await adminAjax.put<User>(updateUserRoute.url(id), data)
}

export const deleteUser = async (id: number): Promise<void> => {
    return await adminAjax.delete<void>(destroyUserRoute.url(id))
}

export const deactivateUser = async (id: number): Promise<User> => {
    return await adminAjax.patch<User>(deactivateUserRoute.url(id))
}

export const reactivateUser = async (id: number): Promise<User> => {
    return await adminAjax.patch<User>(reactivateUserRoute.url(id))
}
