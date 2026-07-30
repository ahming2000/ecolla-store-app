<?php

namespace Tests\Feature;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Tests\TestCase;

class TimezoneAwareResourceTest extends TestCase
{
    public function test_resource_serializes_dates_in_the_authenticated_users_timezone(): void
    {
        $user = new User;
        $user->forceFill(['timezone' => 'Asia/Kuala_Lumpur']);

        $request = Request::create('/');
        $request->setUserResolver(fn (): User => $user);

        $data = OrderResource::make(
            $this->orderAt(CarbonImmutable::parse('2026-01-15 00:00:00', 'UTC')),
        )->resolve($request);

        $this->assertSame('2026-01-15T08:00:00+08:00', $data['created_at']);
    }

    public function test_resource_uses_kuala_lumpur_timezone_for_guests(): void
    {
        $data = OrderResource::make(
            $this->orderAt(CarbonImmutable::parse('2026-07-15 12:00:00', 'UTC')),
        )->resolve(Request::create('/'));

        $this->assertSame('2026-07-15T20:00:00+08:00', $data['created_at']);
    }

    public function test_resource_uses_kuala_lumpur_timezone_when_the_users_timezone_is_invalid(): void
    {
        $user = new User;
        $user->forceFill(['timezone' => 'Invalid/Timezone']);

        $request = Request::create('/');
        $request->setUserResolver(fn (): User => $user);

        $data = OrderResource::make(
            $this->orderAt(CarbonImmutable::parse('2026-01-15 00:00:00', 'UTC')),
        )->resolve($request);

        $this->assertSame('2026-01-15T08:00:00+08:00', $data['created_at']);
    }

    public function test_resource_omits_a_missing_date(): void
    {
        $data = OrderResource::make(new Order)->resolve(Request::create('/'));

        $this->assertArrayNotHasKey('created_at', $data);
    }

    private function orderAt(CarbonImmutable $dateTime): Order
    {
        $order = new Order;
        $order->forceFill([
            'id' => 1,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
        ]);

        return $order;
    }
}
