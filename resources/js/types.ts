import type { AccessLevel } from '@/enums/AccessLevel'

export type Identifier = number
export type Nullable<T> = T | null
export type SupportedLanguage = 'en' | 'zh'
export type ResettableItemCounter = 'view_count' | 'sold_count'

export interface PaginatedResponse<T> {
    current_page: number
    data: T[]
    last_page: number
    per_page: number
    total: number
}

export interface Timestamps {
    created_at: string
    updated_at: string
    deleted_at?: Nullable<string>
}

export interface User extends Timestamps {
    id: Identifier
    username: string
    lang: SupportedLanguage
    timezone: string
    access_level: AccessLevel
    is_enabled: boolean
}

export interface ImageFile extends Timestamps {
    id: Identifier
    name: string
    mime_type: string
    size: number
    url: Nullable<string>
    data_uri: Nullable<string>
    src: Nullable<string>
    variation_id?: Identifier
}

export interface ImageThumbnail extends ImageFile {
    thumbnail_id: null
}

export interface Image extends ImageFile {
    thumbnail_id: Nullable<Identifier>
    thumbnail?: Nullable<ImageThumbnail>
}

export interface Origin extends Timestamps {
    id: Identifier
    name: string
    name_en: string
    items_count?: number
}

export interface Category extends Timestamps {
    id: Identifier
    name: string
    name_en: string
    items_count?: number
}

export interface Variation extends Timestamps {
    id: Identifier
    barcode: string
    name: string
    name_en: string
    price: number
    sale_price: Nullable<number>
    weight: number
    stock: number
    image_id: Nullable<Identifier>
    item_id: Identifier
    image?: Nullable<Image>
    final_price: number
    price_text: string
    sale_price_text: string
    final_price_text: string
    weight_text?: string
}

export interface VariationFormData {
    barcode: string
    name: string
    name_en: string
    price: number
    sale_price: Nullable<number>
    weight: number
    stock: number
}

export interface Item extends Timestamps {
    id: Identifier
    name: string
    name_en: string
    slug: string
    desc: Nullable<string>
    is_listed: boolean
    view_count: number
    sold_count: number
    origin_id: Nullable<Identifier>
    origin?: Nullable<Origin>
    categories: Category[]
    variations: Variation[]
    images: Image[]
    all_images: Image[]
    cover_image: Nullable<string>
    cover_thumbnail: Nullable<string>
    total_stock: number
    total_image_count: number
}

export interface PaymentMethod extends Timestamps {
    id: Identifier
    name: string
    icon_img_path: Nullable<string>
    qr_code_img_path: Nullable<string>
    is_enabled: boolean
}

export interface OrderedItem extends Timestamps {
    id: Identifier
    name: string
    name_en: string
    barcode: string
    price: number
    sale_price: Nullable<number>
    quantity: number
}

export interface Order extends Timestamps {
    id: Identifier
    reference_num: string
    delivery_mode: string
    status: string
    tracking_no: Nullable<string>
    shipping_fee: number
    payment_method_id: Identifier
    receipt_image_id: Identifier
    note: Nullable<string>
    cus_name: string
    cus_phone: string
    cus_address: Nullable<string>
    created_at_display: string
    subtotal: string
    items: OrderedItem[]
    payment_method: PaymentMethod
    receipt_image: Image
}

export type OrderFulfilment = Pick<Order, 'id' | 'status' | 'tracking_no'>

export interface ShippingSettings {
    fee: number
    freeShipping: FreeShippingSettings
}

export interface FreeShippingSettings {
    isActivated: boolean
    threshold: number
    description: string
}

export interface CartData {
    deliveryMode: string
    items: CartItemData[]
}

export interface CartItemData {
    item: Item
    variation: Variation
    quantity: number
}

export interface AppPageProps {
    [key: string]: unknown

    auth: {
        user: Nullable<User>
    }
    csrf: string
    shop?: {
        freeShipping: FreeShippingSettings
    }
}

export interface SelectOption {
    label: string
    value: string
}

export interface MenuItem {
    label: string
    icon?: string
    badge?: number
    command?: () => void
    items?: MenuItem[]
    slot?: 'language-switcher'
}

export type FormErrors = Record<string, string>
