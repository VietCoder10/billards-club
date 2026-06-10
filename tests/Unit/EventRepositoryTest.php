<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use App\Models\UserEvent;
use App\Repositories\Event\EventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class EventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected EventRepository $repository;
    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(EventRepository::class);

        $this->admin = User::factory()->create(['user_role' => UserRole::ADMIN]);
        $this->staff = User::factory()->create(['user_role' => UserRole::USER]);

        // Authenticate as admin so EventRepository resolves Auth::guard('admin')
        $this->actingAs($this->admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // get()
    // -------------------------------------------------------------------------

    /**
     * get() returns empty collection when no events exist in range.
     */
    public function test_get_returns_empty_collection_when_no_events(): void
    {
        $request = Request::create('/', 'POST', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]);

        $result = $this->repository->get($request);

        $this->assertCount(0, $result);
    }

    /**
     * get() returns public events within the date range.
     */
    public function test_get_returns_events_in_date_range(): void
    {
        // Event inside range
        $event = Event::create([
            'name' => 'Ca sáng',
            'start_date' => '2026-06-10 08:00:00',
            'end_date' => '2026-06-10 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'created_by' => $this->admin->id,
            'private_flag' => 0,
        ]);

        // Event outside range
        Event::create([
            'name' => 'Ca ngoài tháng',
            'start_date' => '2026-07-01 08:00:00',
            'end_date' => '2026-07-01 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'created_by' => $this->admin->id,
            'private_flag' => 0,
        ]);

        $request = Request::create('/', 'POST', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]);

        $result = $this->repository->get($request);

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }

    /**
     * get() only returns private events belonging to the current admin.
     */
    public function test_get_does_not_return_other_users_private_events(): void
    {
        $otherAdmin = User::factory()->create(['user_role' => UserRole::ADMIN]);

        // Private event created by a different admin (not assigned to $this->admin)
        Event::create([
            'name' => 'Ca bí mật',
            'start_date' => '2026-06-15 08:00:00',
            'end_date' => '2026-06-15 12:00:00',
            'color' => '#a855f7',
            'event_type' => 1,
            'created_by' => $otherAdmin->id,
            'private_flag' => 1,
        ]);

        $request = Request::create('/', 'POST', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]);

        $result = $this->repository->get($request);

        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // store() — create
    // -------------------------------------------------------------------------

    /**
     * store() creates a new event and assigns the given users.
     */
    public function test_store_creates_event_with_users(): void
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Ca chiều',
            'start_date' => '2026-06-20 13:00:00',
            'end_date' => '2026-06-20 17:00:00',
            'color' => '#ef4444',
            'event_type' => 1,
            'note' => 'Bàn VIP',
            'user_ids' => [$this->staff->id],
        ]);

        $result = $this->repository->store($request);

        $this->assertTrue($result);

        $event = Event::where('name', 'Ca chiều')->first();
        $this->assertNotNull($event);
        $this->assertEquals($this->admin->id, $event->created_by);
        // Staff should be attached
        $this->assertTrue($event->users->contains($this->staff->id));
        // Admin should always be attached (auto-added as owner)
        $this->assertTrue($event->users->contains($this->admin->id));
    }

    /**
     * store() with no user_ids still attaches the creator as a user.
     */
    public function test_store_always_attaches_creator(): void
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Ca tối',
            'start_date' => '2026-06-21 18:00:00',
            'end_date' => '2026-06-21 22:00:00',
            'color' => '#22c55e',
            'event_type' => 1,
        ]);

        $this->repository->store($request);

        $event = Event::where('name', 'Ca tối')->first();
        $this->assertNotNull($event);
        $this->assertTrue($event->users->contains($this->admin->id));
    }

    // -------------------------------------------------------------------------
    // store() — update
    // -------------------------------------------------------------------------

    /**
     * store() with an existing id updates the event.
     */
    public function test_store_updates_existing_event(): void
    {
        $event = Event::create([
            'name' => 'Ca ban đầu',
            'start_date' => '2026-06-10 08:00:00',
            'end_date' => '2026-06-10 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'created_by' => $this->admin->id,
            'private_flag' => 0,
        ]);

        $request = Request::create('/', 'POST', [
            'id' => $event->id,
            'name' => 'Ca đã cập nhật',
            'start_date' => '2026-06-11 08:00:00',
            'end_date' => '2026-06-11 12:00:00',
            'color' => '#ef4444',
            'event_type' => 1,
            'user_ids' => [$this->staff->id],
        ]);

        $result = $this->repository->store($request);

        $this->assertTrue($result);
        $this->assertEquals('Ca đã cập nhật', $event->fresh()->name);
        $this->assertEquals('#ef4444', $event->fresh()->color);
    }

    /**
     * store() removes user associations that are no longer in user_ids.
     */
    public function test_store_removes_deselected_users_from_event(): void
    {
        $event = Event::create([
            'name' => 'Ca kiểm tra',
            'start_date' => '2026-06-12 08:00:00',
            'end_date' => '2026-06-12 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'created_by' => $this->admin->id,
            'private_flag' => 0,
        ]);

        // Attach both admin and staff initially
        $event->users()->attach([$this->admin->id, $this->staff->id]);

        // Update: only keep admin (remove staff)
        $request = Request::create('/', 'POST', [
            'id' => $event->id,
            'name' => 'Ca kiểm tra',
            'start_date' => '2026-06-12 08:00:00',
            'end_date' => '2026-06-12 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'user_ids' => [$this->admin->id], // staff removed
        ]);

        $this->repository->store($request);

        $event->refresh();
        $this->assertFalse($event->users->contains($this->staff->id));
        $this->assertTrue($event->users->contains($this->admin->id));
    }

    // -------------------------------------------------------------------------
    // destroy()
    // -------------------------------------------------------------------------

    /**
     * destroy() soft-deletes the event and detaches users.
     */
    public function test_destroy_soft_deletes_event_and_detaches_users(): void
    {
        $event = Event::create([
            'name' => 'Ca bị xóa',
            'start_date' => '2026-06-13 08:00:00',
            'end_date' => '2026-06-13 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'created_by' => $this->admin->id,
            'private_flag' => 0,
        ]);

        $event->users()->attach([$this->admin->id, $this->staff->id]);

        $result = $this->repository->destroy((string) $event->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('events', ['id' => $event->id]);
        $this->assertDatabaseMissing('user_events', ['event_id' => $event->id]);
    }

    /**
     * destroy() returns false for a non-existing event.
     */
    public function test_destroy_returns_false_for_non_existing_event(): void
    {
        $result = $this->repository->destroy('99999');

        $this->assertFalse($result);
    }
}
