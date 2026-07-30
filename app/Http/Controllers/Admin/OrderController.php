<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryMode;
use App\Enums\Language;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\IndexAdminOrdersRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\UpdateOrderTrackingNumberRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function page(): Response
    {
        return Inertia::render('admin/order/OrderPage');
    }

    public function index(IndexAdminOrdersRequest $request): JsonResponse
    {
        $orders = $this->orderService->getAdminOrders(
            orderDate: $request->orderDate(),
            deliveryMode: $request->deliveryMode(),
            page: $request->page(),
            perPage: $request->perPage(),
        );

        $orders->through(
            function (Order $order) use ($request): array {
                $order->setAttribute(
                    'subtotal',
                    $this->orderService->getOrderItemsSubtotal($order),
                );

                return OrderResource::make($order)->resolve($request);
            },
        );

        return response()->json($orders);
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
    ): JsonResponse {
        $data = $request->validated();
        $attributes = ['status' => $data['status']];

        if (
            $order->delivery_mode === DeliveryMode::DELIVERY
            && array_key_exists('tracking_no', $data)
        ) {
            $attributes['tracking_no'] = $data['tracking_no'];
        }

        $order->update($attributes);

        return response()->json(
            $order->only(['id', 'status', 'tracking_no']),
        );
    }

    public function updateTrackingNumber(
        UpdateOrderTrackingNumberRequest $request,
        Order $order,
    ): JsonResponse {
        $order->update($request->validated());

        return response()->json(
            $order->only(['id', 'status', 'tracking_no']),
        );
    }

    public function update(
        UpdateOrderRequest $request,
        Order $order,
    ): JsonResponse {
        $order = $this->orderService->updateOrder(
            $order,
            $request->orderData(),
        );
        $order->setAttribute(
            'subtotal',
            $this->orderService->getOrderItemsSubtotal($order),
        );

        return response()->json(
            OrderResource::make($order)->resolve($request),
        );
    }

    public function download(Request $request, Order $order): HttpResponse
    {
        $order->loadMissing(['items', 'paymentMethod']);

        $subtotalValue = $this->orderService->getOrderItemsSubtotalValue($order);
        $subtotal = number_format($subtotalValue, 2, '.', '');
        $total = number_format(
            $subtotalValue + (float) $order->shipping_fee,
            2,
            '.',
            '',
        );
        $pdf = Pdf::loadView('admin.order.download', [
            'createdAtDisplay' => $order->created_at
                ->setTimezone($request->user()->timezone)
                ->format('Y/m/d H:i'),
            'deliveryModeLabel' => sprintf(
                '%s / %s',
                DeliveryMode::getLabel($order->delivery_mode, Language::ZH),
                DeliveryMode::getLabel($order->delivery_mode, Language::EN),
            ),
            'isDelivery' => $order->delivery_mode === DeliveryMode::DELIVERY,
            'logoDataUri' => 'data:image/png;base64,'.base64_encode(
                File::get(
                    resource_path('js/assets/images/branding/ecolla.png'),
                ),
            ),
            'order' => $order,
            'statusLabel' => sprintf(
                '%s / %s',
                Status::getLabel($order->status, Language::ZH),
                Status::getLabel($order->status, Language::EN),
            ),
            'subtotal' => $subtotal,
            'total' => $total,
        ])
            ->setPaper('a4')
            ->setOption('defaultMediaType', 'print')
            ->setOption('isFontSubsettingEnabled', true);
        $this->registerOrderDownloadFonts($pdf);

        $reference = Str::of($order->reference_num)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9_-]+/', '-')
            ->trim('-_')
            ->toString();
        $filename = ($reference !== '' ? $reference : "order-{$order->id}").'.pdf';

        return $pdf->download($filename);
    }

    private function registerOrderDownloadFonts(DomPdf $pdf): void
    {
        File::ensureDirectoryExists(storage_path('fonts'));

        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $fonts = [
            [
                'path' => resource_path(
                    'fonts/noto-sans-sc/NotoSansSC-Regular.ttf',
                ),
                'weight' => 'normal',
            ],
            [
                'path' => resource_path(
                    'fonts/noto-sans-sc/NotoSansSC-Bold.ttf',
                ),
                'weight' => 'bold',
            ],
        ];

        foreach ($fonts as $font) {
            $registered = $fontMetrics->registerFont(
                [
                    'family' => 'Noto Sans SC',
                    'style' => 'normal',
                    'weight' => $font['weight'],
                ],
                $font['path'],
            );

            if (! $registered) {
                throw new RuntimeException(
                    "Unable to register the PDF font [{$font['path']}].",
                );
            }
        }
    }
}
