<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Http\Requests\Admin\Order\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\TablePriceMaster;
use App\Models\User;
use App\Repositories\Order\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected OrderRepository $repository;
    protected User $user;
    protected Table $table;
    protected TablePriceMaster $tablePrice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(OrderRepository::class);

        $this->user = User::factory()->create(['user_role' => UserRole::ADMIN]);

        $this->tablePrice = TablePriceMaster::factory()->create([
            'price_per_hour' => 50000,
        ]);

        $this->table = Table::factory()->create([
            'status' => TableStatus::AVAILABLE,
            'table_price_id' => $this->tablePrice->id,
        ]);

        // Authenticate as admin guard so OrderRepository can find auth user
        $this->actingAs($this->user, 'admin');
    }

    // -------------------------------------------------------------------------
    // get()
    // -------------------------------------------------------------------------

    /**
     * get() returns a paginator even when there are no orders.
     */
    public function test_get_returns_empty_paginator_when_no_orders(): void
    {
        $result = $this->repository->get(request());

        $this->assertEquals(0, $result->total());
    }

    /**
     * get() returns all orders and supports free-word search by table_name.
     */
    public function test_get_returns_orders_and_supports_search(): void
    {
        // Create 3 orders for our specific table
        Order::factory()->count(3)->create([
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
        ]);

        // All 3 should show up without filter
        $result = $this->repository->get(request());
        $this->assertEquals(3, $result->total());

        // Search by the table name — should still find them
        $req = request()->merge(['free_word' => $this->table->table_name]);
        $result2 = $this->repository->get($req);
        $this->assertEquals(3, $result2->total());

        // Non-matching search
        $req3 = request()->merge(['free_word' => 'zzz_this_does_not_exist']);
        $result3 = $this->repository->get($req3);
        $this->assertEquals(0, $result3->total());
    }

    // -------------------------------------------------------------------------
    // getById()
    // -------------------------------------------------------------------------

    /**
     * getById() returns the correct order with its relations.
     */
    public function test_get_by_id_returns_order_with_relations(): void
    {
        $order = Order::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $found = $this->repository->getById($order->id);

        $this->assertNotNull($found);
        $this->assertEquals($order->id, $found->id);
        $this->assertTrue($found->relationLoaded('details'));
        $this->assertTrue($found->relationLoaded('table'));
    }

    /**
     * getById() returns null for a non-existing ID.
     */
    public function test_get_by_id_returns_null_for_missing_id(): void
    {
        $found = $this->repository->getById(99999);

        $this->assertNull($found);
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    /**
     * create() persists a new order with PENDING status
     * and marks the table as IN_USE.
     */
    public function test_create_order_persists_and_sets_table_in_use(): void
    {
        /** @var OrderRequest $request */
        $request = OrderRequest::create('/', 'POST', [
            'table_id' => $this->table->id,
        ]);

        $order = $this->repository->create($request);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::PENDING,
            'user_id' => $this->user->id,
        ]);

        // Table must be IN_USE after order creation
        $this->assertEquals(TableStatus::IN_USE, $this->table->fresh()->status);

        // price_per_hour is taken from the table price
        $this->assertEquals($this->tablePrice->price_per_hour, $order->price_per_hour);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    /**
     * update() correctly updates order fields.
     */
    public function test_update_order_saves_new_fields(): void
    {
        $order = Order::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
            'price_per_hour' => 50000,
            'order_number' => 'ORD-ORIGINAL',
        ]);

        /** @var OrderRequest $request */
        $request = OrderRequest::create('/', 'PUT', [
            'table_id' => $this->table->id,
            'order_number' => 'ORD-UPDATED',
            'status' => OrderStatus::PENDING,
            'price_per_hour' => 50000,
            'note' => 'Cập nhật ghi chú',
            'total_minutes' => 60,
            'table_total' => 50000,
            'service_total' => 0,
            'final_total' => 50000,
            'order_details' => [],
        ]);

        // Inject the route binding so OrderRequest can resolve `order` parameter
        $request->route('order', $order->id);

        $result = $this->repository->update($request, $order->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_number' => 'ORD-UPDATED',
            'note' => 'Cập nhật ghi chú',
        ]);
    }

    /**
     * update() with a product detail reduces that product's stock.
     */
    public function test_update_order_deducts_product_stock(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $order = Order::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
            'price_per_hour' => 50000,
            'order_number' => 'ORD-STOCK-TEST',
        ]);

        /** @var OrderRequest $request */
        $request = OrderRequest::create('/', 'PUT', [
            'table_id' => $this->table->id,
            'order_number' => 'ORD-STOCK-TEST',
            'status' => OrderStatus::PENDING,
            'price_per_hour' => 50000,
            'total_minutes' => 60,
            'table_total' => 50000,
            'service_total' => 30000,
            'final_total' => 80000,
            'order_details' => [
                [
                    'id' => null,
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => 3,
                    'price' => 10000,
                    'sub_total' => 30000,
                ],
            ],
        ]);

        $request->route('order', $order->id);

        $result = $this->repository->update($request, $order->id);

        $this->assertTrue($result);
        // 10 - 3 = 7 remaining in stock
        $this->assertEquals(7, $product->fresh()->quantity);
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    /**
     * delete() soft-deletes the order.
     */
    public function test_delete_soft_deletes_order(): void
    {
        $order = Order::factory()->create([
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
        ]);

        $this->repository->delete($order->id);

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }
}
