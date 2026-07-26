export default {
    actions: {
        add: 'Add',
        browse: 'Browse',
        cancel: 'Cancel',
        confirm: 'Confirm',
        contact: 'Contact us',
        create: 'Create',
        delete: 'Delete',
        details: 'Details',
        disable: 'Disable',
        download: 'Download',
        edit: 'Edit',
        enable: 'Enable',
        home: 'Home',
        reset: 'Reset',
        save: 'Save',
    },
    alt: {
        'item-image': 'Item image',
        logo: 'Ecolla logo',
        'no-image-thumbnail': 'No image thumbnail',
        'no-results': 'No results',
        'uploaded-image': 'Uploaded image',
    },
    errors: {
        401: {
            description: 'You must sign in to view this page.',
            title: 'Sign in required',
        },
        403: {
            description: 'You do not have permission to view this page.',
            title: 'Permission denied',
        },
        404: {
            description:
                'The page you requested does not exist or may have been moved.',
            title: 'Page not found',
        },
        default: {
            description:
                'The server could not complete the request. Please contact us for help.',
            title: 'Something went wrong',
        },
    },
    filters: {
        'all-items': 'Show all items',
        'all-origins': 'Show all origins',
        clicks: 'Clicks',
        'created-at': 'Created time',
        name: 'Name',
        'no-sort': 'No sorting',
        'only-not-listed': 'Only show unlisted items',
        'only-out-of-stock': 'Only show out-of-stock items',
        sales: 'Sales',
        'search-items':
            'Search name, SKU, variation, origin, or item description',
    },
    'image-upload': {
        fill: 'Fill',
        'invalid-file-size':
            'The selected image is too large. Choose an image smaller than 10 MB.',
        'invalid-file-type': 'Choose a JPG, PNG, GIF, or WebP image.',
        original: 'Original',
        stretch: 'Stretch',
        title: 'Upload image',
        'upload-failed': 'Unable to upload the selected image.',
        'white-edge': 'White border',
    },
    language: {
        chinese: '中文',
        english: 'English',
        label: 'Language',
    },
    notifications: {
        error: 'Error',
        'generic-error': 'Something went wrong.',
        success: 'Successful',
    },
    placeholders: {
        'no-items': 'No items',
        'no-orders': 'No orders',
        'no-results': 'No results',
    },
} as const
