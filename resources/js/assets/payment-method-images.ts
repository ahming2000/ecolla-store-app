import boostIcon from '@/assets/images/payment-methods/icons/boost.png'
import maybankQrPayIcon from '@/assets/images/payment-methods/icons/maybank-qr-pay.png'
import onlineBankingIcon from '@/assets/images/payment-methods/icons/online-banking.png'
import quinPayIcon from '@/assets/images/payment-methods/icons/quin-pay.png'
import touchNGoIcon from '@/assets/images/payment-methods/icons/tng.png'
import boostQrCode from '@/assets/images/payment-methods/qr-codes/boost-qr-code.jpeg'
import maybankQrPayQrCode from '@/assets/images/payment-methods/qr-codes/maybank-qr-pay-qr-code.jpeg'
import onlineBankingQrCode from '@/assets/images/payment-methods/qr-codes/online-banking-qr-code.jpeg'
import quinPayQrCode from '@/assets/images/payment-methods/qr-codes/quin-pay-qr-code.jpeg'
import touchNGoQrCode from '@/assets/images/payment-methods/qr-codes/tng-qr-code.jpeg'

const paymentMethodIcons: Record<string, string> = {
    boost: boostIcon,
    'maybank-qr-pay': maybankQrPayIcon,
    'online-banking': onlineBankingIcon,
    'quin-pay': quinPayIcon,
    tng: touchNGoIcon,
}

const paymentMethodQrCodes: Record<string, string> = {
    boost: boostQrCode,
    'maybank-qr-pay': maybankQrPayQrCode,
    'online-banking': onlineBankingQrCode,
    'quin-pay': quinPayQrCode,
    tng: touchNGoQrCode,
}

const getAssetKey = (assetPath: string): string => {
    const fileName = assetPath.split('/').pop() ?? assetPath

    return fileName
        .replace(/\.(?:gif|jpe?g|png|webp)$/i, '')
        .replace(/-qr-code$/, '')
}

const resolveAsset = (
    assetPath: string | null,
    assets: Record<string, string>
): string | undefined => {
    if (!assetPath) {
        return undefined
    }

    return assets[getAssetKey(assetPath)] ?? assetPath
}

export const resolvePaymentMethodIcon = (
    assetPath: string | null
): string | undefined => resolveAsset(assetPath, paymentMethodIcons)

export const resolvePaymentMethodQrCode = (
    assetPath: string | null
): string | undefined => resolveAsset(assetPath, paymentMethodQrCodes)
