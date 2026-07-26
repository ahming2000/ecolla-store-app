import type { CartItemData, Item, Variation } from '@/types'

export default class CartItem implements CartItemData {
    constructor(
        public item: Item,
        public variation: Variation,
        public quantity: number
    ) {}

    static fromArray(cartItems: CartItemData[] = []): CartItem[] {
        return cartItems?.map((cartItem) => {
            return new CartItem(
                cartItem.item,
                cartItem.variation,
                cartItem.quantity
            )
        })
    }

    getTotalFinalPrice(): number {
        return this.variation.final_price * this.quantity
    }

    getTotalFinalPriceText(): string {
        return `RM ${this.getTotalFinalPrice().toFixed(2)}`
    }

    getTotalWeight(): number {
        return this.variation.weight * this.quantity
    }

    getTotalWeightText(): string {
        return `${this.getTotalWeight().toFixed(3)} kg`
    }
}
