<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Business;
use App\Models\Region;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewerTest extends TestCase
{
    use RefreshDatabase;

    private Region $region;
    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->region = Region::create([
            'name' => 'Colima',
            'slug' => 'colima',
            'state' => 'Colima',
            'country_code' => 'MX',
            'timezone' => 'America/Mexico_City',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'owner_user_id' => $this->user->id,
            'region_id' => $this->region->id,
            'name' => 'Café de la Plaza',
            'status' => 'verified',
        ]);
    }

    public function test_viewer_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Un Minuto Para');
        $response->assertSee('Vender');
    }

    public function test_api_viewer_returns_current_active_slot_and_remaining_seconds(): void
    {
        Carbon::setTestNow($now = Carbon::create(2026, 10, 6, 12, 0, 0));

        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Café 2x1',
            'destination_url' => 'https://cafe.com/comprar',
            'content_type' => 'image',
            'status' => 'active',
        ]);

        Slot::create([
            'region_id' => $this->region->id,
            'advertisement_id' => $ad->id,
            'starts_at' => $now->copy()->subSeconds(20),
            'ends_at' => $now->copy()->addSeconds(40),
        ]);

        $response = $this->getJson("/api/viewer/{$this->region->slug}");

        $response->assertStatus(200);
        $response->assertJson([
            'has_ad' => true,
            'advertisement' => [
                'id' => $ad->id,
                'title' => 'Café 2x1',
                'business_name' => 'Café de la Plaza',
            ],
            'slot' => [
                'remaining_seconds' => 40,
            ],
        ]);

        Carbon::setTestNow();
    }

    public function test_anonymous_click_registers_metric_and_redirects(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Promo Especial',
            'destination_url' => 'https://cafe.com/oferta',
            'content_type' => 'image',
            'status' => 'active',
        ]);

        $response = $this->get("/ads/{$ad->id}/click/{$this->region->id}");

        $response->assertRedirect('https://cafe.com/oferta');
        $this->assertDatabaseHas('ad_clicks', [
            'advertisement_id' => $ad->id,
            'region_id' => $this->region->id,
        ]);
    }

    public function test_anonymous_impression_registers_metric(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Promo Impresion',
            'destination_url' => 'https://cafe.com/oferta',
            'content_type' => 'image',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/ads/impression', [
            'advertisement_id' => $ad->id,
            'region_id' => $this->region->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ad_impressions', [
            'advertisement_id' => $ad->id,
            'region_id' => $this->region->id,
        ]);
    }
}
