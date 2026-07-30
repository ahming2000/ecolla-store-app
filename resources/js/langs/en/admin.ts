export default {
    layout: {
        brand: 'Ecolla Admin',
        navigation: {
            'change-log': 'Change log',
            dashboard: 'Dashboard',
            items: 'Items',
            login: 'Login',
            logout: 'Log out',
            orders: 'Orders',
            profile: 'Profile',
            settings: 'Website settings',
            shop: 'Go to shop',
            users: 'Staff accounts',
            wiki: 'Help & wiki',
        },
    },
    auth: {
        login: {
            password: 'Password',
            remember: 'Keep me signed in',
            title: 'Login',
            username: 'Account ID',
        },
    },
    'change-log': {
        'current-version': 'Current version',
        title: 'Change log',
        'updated-at': 'Updated on: {date}',
        'version-date': '{name} ({date})',
    },
    wiki: {
        'clear-search': 'Clear search',
        contents: 'On this page',
        description:
            'A visual guide to every day-to-day admin area, from sales reporting and catalogue maintenance to orders, staff access, and site settings.',
        eyebrow: 'Ecolla operations guide',
        'guide-number': 'Guide {number}',
        'no-results': {
            description:
                'Try a broader term such as item, order, staff, or settings.',
            title: 'No guides match your search',
        },
        'open-module': 'Open this area',
        'open-screenshot': 'Open the {title} screenshot',
        'result-count': 'Showing {visible} of {total} guides',
        'screenshot-alt': '{title} admin screen',
        'screenshot-title': '{title} screenshot',
        'search-label': 'Find a guide',
        'search-placeholder': 'Search features, actions, or keywords',
        title: 'Admin handbook',
        'view-screenshot': 'View full screenshot',
        categories: {
            account: 'Account',
            administration: 'Administration',
            catalog: 'Catalogue',
            insights: 'Insights',
            operations: 'Operations',
            reference: 'Reference',
        },
        articles: {
            dashboard: {
                access: 'Available to every signed-in staff member.',
                caption:
                    'The reporting period and four sales metrics appear together on the dashboard.',
                points: {
                    1: 'Choose daily, weekly, monthly, or yearly reporting, then use the matching date control to move to the period you need.',
                    2: 'Read completed orders, items sold, sales revenue, and canceled order value from the summary cards.',
                    3: 'Changing a filter refreshes only the report, so the current dashboard stays in place while the figures update.',
                },
                summary:
                    'Use the dashboard for a quick, consistent view of completed sales and canceled merchandise value across the selected period.',
                title: 'Sales dashboard',
            },
            catalog: {
                access: 'Viewers can browse. Editors and supervisors receive additional item actions.',
                caption:
                    'Search, filters, sorting, listing state, and item cards are available from one catalogue view.',
                points: {
                    1: 'Search by product name, SKU, variation, origin, or description, then narrow the list by category.',
                    2: 'Use stock and listing filters to find products that need attention, and change the arrangement when reviewing a large catalogue.',
                    3: 'Create a product from the page header, or open an existing card to edit details and control whether it appears in the shop.',
                },
                summary:
                    'The item catalogue is the main workspace for finding products, checking stock and visibility, and starting item maintenance.',
                title: 'Item catalogue',
            },
            'item-editor': {
                access: 'Editors can maintain items; destructive actions require supervisor-level access.',
                caption:
                    'The item dialog groups basic information, variations, images, and counters into focused tabs.',
                points: {
                    1: 'Complete the Chinese and English names, description, origin, and categories under Basic information.',
                    2: 'Maintain each variation’s SKU, bilingual names, normal and sale prices, weight, stock, and assigned image.',
                    3: 'Upload product photos in Images, review or reset counters under Other, and list the product only after all required details are complete.',
                },
                summary:
                    'Open an item to maintain everything the storefront needs, including bilingual content, variations, photos, publishing state, and counters.',
                title: 'Item details & publishing',
            },
            orders: {
                access: 'All staff can view orders; available updates follow the account access level.',
                caption:
                    'The order table brings references, fulfilment mode, payment, totals, and status together.',
                points: {
                    1: 'Filter orders by date and delivery mode to focus on the fulfilment queue you are working on.',
                    2: 'Open an order to review customer contact details, delivery information, purchased items, notes, and the uploaded receipt.',
                    3: 'Use the permitted controls to move the order through its status and record a delivery tracking ID when required.',
                },
                summary:
                    'Use Orders to review incoming purchases, verify payment information, and follow each delivery or self-pickup through fulfilment.',
                title: 'Order fulfilment',
            },
            staff: {
                access: 'Only administrators can open staff account management.',
                caption:
                    'Staff accounts show the account ID and provide access to the account editor.',
                points: {
                    1: 'Add a staff account with an account ID, password, password confirmation, and the least-privileged suitable access level.',
                    2: 'Edit an existing account when its credentials or responsibilities change.',
                    3: 'Use Viewer for read-only work, Editor for day-to-day item and order work, and Supervisor for broader item and settings control.',
                },
                summary:
                    'Administrators use this area to create and maintain staff sign-ins and match each person to the right operational permissions.',
                title: 'Staff accounts & access',
            },
            settings: {
                access: 'Everyone can view settings; updates require the access granted to the account.',
                caption:
                    'Origins, categories, shipping fees, and free-shipping rules are grouped on the settings page.',
                points: {
                    1: 'Maintain bilingual origins and categories so products can be organised and filtered consistently in the shop.',
                    2: 'Review the base shipping fee used during checkout.',
                    3: 'Configure the free-shipping switch, order threshold, and customer-facing note when those controls are enabled.',
                },
                summary:
                    'Website settings hold the catalogue labels and checkout rules that are shared across the storefront.',
                title: 'Website settings',
            },
            profile: {
                access: 'Every signed-in staff member has a personal profile.',
                caption:
                    'The profile keeps the current account ID and password controls in one place.',
                points: {
                    1: 'Confirm the account ID currently in use; contact an administrator if it needs to be changed.',
                    2: 'Use the password section when password updates are enabled for your deployment.',
                    3: 'Switch between English and Chinese from the navigation language control; the choice follows the signed-in account.',
                },
                summary:
                    'The profile identifies the signed-in account and provides the staff member’s personal security and language controls.',
                title: 'Profile & language',
            },
            'change-log': {
                access: 'The change log is available to signed-in staff and visitors to the admin login area.',
                caption:
                    'Release groups expand to show dated feature, improvement, and fix notes.',
                points: {
                    1: 'Check the version label and update date at the top of the page before reviewing recent changes.',
                    2: 'Expand a release group to read its dated features, improvements, and fixes.',
                    3: 'Use the language control to read the complete release notes in English or Chinese.',
                },
                summary:
                    'The change log explains what changed between releases and gives staff a quick record of newly available behaviour.',
                title: 'Change log',
            },
        },
    },
    dashboard: {
        description:
            'Review completed sales and canceled order value for a selected period.',
        'fallback-user': 'Admin',
        filters: {
            'date-formats': {
                daily: 'yy-mm-dd',
                monthly: 'yy-mm',
                weekly: 'yy-mm-dd',
                yearly: 'yy',
            },
            'date-labels': {
                daily: 'Date',
                monthly: 'Month',
                weekly: 'Week containing',
                yearly: 'Year',
            },
            description:
                'Choose a reporting interval and the date it should contain.',
            period: 'Reporting interval',
            title: 'Sales period',
        },
        'latest-order': 'Latest order',
        'most-clicked-today': "Today's most clicked items",
        'no-notifications': 'No notifications',
        periods: {
            daily: 'Daily',
            monthly: 'Monthly',
            weekly: 'Weekly',
            yearly: 'Yearly',
        },
        summary: {
            'canceled-order-value': {
                description:
                    'Merchandise value in canceled or refunded orders, excluding shipping.',
                label: 'Canceled or refunded order value',
            },
            'completed-orders': {
                description: 'Orders marked as completed.',
                label: 'Completed orders',
            },
            'items-sold': {
                description: 'Total units in completed orders.',
                label: 'Items sold',
            },
            'sales-revenue': {
                description:
                    'Merchandise value in completed orders, excluding shipping.',
                label: 'Sales revenue',
            },
            title: 'Sales summary',
        },
        title: 'Dashboard',
        visualizations: {
            distributions: {
                'delivery-description':
                    'Compare how customers receive orders in the selected period.',
                'delivery-modes': {
                    delivery: 'Delivery',
                    'self-pickup': 'Self pickup',
                },
                'delivery-title': 'Delivery modes',
                'section-description':
                    'See the operational mix behind the sales totals.',
                'section-title': 'Order composition',
                'status-description':
                    'Compare every order status, including work still in progress.',
                'status-title': 'Order statuses',
                statuses: {
                    canceled: 'Canceled',
                    completed: 'Completed',
                    pending: 'Pending',
                    ready: 'Ready',
                    refunded: 'Refunded',
                },
            },
            empty: 'No orders were recorded in this period.',
            trend: {
                'bar-label': '{label}: {revenue}; completed orders: {orders}.',
                'completed-orders': 'Completed: {count}',
                'data-table': 'View trend data table',
                description:
                    'Compare completed-order merchandise revenue across the selected period.',
                empty: 'No completed-order sales were recorded in this period.',
                orders: 'Completed orders',
                period: 'Period',
                revenue: 'Sales revenue',
                'table-caption':
                    'Completed order count and sales revenue by period',
                title: 'Sales trend',
            },
        },
        welcome: 'Welcome back, {username}!',
    },
    profile: {
        'account-id': 'Account ID',
        'account-id-description': 'Your account ID is',
        'account-id-help':
            '**Contact a website administrator to change your account ID.',
        'change-password': 'Change password',
        'confirm-password': 'Confirm password',
        'new-password': 'New password',
        'old-password': 'Current password',
        'password-update-failed':
            'The password could not be updated. Check the form and try again.',
        'password-updated-success': 'Password updated successfully.',
        title: 'Profile',
    },
    users: {
        'access-level': 'Access level',
        'access-level-details': 'Access level details',
        active: 'Active',
        'add-account': 'Add staff account',
        'confirm-password': 'Confirm password',
        'create-account': 'Create account',
        'created-success': 'User created successfully.',
        'deactivate-account': 'Deactivate “{username}”',
        'deactivate-confirmation':
            'Deactivate “{username}”? They will be signed out and will no longer be able to sign in.',
        'deactivate-failed': 'The account could not be deactivated.',
        'deactivate-title': 'Deactivate staff account',
        deactivated: 'Deactivated',
        'deactivated-success': 'User deactivated successfully.',
        'delete-account': 'Delete “{username}”',
        'delete-confirmation':
            'Delete “{username}”? The account will be removed, and its account ID can be reused.',
        'delete-failed': 'The account could not be deleted.',
        'delete-title': 'Delete staff account',
        'deleted-success': 'User deleted successfully.',
        edit: 'Edit',
        'edit-account': 'Edit “{username}”',
        'editor-details':
            'Can view, create, and update items; view orders; update processing order statuses and delivery tracking IDs; and view all website settings.',
        password: 'Password',
        'reactivate-account': 'Reactivate “{username}”',
        'reactivate-confirmation':
            'Reactivate “{username}”? They will be able to sign in again.',
        'reactivate-failed': 'The account could not be reactivated.',
        'reactivate-title': 'Reactivate staff account',
        'reactivated-success': 'User reactivated successfully.',
        'supervisor-details':
            'Can view, create, update, and delete items; view orders; update all order statuses and delivery tracking IDs; and view and update all website settings.',
        title: 'Staff account management',
        'updated-success': 'User updated successfully.',
        username: 'Account ID',
        'viewer-details': 'Can view all items, orders, and website settings.',
    },
    orders: {
        columns: {
            barcode: 'SKU',
            date: 'Date',
            'delivery-mode': 'Type',
            'item-count': 'Item quantity',
            name: 'Name',
            'payment-method': 'Payment method',
            price: 'Price (RM)',
            quantity: 'Quantity',
            reference: 'Reference',
            status: 'Status',
            total: 'Total (RM)',
        },
        'customer-address': 'Customer address',
        'customer-information': 'Customer information',
        'customer-name': 'Customer name',
        'customer-phone': 'Customer phone number',
        'date-filter': 'Order date',
        'date-format': 'yy-mm-dd',
        'delivery-mode': {
            delivery: 'Delivery',
            'self-pickup': 'Self pickup',
        },
        'delivery-tracking-id': 'Delivery tracking ID',
        'download-order': 'Download order',
        edit: {
            action: 'Edit order',
            'customer-and-order': 'Customer and order details',
            'empty-order-will-cancel':
                'This order will be canceled when you save because its final item was removed.',
            'items-subtotal': 'Items subtotal',
            'order-total': 'Order total',
            'price-help':
                'Enter the final price charged. A lower amount is saved as a sale price.',
            'remove-and-cancel': 'Remove item and cancel order',
            'remove-item': 'Remove {name} from the order',
            'remove-last-confirmation':
                'This is the final item. Remove it and change the order status to Canceled when you save?',
            'remove-last-title': 'Cancel order with no items?',
            'update-failed':
                'Unable to save the order changes. Check the form and try again.',
            'updated-success': 'Order changes saved.',
        },
        'item-details': 'Item details ({count})',
        note: 'Note',
        'order-details': 'Order details',
        'order-mode-filter': 'All order modes',
        'receipt-dialog-title': 'Receipt for {reference}',
        'receipt-image-alt': 'Receipt uploaded for order {reference}',
        'shipping-fee': 'Shipping fee',
        'shipping-information': 'Shipping information',
        'status-update-failed': 'Unable to update the order status.',
        'status-updated-success': 'Order status updated.',
        status: {
            canceled: 'Canceled',
            completed: 'Completed',
            pending: 'Processing',
            ready: 'Ready',
            refunded: 'Refunded',
        },
        title: 'Orders',
        'tracking-required':
            'Enter a delivery tracking ID before moving this order out of processing.',
        'tracking-update-failed': 'Unable to update the delivery tracking ID.',
        'tracking-updated-success': 'Delivery tracking ID updated.',
        'view-receipt': 'View receipt',
    },
    settings: {
        'add-category': 'Add category',
        'add-origin': 'Add origin',
        category: 'Category',
        'category-filter': 'Category filter',
        'catalog-created-success': '{subject} created successfully.',
        'catalog-delete-confirmation':
            'Delete {subject} “{name}”? This action cannot be undone.',
        'catalog-delete-failed': 'Unable to delete this {subject}.',
        'catalog-deleted-success': '{subject} deleted successfully.',
        'catalog-in-use':
            'This {subject} is assigned to {count} item(s) and cannot be deleted.',
        'catalog-save-failed': 'Unable to save this {subject}.',
        'catalog-updated-success': '{subject} updated successfully.',
        'create-subject': 'Create {subject}',
        'default-category-delete-disabled':
            'The default category cannot be deleted.',
        'delete-subject': 'Delete {subject}',
        'edit-subject': 'Edit {subject} “{name}”',
        'free-shipping-after': 'for free shipping',
        'free-shipping-description-input': 'Free-shipping note',
        'free-shipping-note': 'Free-shipping note',
        'free-shipping-threshold-input': 'Free-shipping order threshold',
        'free-shipping-toggle': 'Enable free shipping',
        'free-shipping-update-failed':
            'Unable to update the free-shipping settings.',
        'free-shipping-updated-success': 'Free-shipping settings updated.',
        'name-en': 'Name (English)',
        'name-zh': 'Name (Chinese)',
        note: 'Note',
        origin: 'Origin',
        'origin-filter': 'Origin filter',
        'over-amount': 'Over RM',
        price: 'Price',
        shipping: 'Shipping fee',
        'shipping-discount': 'Shipping discount',
        'shipping-fee-input': 'Shipping fee',
        'shipping-update-failed': 'Unable to update the shipping fee.',
        'shipping-updated-success': 'Shipping fee updated.',
        title: 'Website settings',
    },
    items: {
        'created-success': 'Item created successfully.',
        'create-item': 'Create item',
        'create-title': 'Create item',
        'delete-confirmation':
            'Are you sure you want to delete “{name}”? This action cannot be undone.',
        'delete-failed': 'Unable to delete the item.',
        'delete-item': 'Delete “{name}”',
        'delete-title': 'Delete item',
        'deleted-success': 'Item deleted successfully.',
        'edit-item': 'Edit item',
        'edit-named-item': 'Edit “{name}”',
        fields: {
            categories: 'Categories',
            description: 'Item details',
            name: 'Item name',
            'name-en': 'Item name (English)',
            origin: 'Origin',
            'origin-value': 'Made in {name}, {nameEn}',
        },
        images: {
            add: 'Add item image',
            'remove-confirmation':
                'Are you sure you want to remove “{name}”? This action cannot be undone.',
            'remove-failed': 'Unable to remove the item image.',
            'remove-image': 'Remove “{name}”',
            'remove-title': 'Remove item image',
            'removed-success': 'Item image removed successfully.',
            'upload-failed': 'Unable to add the uploaded image to the item.',
            'uploaded-success': 'Item image uploaded successfully.',
        },
        'list-item': 'List “{name}”',
        'listed-success': 'Item listed successfully.',
        'listing-requirements':
            'Before listing, complete both item names, the description, the origin, and at least one variation with a barcode and both names.',
        'listing-update-failed': 'Unable to update the listing status.',
        miscellaneous: {
            feature: 'Feature',
            'reset-action': 'Reset action',
            'reset-confirmation':
                'Are you sure you want to reset {counter} to 0?',
            'reset-counter': 'Reset {counter}',
            'reset-failed': 'Unable to reset {counter}.',
            'reset-success': '{counter} reset successfully.',
            'reset-title': 'Reset {counter}',
            'sold-count': 'Sales',
            value: 'Value',
            'view-count': 'Views',
        },
        'no-variations': 'No variations',
        tabs: {
            basic: 'Basic information',
            images: 'Images',
            miscellaneous: 'Other',
            variations: 'Variations',
        },
        title: 'Item management',
        'unlist-item': 'Unlist “{name}”',
        'unlisted-success': 'Item unlisted successfully.',
        'update-failed': 'Unable to update the item details.',
        'updated-success': 'Item details updated successfully.',
        variation: {
            'add-photo': 'Add photo',
            'add-variation': 'Add variation',
            barcode: 'SKU',
            'change-photo': 'Change photo',
            'create-failed': 'Unable to create the variation.',
            'create-title': 'Create variation',
            'created-success': 'Variation created successfully.',
            'delete-confirmation':
                'Are you sure you want to delete “{name}”? This action cannot be undone.',
            'delete-failed': 'Unable to delete the variation.',
            'delete-title': 'Delete variation',
            'delete-variation': 'Delete “{name}”',
            'deleted-success': 'Variation deleted successfully.',
            'edit-title': 'Edit variation',
            'edit-variation': 'Edit “{name}”',
            name: 'Name',
            'name-en': 'Name (English)',
            photo: 'Variation photo',
            'photo-upload-failed':
                'Unable to add the uploaded photo to the variation.',
            price: 'Price',
            'sale-price': 'Sale price',
            'save-requirements':
                'Enter both names and an SKU. Price must be at least RM0.01; sale price cannot exceed the regular price; weight and whole-number stock cannot be negative.',
            stock: 'Stock',
            'update-failed': 'Unable to update the variation.',
            'updated-success': 'Variation updated successfully.',
            weight: 'Weight',
        },
        variations: 'Variations:',
    },
} as const
