import type { SupportedLanguage } from '@/types'

export const DEFAULT_LANGUAGE: SupportedLanguage = 'zh'
export const ADMIN_LANGUAGE_STORAGE_KEY = 'admin.lang'
export const SHOP_LANGUAGE_STORAGE_KEY = 'shop.lang'

interface LocalizedName {
    name: string
    name_en?: string | null
}

export const getLocalizedName = (
    value: LocalizedName,
    language: string
): string => {
    return language === 'en' && value.name_en ? value.name_en : value.name
}

export const getAlternateLocalizedName = (
    value: LocalizedName,
    language: string
): string => {
    return language === 'en' ? value.name : (value.name_en ?? value.name)
}

export const isSupportedLanguage = (
    value: unknown
): value is SupportedLanguage => {
    return value === 'en' || value === 'zh'
}

export const getStoredLanguage = (key: string): SupportedLanguage | null => {
    try {
        const language = window.localStorage.getItem(key)

        return isSupportedLanguage(language) ? language : null
    } catch {
        return null
    }
}

export const storeLanguage = (
    key: string,
    language: SupportedLanguage
): void => {
    try {
        window.localStorage.setItem(key, language)
    } catch {
        return
    }
}
