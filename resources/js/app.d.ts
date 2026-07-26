import '@inertiajs/core'

declare module '@inertiajs/core' {
    interface InertiaConfig {
        sharedPageProps: import('@/types').AppPageProps
    }
}

export {}
