<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'user_role' => UserRole::ADMIN,
        ]);

        // Create a staff user to assign shifts to
        $this->staff = User::factory()->create([
            'user_role' => UserRole::USER,
        ]);
    }

    /**
     * Test viewing dashboard index page.
     */
    public function test_can_view_dashboard_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard.index'));

        $response->assertStatus(200);
    }

    /**
     * Test fetching shift events list.
     */
    public function test_can_get_shift_events(): void
    {
        // Create an event manually
        $event = Event::create([
            'name' => 'Ca sáng - Nhân viên A',
            'start_date' => '2026-06-10 08:00:00',
            'end_date' => '2026-06-10 12:00:00',
            'color' => '#3b82f6',
            'event_type' => 1,
            'created_by' => $this->admin->id,
        ]);

        // Attach users to the event
        $event->users()->attach([$this->admin->id, $this->staff->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.dashboard.event'), [
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'events' => [
                    '*' => [
                        'id',
                        'title',
                        'backgroundColor',
                        'borderColor',
                        'start',
                        'end',
                        'allDay',
                        'textColor',
                        'avatar_urls',
                        'extendedProps' => [
                            'event_type',
                            'location',
                            'note',
                            'start_date',
                            'end_date',
                            'type',
                            'color',
                            'private_flag',
                            'desktop_notification_flag',
                            'user_ids',
                            'flag_delete',
                            'is_complete',
                            'user_event_user_id',
                        ]
                    ]
                ]
            ]);

        // Assert our event is in the response list
        $events = $response->json('events');
        $this->assertCount(1, $events);
        $this->assertEquals($event->id, $events[0]['id']);
        $this->assertEquals($event->name, $events[0]['title']);
    }

    /**
     * Test creating a new shift assignment (event).
     */
    public function test_can_store_shift_event(): void
    {
        $payload = [
            'name' => 'Ca chiều - Nhân viên B',
            'start_date' => '2026-06-10 13:00:00',
            'end_date' => '2026-06-10 17:00:00',
            'color' => '#ef4444',
            'event_type' => 1,
            'note' => 'Hỗ trợ bàn VIP',
            'user_ids' => [$this->staff->id],
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.dashboard.store'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Lưu thành công',
            ]);

        // Assert the event was created in the database
        $event = Event::where('name', 'Ca chiều - Nhân viên B')->first();
        $this->assertNotNull($event);
        $this->assertEquals($this->admin->id, $event->created_by);

        // Assert the user association was saved
        $this->assertTrue($event->users->contains($this->staff->id));
    }

    /**
     * Test deleting a shift assignment event.
     */
    public function test_can_delete_shift_event(): void
    {
        // Create an event manually
        $event = Event::create([
            'name' => 'Ca tối - Nhân viên C',
            'start_date' => '2026-06-10 18:00:00',
            'end_date' => '2026-06-10 22:00:00',
            'color' => '#22c55e',
            'event_type' => 1,
            'created_by' => $this->admin->id,
        ]);

        $event->users()->attach([$this->staff->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.dashboard.destroy', $event->id));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Xóa thành công',
            ]);

        // Assert event was soft deleted (since Event uses SoftDeletes)
        $this->assertSoftDeleted('events', [
            'id' => $event->id,
        ]);

        // Assert user event relationship is detached (detached in repository using `detach()`)
        $this->assertDatabaseMissing('user_events', [
            'event_id' => $event->id,
        ]);
    }
}
