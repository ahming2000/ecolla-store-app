export default {
    brand: 'Ecolla e口乐',
    address: {
        line1: '2365, Jalan Hala Timah 3',
        line2: 'Taman Bandar Baru',
        line3: '31900 Kampar, Perak',
    },
    'item-list': {
        empty: 'No items',
        title: 'Items',
    },
    item: {
        breadcrumbs: {
            list: 'Items',
            origin: 'Made in {name}',
        },
        'sold-out': 'Sold out',
    },
    cart: {
        title: 'Cart',
        'item-count': 'Cart ({count} items)',
        clear: 'Clear',
        'order-mode': 'Order mode',
        empty: {
            alt: 'Empty cart',
            description: 'Your cart is empty',
        },
        item: {
            'image-alt': '{name} image',
            remove: 'Remove {name} from the cart',
        },
        summary: {
            checkout: 'Proceed to checkout',
            shipping: 'Shipping fee',
            subtotal: 'Subtotal',
            title: 'Order summary',
            total: 'Total',
        },
        contact: {
            title: 'Contact us if you have any questions!',
            'phone-copied': 'Phone number copied.',
            facebook: 'Ecolla official Facebook',
            whatsapp: 'WhatsApp customer service',
        },
        delivery: {
            title: 'Delivery',
            'cash-on-delivery': 'Cash on delivery is not available.',
            'within-distance': 'Delivery is available within 5 km of the shop.',
            'outside-distance':
                'Delivery beyond 5 km is currently unavailable.',
            fee: 'Delivery fee: {amount}',
            schedule: 'Delivery schedule:',
            'before-three': 'Orders placed before 3 PM: 3 PM–4 PM',
            'after-three': 'Orders placed after 3 PM: 7 PM–8 PM',
        },
        pickup: {
            title: 'Self pickup',
            description:
                'Enter your phone number so you can collect your items at the shop.',
        },
    },
    checkout: {
        title: 'Checkout',
        'items-title': 'Items in your cart',
        shipping: 'Shipping fee',
        total: 'Total',
        'form-title': 'Details ({mode})',
        fields: {
            address: 'Address',
            name: 'Name',
            phone: 'Phone number',
        },
        placeholders: {
            address: '10, Jalan Kampar',
            name: 'John',
            phone: '0121234567',
        },
        submit: 'Submit order',
        payment: {
            select: 'Select a payment method',
            'show-qr-code': 'Show QR code',
        },
        receipt: {
            prompt: 'Upload your receipt',
            upload: 'Upload receipt',
        },
        success: {
            title: 'Order placed',
            heading: 'Thank you for your order!',
            reference: 'Your order reference is {reference}.',
        },
    },
    'payment-methods': {
        title: 'Payment methods',
        description: 'These are the payment methods we accept.',
        'view-qr-code': 'View QR code',
    },
} as const
