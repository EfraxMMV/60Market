<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\AdvertisementAsset;
use App\Models\Business;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdvertisementManagementTest extends TestCase
{
    use RefreshDatabase;

    private Region $region;
    private User $userA;
    private Business $businessA;
    private User $userB;
    private Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->region = Region::create([
            'name' => 'Colima',
            'slug' => 'colima',
            'state' => 'Colima',
            'country_code' => 'MX',
            'timezone' => 'America/Mexico_City',
            'is_active' => true,
        ]);

        $this->userA = User::factory()->create(['name' => 'Dueño A', 'email' => 'a@empresa.com']);
        $this->businessA = Business::create([
            'owner_user_id' => $this->userA->id,
            'region_id' => $this->region->id,
            'name' => 'Café A',
            'status' => 'verified',
        ]);

        $this->userB = User::factory()->create(['name' => 'Dueño B', 'email' => 'b@empresa.com']);
        $this->businessB = Business::create([
            'owner_user_id' => $this->userB->id,
            'region_id' => $this->region->id,
            'name' => 'Taller B',
            'status' => 'verified',
        ]);
    }

    public function test_guest_cannot_access_dashboard_or_ad_creation(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/ads/create')->assertRedirect('/login');
    }

    public function test_advertiser_can_view_dashboard_with_their_ads(): void
    {
        Advertisement::create([
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio Propio A',
            'destination_url' => 'https://cafe-a.com/oferta',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->userA)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Anuncio Propio A');
        $response->assertSee('Café A');
    }

    public function test_advertiser_can_create_draft_with_image_asset(): void
    {
        $image = UploadedFile::fake()->image('promo.png', 800, 600);

        $response = $this->actingAs($this->userA)->post('/ads', [
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Descuento en Café Frío',
            'destination_url' => 'https://cafe-a.com/promo',
            'content_type' => 'image',
            'image_file' => $image,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('advertisements', [
            'title' => 'Descuento en Café Frío',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $ad = Advertisement::where('title', 'Descuento en Café Frío')->first();
        $this->assertCount(1, $ad->assets);
        $this->assertEquals('image', $ad->assets->first()->type);
        $this->assertTrue(Storage::disk('public')->exists($ad->assets->first()->storage_key));
    }

    public function test_advertiser_can_create_draft_with_video_asset(): void
    {
        $video = UploadedFile::fake()->create('spot.mp4', 5000, 'video/mp4');

        $response = $this->actingAs($this->userA)->post('/ads', [
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Spot Promocional 60s',
            'destination_url' => 'https://cafe-a.com/spot',
            'content_type' => 'video',
            'video_file' => $video,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('advertisements', [
            'title' => 'Spot Promocional 60s',
            'content_type' => 'video',
            'status' => 'draft',
        ]);

        $ad = Advertisement::where('title', 'Spot Promocional 60s')->first();
        $this->assertCount(1, $ad->assets);
        $this->assertEquals('video', $ad->assets->first()->type);
        $this->assertTrue(Storage::disk('public')->exists($ad->assets->first()->storage_key));
    }

    public function test_advertiser_can_create_draft_with_ai_generated_html(): void
    {
        $validHtml = '<!DOCTYPE html><html><head><style>body{background:#032765;color:#fff;text-align:center;}</style></head><body><h1>Oferta Especial</h1><p>Visítanos hoy</p></body></html>';

        $response = $this->actingAs($this->userA)->post('/ads', [
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio Generado con IA',
            'destination_url' => 'https://cafe-a.com/ai',
            'content_type' => 'html',
            'generated_html' => $validHtml,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('advertisements', [
            'title' => 'Anuncio Generado con IA',
            'content_type' => 'html',
            'status' => 'draft',
            'generated_html' => $validHtml,
        ]);
    }

    public function test_advertiser_can_update_their_own_draft(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Título Antiguo',
            'destination_url' => 'https://cafe-a.com/antiguo',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->userA)->put("/ads/{$ad->id}", [
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Título Actualizado',
            'destination_url' => 'https://cafe-a.com/nuevo',
            'content_type' => 'image',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('advertisements', [
            'id' => $ad->id,
            'title' => 'Título Actualizado',
            'destination_url' => 'https://cafe-a.com/nuevo',
        ]);
    }

    public function test_advertiser_cannot_modify_ad_when_status_is_not_draft(): void
    {
        $adQueued = Advertisement::create([
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio en Cola',
            'destination_url' => 'https://cafe-a.com/cola',
            'content_type' => 'image',
            'status' => 'queued',
        ]);

        $response = $this->actingAs($this->userA)->put("/ads/{$adQueued->id}", [
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Intento de modificar anuncio en cola',
            'destination_url' => 'https://cafe-a.com/modificado',
            'content_type' => 'image',
        ]);

        $response->assertStatus(403);
    }

    public function test_advertiser_b_cannot_edit_or_delete_advertiser_a_advertisement(): void
    {
        $adA = Advertisement::create([
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio Privado de A',
            'destination_url' => 'https://cafe-a.com/privado',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $this->actingAs($this->userB)->get("/ads/{$adA->id}/edit")->assertStatus(403);
        $this->actingAs($this->userB)->put("/ads/{$adA->id}", [
            'business_id' => $this->businessB->id,
            'region_id' => $this->region->id,
            'title' => 'Ataque',
            'destination_url' => 'https://taller-b.com/hacked',
            'content_type' => 'image',
        ])->assertStatus(403);
        $this->actingAs($this->userB)->delete("/ads/{$adA->id}")->assertStatus(403);
    }

    public function test_advertiser_can_delete_their_own_draft(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->businessA->id,
            'region_id' => $this->region->id,
            'title' => 'Borrador para eliminar',
            'destination_url' => 'https://cafe-a.com/eliminar',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->userA)->delete("/ads/{$ad->id}");

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('advertisements', ['id' => $ad->id]);
    }
}
