<?php

namespace Tests\Feature;

use App\Models\PopupBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PopupBannerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_public_active_endpoint_filters_schedule_and_status(): void
    {
        PopupBanner::query()->create([
            'title' => 'Aktif',
            'image' => 'popup-banners/active.jpg',
            'is_active' => true,
            'display_order' => 2,
            'display_frequency' => 'every_visit',
        ]);

        PopupBanner::query()->create([
            'title' => 'Nonaktif',
            'image' => 'popup-banners/off.jpg',
            'is_active' => false,
            'display_order' => 1,
            'display_frequency' => 'every_visit',
        ]);

        PopupBanner::query()->create([
            'title' => 'Belum mulai',
            'image' => 'popup-banners/future.jpg',
            'is_active' => true,
            'start_at' => now()->addDay(),
            'display_order' => 0,
            'display_frequency' => 'every_visit',
        ]);

        PopupBanner::query()->create([
            'title' => 'Kadaluarsa',
            'image' => 'popup-banners/expired.jpg',
            'is_active' => true,
            'end_at' => now()->subHour(),
            'display_order' => 0,
            'display_frequency' => 'every_visit',
        ]);

        $first = PopupBanner::query()->create([
            'title' => 'Urutan pertama',
            'image' => 'popup-banners/first.jpg',
            'is_active' => true,
            'display_order' => 1,
            'display_frequency' => 'once_per_day',
        ]);

        $response = $this->getJson('/api/popup-banners/active');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Urutan pertama')
            ->assertJsonPath('data.1.title', 'Aktif');

        $this->assertArrayNotHasKey('image', $response->json('data.0'));
        $this->assertArrayNotHasKey('is_active', $response->json('data.0'));

        $this->assertSame($first->id, $response->json('data.0.id'));
    }

    public function test_guest_cannot_manage_popup_banners(): void
    {
        $this->getJson('/api/popup-banners')->assertUnauthorized();
        $this->postJson('/api/popup-banners', [])->assertUnauthorized();
    }

    public function test_admin_can_create_toggle_and_delete_popup_banner(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $file = UploadedFile::fake()->image('banner.jpg', 800, 600);

        $create = $this->post('/api/popup-banners', [
            'title' => 'PPDB 2026',
            'description' => 'Pendaftaran dibuka',
            'link_url' => 'https://man2kotamadiun.sch.id/ppdb',
            'button_text' => 'Daftar',
            'is_active' => '1',
            'display_order' => 1,
            'display_frequency' => 'once_per_session',
            'image' => $file,
        ], ['Accept' => 'application/json']);

        $create->assertCreated()
            ->assertJsonPath('data.title', 'PPDB 2026');

        $id = $create->json('data.id');
        Storage::disk('public')->assertExists($create->json('data.image'));

        $this->patchJson("/api/popup-banners/{$id}/status")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/popup-banners/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('popup_banners', ['id' => $id]);
        Storage::disk('public')->assertMissing($create->json('data.image'));
    }

    private function makeAdmin(): User
    {
        return User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }
}
