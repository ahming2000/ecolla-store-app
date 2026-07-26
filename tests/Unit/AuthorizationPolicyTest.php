<?php

namespace Tests\Unit;

use App\Enums\AccessLevel;
use App\Enums\Status;
use App\Models\Item;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Policies\ItemPolicy;
use App\Policies\OrderPolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    public function test_item_permissions_increase_with_access_level(): void
    {
        $policy = new ItemPolicy;
        $item = new Item;

        $this->assertTrue($policy->viewAny($this->user(AccessLevel::VIEWER)));
        $this->assertFalse($policy->create($this->user(AccessLevel::VIEWER)));
        $this->assertTrue($policy->create($this->user(AccessLevel::EDITOR)));
        $this->assertFalse($policy->delete($this->user(AccessLevel::EDITOR), $item));
        $this->assertTrue($policy->delete($this->user(AccessLevel::SUPERVISOR), $item));
        $this->assertFalse($policy->forceDelete($this->user(AccessLevel::SUPERVISOR), $item));
        $this->assertTrue($policy->forceDelete($this->user(AccessLevel::ADMIN), $item));
    }

    public function test_editors_can_only_change_pending_order_fulfilment_details(): void
    {
        $policy = new OrderPolicy;
        $editor = $this->user(AccessLevel::EDITOR);
        $pendingOrder = new Order(['status' => Status::PENDING]);
        $completedOrder = new Order(['status' => Status::COMPLETED]);

        $this->assertTrue($policy->updateStatus($editor, $pendingOrder));
        $this->assertTrue($policy->updateTrackingNumber($editor, $pendingOrder));
        $this->assertFalse($policy->updateStatus($editor, $completedOrder));
        $this->assertFalse($policy->updateTrackingNumber($editor, $completedOrder));
    }

    public function test_supervisors_can_manage_orders_but_only_admins_can_force_delete_them(): void
    {
        $policy = new OrderPolicy;
        $supervisor = $this->user(AccessLevel::SUPERVISOR);
        $admin = $this->user(AccessLevel::ADMIN);
        $completedOrder = new Order(['status' => Status::COMPLETED]);

        $this->assertTrue($policy->update($supervisor, $completedOrder));
        $this->assertFalse($policy->delete($supervisor, $completedOrder));
        $this->assertTrue($policy->delete($admin, $completedOrder));
        $this->assertTrue($policy->forceDelete($admin, $completedOrder));
    }

    public function test_setting_management_has_distinct_view_update_and_delete_boundaries(): void
    {
        $policy = new SettingPolicy;
        $setting = new Setting;

        $this->assertTrue($policy->viewAny($this->user(AccessLevel::VIEWER)));
        $this->assertFalse($policy->update($this->user(AccessLevel::EDITOR), $setting));
        $this->assertTrue($policy->update($this->user(AccessLevel::SUPERVISOR), $setting));
        $this->assertFalse($policy->delete($this->user(AccessLevel::SUPERVISOR), $setting));
        $this->assertTrue($policy->delete($this->user(AccessLevel::ADMIN), $setting));
    }

    public function test_users_can_only_change_their_own_password_and_only_admins_manage_users(): void
    {
        $policy = new UserPolicy;
        $viewer = $this->user(AccessLevel::VIEWER, 10);
        $otherViewer = $this->user(AccessLevel::VIEWER, 11);

        $this->assertTrue($policy->updatePassword($viewer, $viewer));
        $this->assertFalse($policy->updatePassword($viewer, $otherViewer));
        $this->assertFalse($policy->viewAny($this->user(AccessLevel::SUPERVISOR)));
        $this->assertTrue($policy->viewAny($this->user(AccessLevel::ADMIN)));

        $admin = $this->user(AccessLevel::ADMIN, 12);
        $this->assertTrue($policy->deactivate($admin, $viewer));
        $this->assertTrue($policy->reactivate($admin, $viewer));
        $this->assertTrue($policy->delete($admin, $viewer));
        $this->assertFalse($policy->deactivate($admin, $admin));
        $this->assertFalse($policy->reactivate($admin, $admin));
        $this->assertFalse($policy->delete($admin, $admin));
    }

    private function user(AccessLevel $accessLevel, int $id = 1): User
    {
        $user = new User;
        $user->forceFill([
            'id' => $id,
            'access_level' => $accessLevel->value,
        ]);

        return $user;
    }
}
