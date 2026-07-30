<script setup lang="ts">
import type { Image as AppImage } from '@/types'
import PrimeImage from 'primevue/image'
import { computed, ref, watch } from 'vue'

defineOptions({ inheritAttrs: false })

type ImageSourceKind = 'thumbnail' | 'full' | 'fallback'

interface ImageSource {
    src: string
    kind: ImageSourceKind
}

const props = withDefaults(
    defineProps<{
        image?: AppImage | null
        fallbackImage?: AppImage | null
        src?: string | null
        thumbnailSrc?: string | null
        fallbackSrc?: string | null
        alt?: string
        full?: boolean
        preview?: boolean
        loading?: 'eager' | 'lazy'
        wrapperClass?: string
        imageClass?: string
    }>(),
    {
        image: null,
        fallbackImage: null,
        src: null,
        thumbnailSrc: null,
        fallbackSrc: null,
        alt: '',
        full: false,
        preview: false,
        loading: 'lazy',
        wrapperClass: '',
        imageClass: '',
    }
)

const emit = defineEmits<{
    error: [event: Event]
}>()

const activeSourceIndex = ref(0)

const uniqueSources = (sources: ImageSource[]): ImageSource[] => {
    const seenSources = new Set<string>()

    return sources.filter((source) => {
        if (!source.src || seenSources.has(source.src)) {
            return false
        }

        seenSources.add(source.src)

        return true
    })
}

const sources = computed<ImageSource[]>(() => {
    const imageFullSource = props.image?.src
    const imageThumbnailSource = props.image?.thumbnail?.src
    const fallbackFullSource = props.fallbackImage?.src
    const fallbackThumbnailSource = props.fallbackImage?.thumbnail?.src

    if (props.full) {
        return uniqueSources([
            ...(imageFullSource
                ? [{ src: imageFullSource, kind: 'full' as const }]
                : []),
            ...(props.src ? [{ src: props.src, kind: 'full' as const }] : []),
            ...(fallbackFullSource
                ? [{ src: fallbackFullSource, kind: 'fallback' as const }]
                : []),
            ...(props.fallbackSrc
                ? [{ src: props.fallbackSrc, kind: 'fallback' as const }]
                : []),
        ])
    }

    return uniqueSources([
        ...(imageThumbnailSource
            ? [{ src: imageThumbnailSource, kind: 'thumbnail' as const }]
            : []),
        ...(imageFullSource
            ? [{ src: imageFullSource, kind: 'full' as const }]
            : []),
        ...(props.thumbnailSrc
            ? [{ src: props.thumbnailSrc, kind: 'thumbnail' as const }]
            : []),
        ...(props.src ? [{ src: props.src, kind: 'full' as const }] : []),
        ...(fallbackThumbnailSource
            ? [{ src: fallbackThumbnailSource, kind: 'thumbnail' as const }]
            : []),
        ...(fallbackFullSource
            ? [{ src: fallbackFullSource, kind: 'fallback' as const }]
            : []),
        ...(props.fallbackSrc
            ? [{ src: props.fallbackSrc, kind: 'fallback' as const }]
            : []),
    ])
})

const activeSource = computed(() => {
    return sources.value[activeSourceIndex.value] ?? null
})

const previewSource = computed(() => {
    return (
        props.image?.src ??
        props.src ??
        props.fallbackImage?.src ??
        props.fallbackSrc ??
        activeSource.value?.src ??
        null
    )
})

const onImageError = (event: Event, primeErrorCallback?: () => void): void => {
    if (activeSourceIndex.value < sources.value.length - 1) {
        activeSourceIndex.value += 1

        return
    }

    primeErrorCallback?.()
    emit('error', event)
}

watch(sources, () => {
    activeSourceIndex.value = 0
})
</script>

<template>
    <PrimeImage
        v-if="preview && previewSource"
        :class="wrapperClass"
        :src="previewSource"
        preview
    >
        <template #image="{ errorCallback }">
            <img
                v-bind="$attrs"
                :alt="alt"
                :class="imageClass"
                :data-smart-image-source="activeSource?.kind"
                decoding="async"
                :loading="loading"
                :src="activeSource?.src"
                @error="onImageError($event, errorCallback)"
            />
        </template>

        <template #original="{ class: previewClass, style, previewCallback }">
            <img
                :alt="alt"
                :class="previewClass"
                :src="previewSource"
                :style="style"
                @click="previewCallback"
            />
        </template>
    </PrimeImage>

    <img
        v-else
        v-bind="$attrs"
        :alt="alt"
        :class="imageClass"
        :data-smart-image-source="activeSource?.kind"
        decoding="async"
        :loading="loading"
        :src="activeSource?.src"
        @error="onImageError"
    />
</template>
