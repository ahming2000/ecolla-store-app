<?php

namespace App\Services;

use App\Enums\DeliveryMode;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public const SHIPPING_FEE = 'shipping_fee';

    public const FREE_SHIPPING_IS_ACTIVATED = 'freeShipping_isActivated';

    public const FREE_SHIPPING_THRESHOLD = 'freeShipping_threshold';

    public const FREE_SHIPPING_DESCRIPTION = 'freeShipping_desc';

    private const DEFAULT_SHIPPING_FEE = '3';

    private const DEFAULT_FREE_SHIPPING_IS_ACTIVATED = '0';

    private const DEFAULT_FREE_SHIPPING_THRESHOLD = '0';

    private const DEFAULT_FREE_SHIPPING_DESCRIPTION = '';

    /**
     * @return array{
     *     fee: float,
     *     freeShipping: array{
     *         isActivated: bool,
     *         threshold: float,
     *         description: string
     *     }
     * }
     */
    public function getShippingSettings(): array
    {
        $values = Setting::query()
            ->whereKey([
                self::SHIPPING_FEE,
                self::FREE_SHIPPING_IS_ACTIVATED,
                self::FREE_SHIPPING_THRESHOLD,
                self::FREE_SHIPPING_DESCRIPTION,
            ])
            ->pluck('value', 'name');

        return [
            'fee' => (float) $values->get(
                self::SHIPPING_FEE,
                self::DEFAULT_SHIPPING_FEE,
            ),
            'freeShipping' => [
                'isActivated' => (bool) $values->get(
                    self::FREE_SHIPPING_IS_ACTIVATED,
                    self::DEFAULT_FREE_SHIPPING_IS_ACTIVATED,
                ),
                'threshold' => (float) $values->get(
                    self::FREE_SHIPPING_THRESHOLD,
                    self::DEFAULT_FREE_SHIPPING_THRESHOLD,
                ),
                'description' => (string) $values->get(
                    self::FREE_SHIPPING_DESCRIPTION,
                    self::DEFAULT_FREE_SHIPPING_DESCRIPTION,
                ),
            ],
        ];
    }

    public function getShippingFee(): float
    {
        return (float) $this->getValue(
            self::SHIPPING_FEE,
            self::DEFAULT_SHIPPING_FEE,
        );
    }

    public function isFreeShippingActivated(): bool
    {
        return (bool) $this->getValue(
            self::FREE_SHIPPING_IS_ACTIVATED,
            self::DEFAULT_FREE_SHIPPING_IS_ACTIVATED,
        );
    }

    public function getFreeShippingThreshold(): float
    {
        return (float) $this->getValue(
            self::FREE_SHIPPING_THRESHOLD,
            self::DEFAULT_FREE_SHIPPING_THRESHOLD,
        );
    }

    public function getFreeShippingDesc(): string
    {
        return $this->getValue(
            self::FREE_SHIPPING_DESCRIPTION,
            self::DEFAULT_FREE_SHIPPING_DESCRIPTION,
        );
    }

    public function calculateShippingFee(
        DeliveryMode $deliveryMode,
        float $subtotal,
    ): float {
        if ($deliveryMode !== DeliveryMode::DELIVERY) {
            return 0.0;
        }

        $shipping = $this->getShippingSettings();

        if (
            $shipping['freeShipping']['isActivated']
            && $subtotal >= $shipping['freeShipping']['threshold']
        ) {
            return 0.0;
        }

        return $shipping['fee'];
    }

    public function updateShippingFee(float $shippingFee): void
    {
        $this->updateValue(self::SHIPPING_FEE, (string) $shippingFee);
    }

    public function updateFreeShipping(
        bool $isActivated,
        float $threshold,
        string $description,
    ): void {
        DB::transaction(function () use ($isActivated, $threshold, $description): void {
            $this->updateValue(
                self::FREE_SHIPPING_IS_ACTIVATED,
                (string) (int) $isActivated,
            );
            $this->updateValue(
                self::FREE_SHIPPING_THRESHOLD,
                (string) $threshold,
            );
            $this->updateValue(
                self::FREE_SHIPPING_DESCRIPTION,
                $description,
            );
        });
    }

    private function getValue(string $name, string $default): string
    {
        $value = Setting::query()->whereKey($name)->value('value');

        return $value === null ? $default : (string) $value;
    }

    private function updateValue(string $name, string $value): void
    {
        Setting::query()->findOrFail($name)->update(['value' => $value]);
    }
}
