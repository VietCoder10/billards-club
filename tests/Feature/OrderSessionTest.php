<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Table;
use App\Models\TablePriceMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'user_role' => UserRole::ADMIN,
        ]);

        // Create a table price master first
        $tablePrice = TablePriceMaster::factory()->create([
            'price_name' => 'Bàn Thường',
            'price_per_hour' => 50000,
        ]);

        // Create a table for the session tests
        $this->table = Table::factory()->create([
            'table_name' => 'Bàn 1',
            'status' => TableStatus::AVAILABLE,
            'table_price_id' => $tablePrice->id,
        ]);
    }

    /**
     * Test viewing playing sessions page (indexSession).
     */
    public function test_can_view_playing_sessions_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.order.indexSession'));

        $response->assertStatus(200);
    }

    /**
     * Test starting a play session by creating an order.
     */
    public function test_can_start_playing_session(): void
    {
        // Assert the table is available initially
        $this->assertEquals(TableStatus::AVAILABLE, $this->table->fresh()->status);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.store'), [
                'table_id' => $this->table->id,
            ]);

        // It should redirect to order-item edit page
        $response->assertStatus(302);

        // Check if order was created in the database
        $order = Order::where('table_id', $this->table->id)
            ->where('status', OrderStatus::PENDING)
            ->first();

        $this->assertNotNull($order);
        $this->assertEquals($this->admin->id, $order->user_id);

        // The table status should be updated to IN_USE
        $this->assertEquals(TableStatus::IN_USE, $this->table->fresh()->status);
    }

    /**
     * Test updating a playing session details.
     */
    public function test_can_update_playing_session_details(): void
    {
        // Start a session first
        $order = Order::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::PENDING,
            'price_per_hour' => $this->table->tablePrice->price_per_hour,
            'started_at' => now(),
            'order_number' => 'ORD-12345678',
        ]);

        $this->table->update(['status' => TableStatus::IN_USE]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.order.updateSession', $order->id), [
                'table_id' => $this->table->id,
                'order_number' => 'ORD-12345678',
                'status' => OrderStatus::PENDING,
                'price_per_hour' => 50000,
                'note' => 'Cập nhật ghi chú mới',
            ]);

        $response->assertRedirect(route('admin.order.indexSession'));

        $this->assertEquals('Cập nhật ghi chú mới', $order->fresh()->note);
    }

    /**
     * Test ending a playing session by paying and generating an invoice.
     */
    public function test_can_end_playing_session_via_invoice(): void
    {
        // Start a session
        $order = Order::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::PENDING,
            'price_per_hour' => 50000,
            'started_at' => now()->subHours(2), // Played for 2 hours
            'order_number' => 'ORD-TEST1234',
        ]);

        $this->table->update(['status' => TableStatus::IN_USE]);

        // Invoice creation payload
        $payload = [
            'order_id' => $order->id,
            'order_number' => 'ORD-TEST1234',
            'table_name' => $this->table->table_name,
            'table_total' => 100000, // 50000 * 2 hours
            'service_total' => 0,
            'final_total' => 100000,
            'total_minutes' => 120,
            'payment_method' => 1,
            'details' => [
                [
                    'product_id' => null,
                    'item_name' => 'Tiền bàn (120 phút)',
                    'quantity' => 1,
                    'price' => 100000,
                    'sub_total' => 100000,
                ]
            ],
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.invoice.store'), $payload);

        $response->assertRedirect(route('admin.order.indexSession'));

        // Assert order status updated to completed
        $this->assertEquals(OrderStatus::COMPLETED, $order->fresh()->status);

        // Assert table status updated back to available
        $this->assertEquals(TableStatus::AVAILABLE, $this->table->fresh()->status);
    }
}
