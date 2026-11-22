<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BannerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_public_active_endpoint_returns_only_active_banners_in_order(): void
    {
        Banner::query()->create([
            'image' => 'banners/second.webp',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        Banner::query()->create([
            'image' => 'banners/inactive.jpg',
            'sort_order' => 1,
            'is_active' => false,
        ]);
        Banner::query()->create([
            'image' => 'banners/first.png',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/banners/active');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.image_url', '/storage/banners/first.png')
            ->assertJsonPath('data.1.image_url', '/storage/banners/second.webp');

        $this->assertArrayNotHasKey('is_active', $response->json('data.0'));
        $this->assertArrayNotHasKey('image', $response->json('data.0'));
    }

    public function test_guest_cannot_manage_banners(): void
    {
        $this->getJson('/api/banners')->assertUnauthorized();
        $this->postJson('/api/banners', [])->assertUnauthorized();
        $this->patchJson('/api/banners/reorder', ['ids' => [1]])->assertUnauthorized();
    }

    public function test_admin_can_upload_toggle_reorder_and_delete_banner(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $create = $this->post('/api/banners', [
            'images' => [
                $this->fakePng('satu.png'),
                $this->fakePng('dua.png'),
            ],
        ], ['Accept' => 'application/json']);

        $create->assertCreated()
            ->assertJsonCount(2, 'data');

        $firstId = $create->json('data.0.id');
        $secondId = $create->json('data.1.id');
        $firstPath = str_replace('/storage/', '', $create->json('data.0.image_url'));

        Storage::disk('public')->assertExists($firstPath);

        $this->patchJson("/api/banners/{$firstId}/status", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->getJson('/api/banners/active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $secondId);

        $this->patchJson('/api/banners/reorder', ['ids' => [$secondId, $firstId]])
            ->assertOk()
            ->assertJsonPath('data.0.id', $secondId)
            ->assertJsonPath('data.1.id', $firstId);

        $this->deleteJson("/api/banners/{$firstId}")
            ->assertOk();

        $this->assertDatabaseMissing('banners', ['id' => $firstId]);
        Storage::disk('public')->assertMissing($firstPath);
    }

    public function test_home_includes_active_hero_banners(): void
    {
        Banner::query()->create([
            'image' => 'banners/home.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Banner::query()->create([
            'image' => 'banners/off.jpg',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $this->getJson('/api/public/home')
            ->assertOk()
            ->assertJsonCount(1, 'hero_banners')
            ->assertJsonPath('hero_banners.0.image_url', '/storage/banners/home.jpg');
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

    private function fakePng(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $png);
    }
}
