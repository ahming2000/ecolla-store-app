<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->reference_num }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        :root {
            color: #18181b;
            font-family: 'Noto Sans SC', sans-serif;
            font-size: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            background: #fff;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        main {
            width: auto;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        h1,
        h2,
        p {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        h2 {
            margin-top: 32px;
            margin-bottom: 12px;
            font-size: 18px;
        }

        .reference {
            color: #71717a;
        }

        .logo {
            display: block;
            width: 75px;
            height: 75px;
            margin-bottom: 16px;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 32px;
            margin-top: 28px;
        }

        .detail {
            display: grid;
            grid-template-columns: minmax(145px, 0.7fr) minmax(0, 1fr);
            gap: 12px;
        }

        .label {
            color: #71717a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 12px;
            border: 1px solid #d4d4d8;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f4f4f5;
            font-weight: 700;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .secondary {
            margin-top: 3px;
            color: #71717a;
            font-size: 12px;
        }

        .totals {
            width: min(360px, 100%);
            margin-top: 16px;
            margin-left: auto;
        }

        .totals td:first-child {
            font-weight: 700;
        }

        .grand-total {
            font-size: 16px;
            font-weight: 700;
        }
    </style>
</head>
<body>
<main>
    <img class="logo" src="{{ $logoDataUri }}" alt="Ecolla">
    <h1>订单 / Order</h1>
    <p class="reference">{{ $order->reference_num }}</p>

    <section class="details">
        <div class="detail">
            <span class="label">日期 / Date</span>
            <span>{{ $createdAtDisplay }}</span>
        </div>
        <div class="detail">
            <span class="label">状态 / Status</span>
            <span>{{ $statusLabel }}</span>
        </div>
        <div class="detail">
            <span class="label">种类 / Type</span>
            <span>{{ $deliveryModeLabel }}</span>
        </div>
        <div class="detail">
            <span class="label">付款方式 / Payment</span>
            <span>{{ $order->paymentMethod->name }}</span>
        </div>
        <div class="detail">
            <span class="label">顾客名称 / Customer</span>
            <span>{{ $order->cus_name }}</span>
        </div>
        <div class="detail">
            <span class="label">电话号码 / Phone</span>
            <span>{{ $order->cus_phone }}</span>
        </div>
        @if ($isDelivery)
            <div class="detail">
                <span class="label">地址 / Address</span>
                <span>{{ $order->cus_address ?: '—' }}</span>
            </div>
            <div class="detail">
                <span class="label">邮寄追踪 ID / Tracking ID</span>
                <span>{{ $order->tracking_no ?: '—' }}</span>
            </div>
        @endif
        <div class="detail">
            <span class="label">备注 / Note</span>
            <span>{{ $order->note ?: '—' }}</span>
        </div>
    </section>

    <h2>商品详情 / Item details</h2>
    <table>
        <thead>
        <tr>
            <th>名称 / Name</th>
            <th>货号 / SKU</th>
            <th class="number">单价 / Unit price</th>
            <th class="number">数量 / Quantity</th>
            <th class="number">小计 / Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($order->items as $item)
            @php
                $unitPrice = $item->sale_price ?? $item->price;
                $lineTotal = $unitPrice * $item->quantity;
            @endphp
            <tr>
                <td>
                    <div>{{ $item->name }}</div>
                    @if ($item->name_en)
                        <div class="secondary">{{ $item->name_en }}</div>
                    @endif
                </td>
                <td>{{ $item->barcode }}</td>
                <td class="number">RM {{ number_format($unitPrice, 2, '.', '') }}</td>
                <td class="number">{{ $item->quantity }}</td>
                <td class="number">RM {{ number_format($lineTotal, 2, '.', '') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tbody>
        <tr>
            <td>商品总计 / Subtotal</td>
            <td class="number">RM {{ $subtotal }}</td>
        </tr>
        <tr>
            <td>运费 / Shipping</td>
            <td class="number">RM {{ number_format($order->shipping_fee, 2, '.', '') }}</td>
        </tr>
        <tr class="grand-total">
            <td>全部总计 / Total</td>
            <td class="number">RM {{ $total }}</td>
        </tr>
        </tbody>
    </table>
</main>
</body>
</html>
