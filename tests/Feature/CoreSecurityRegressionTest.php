<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreSecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_dashboard_resolves_on_case_sensitive_filesystems(): void
    {
        $customer = User::factory()->create(['status' => 'active']);

        $this->actingAs($customer, 'web')
            ->get(route('website.account.dashboard'))
            ->assertOk();
    }

    public function test_agent_redirect_uses_an_existing_dashboard_route(): void
    {
        $agent = $this->createAdminWithRole('agent');

        $this->actingAs($agent, 'admin')
            ->get(route('admin.redirect'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($agent, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_agent_order_list_and_dashboard_only_show_assigned_orders(): void
    {
        $agent = $this->createAdminWithRole('agent');
        $otherAgent = $this->createAdminWithRole('agent');
        $ownOrder = $this->createOrder($agent, 'ORD-AGENT-OWN');
        $otherOrder = $this->createOrder($otherAgent, 'ORD-AGENT-OTHER');

        $this->actingAs($agent, 'admin')
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($ownOrder->order_number)
            ->assertDontSee($otherOrder->order_number);

        $this->actingAs($agent, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($ownOrder->order_number)
            ->assertDontSee($otherOrder->order_number);
    }

    public function test_agent_cannot_view_or_update_another_agents_order(): void
    {
        $agent = $this->createAdminWithRole('agent');
        $otherAgent = $this->createAdminWithRole('agent');
        $otherOrder = $this->createOrder($otherAgent, 'ORD-NOT-MINE');

        $this->actingAs($agent, 'admin')
            ->getJson(route('admin.orders.show', $otherOrder))
            ->assertForbidden();

        $this->actingAs($agent, 'admin')
            ->patchJson(route('admin.orders.update', $otherOrder), [])
            ->assertForbidden();
    }

    public function test_agent_cannot_delete_an_order_with_only_update_permission(): void
    {
        $agent = $this->createAdminWithRole('agent');
        $order = $this->createOrder($agent, 'ORD-AGENT-DELETE');

        $this->actingAs($agent, 'admin')
            ->deleteJson(route('admin.orders.destroy', $order))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_keeps_global_order_access(): void
    {
        $admin = $this->createAdminWithRole('admin');
        $agent = $this->createAdminWithRole('agent');
        $order = $this->createOrder($agent, 'ORD-ADMIN-VIEW');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();
    }

    private function createAdminWithRole(string $role): Admin
    {
        static $sequence = 0;
        $sequence++;

        $admin = Admin::query()->create([
            'name' => ucfirst($role) . " {$sequence}",
            'email' => "{$role}{$sequence}@example.test",
            'password' => 'password',
            'status' => 'active',
        ]);

        $admin->assignRole($role);

        return $admin;
    }

    private function createOrder(?Admin $agent, string $number): Order
    {
        return Order::query()->create([
            'order_number' => $number,
            'user_id' => null,
            'assigned_agent_id' => $agent?->id,
            'coupon_id' => null,
            'coupon_code' => null,
            'buyer_name' => 'Demo Buyer',
            'buyer_phone' => '01700000000',
            'buyer_email' => strtolower($number) . '@example.test',
            'shipping_address' => 'Demo Address',
            'city_or_area' => 'Dhaka',
            'subtotal' => 1000,
            'discount' => 0,
            'shipping' => 60,
            'grand_total' => 1060,
            'payment_status' => Order::PAYMENT_UNPAID,
            'order_status' => Order::STATUS_PENDING,
            'customer_note' => null,
            'internal_note' => null,
        ]);
    }
}
