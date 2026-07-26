import { router } from '@inertiajs/vue3'

type QueryParameterValue =
    string | number | null | undefined | readonly (string | number)[]

const queryBaseUrl = 'https://app.invalid'

export const getQueryParameters = (pageUrl: string): URLSearchParams => {
    return new URL(pageUrl, queryBaseUrl).searchParams
}

export const replaceQueryParameters = (
    pageUrl: string,
    parameters: Record<string, QueryParameterValue>
): void => {
    const currentUrl = new URL(pageUrl, queryBaseUrl)

    Object.entries(parameters).forEach(([key, value]) => {
        currentUrl.searchParams.delete(key)

        if (value === null || value === undefined || value === '') {
            return
        }

        const values = Array.isArray(value) ? value : [value]

        values.forEach((parameterValue) => {
            currentUrl.searchParams.append(key, String(parameterValue))
        })
    })

    const nextUrl = `${currentUrl.pathname}${currentUrl.search}${currentUrl.hash}`
    const currentRelativeUrl = new URL(pageUrl, queryBaseUrl)
    const currentUrlValue = `${currentRelativeUrl.pathname}${currentRelativeUrl.search}${currentRelativeUrl.hash}`

    if (nextUrl === currentUrlValue) {
        return
    }

    router.replace({
        url: nextUrl,
        preserveScroll: true,
        preserveState: true,
    })
}
