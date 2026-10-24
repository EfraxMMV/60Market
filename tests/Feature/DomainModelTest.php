<?php

namespace Tests\Feature;

use App\Models\AdClick;
use App\Models\AdImpression;
use App\Models\Advertisement;
use App\Models\Business;
use App\Models\Region;
use App\Models\Slot;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_belongs_to_owner_user_and_has_advertisements(): void
    {
        $user = User::create([
            'name' => 'Empresario Local',
            'email' => 'empresa@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $region = Region::create([
            'name' => 'Colima',
            'state' => 'Colima',
            'slug' => 'colima',
            'is_active' => true,
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'region_id' => $region->id,
            'name' => 'Café Central',
            'description' => 'Café de especialidad',
            'status' => 'active',
        ]);

        $ad = Advertisement::create([
            'business_id' => $business->id,
            'region_id' => $region->id,
            'title' => '2x1 en Americano',
            'destination_url' => 'https://wa.me/523121234567',
            'content_type' => 'image',
            'status' => 'draft',
            'moderation_status' => 'pending',
        ]);

        $this->assertEquals($user->id, $business->owner->id);
        $this->assertCount(1, $user->businesses);
        $this->assertCount(1, $business->advertisements);
        $this->assertEquals('Café Central', $ad->business->name);
    }

    public function test_slot_invariant_duration_is_exactly_60_seconds(): void
    {
        $region = Region::create([
            'name' => 'Manzanillo',
            'state' => 'Colima',
            'slug' => 'manzanillo',
        ]);

        $user = User::create([
            'name' => 'Negocio Puerto',
            'email' => 'puerto@example.com',
            'password' => bcrypt('secret'),
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'region_id' => $region->id,
            'name' => 'Mariscos El Faro',
        ]);

        $ad = Advertisement::create([
            'business_id' => $business->id,
            'region_id' => $region->id,
            'title' => 'Ceviche especial',
            'destination_url' => 'https://elfaro.com/menu',
            'content_type' => 'image',
        ]);

        $startsAt = Carbon::parse('2026-10-06 12:00:00');

        $slot = Slot::create([
            'region_id' => $region->id,
            'advertisement_id' => $ad->id,
            'starts_at' => $startsAt,
        ]);

        $this->assertEquals(60, Slot::DURATION_SECONDS);
        $this->assertEquals('2026-10-06 12:01:00', $slot->ends_at->toDateTimeString());
        $this->assertEquals(60, $slot->starts_at->diffInSeconds($slot->ends_at));
    }

    public function test_slot_invariant_maximum_one_slot_per_advertisement(): void
    {
        $region = Region::create([
            'name' => 'Villa de Álvarez',
            'state' => 'Colima',
            'slug' => 'villa-de-alvarez',
        ]);

        $user = User::create([
            'name' => 'Tacos Don Pepe',
            'email' => 'pepe@example.com',
            'password' => bcrypt('secret'),
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'region_id' => $region->id,
            'name' => 'Taquería Don Pepe',
        ]);

        $ad = Advertisement::create([
            'business_id' => $business->id,
            'region_id' => $region->id,
            'title' => 'Tacos al pastor 5x4',
            'destination_url' => 'https://tacosdonpepe.com',
            'content_type' => 'image',
        ]);

        Slot::create([
            'region_id' => $region->id,
            'advertisement_id' => $ad->id,
            'starts_at' => now(),
        ]);

        // Intento de asignar un segundo slot al mismo anuncio debe arrojar violación de unicidad
        $this->expectException(UniqueConstraintViolationException::class);

        Slot::create([
            'region_id' => $region->id,
            'advertisement_id' => $ad->id,
            'starts_at' => now()->addMinutes(2),
        ]);
    }

    public function test_slot_active_resolution_and_mid_slot_entry_does_not_restart_time(): void
    {
        $region = Region::create([
            'name' => 'Guadalajara',
            'state' => 'Jalisco',
            'slug' => 'gdl',
        ]);

        $user = User::create([
            'name' => 'Zapatería GDL',
            'email' => 'zapatos@example.com',
            'password' => bcrypt('secret'),
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'region_id' => $region->id,
            'name' => 'Calzado Tapatío',
        ]);

        $ad = Advertisement::create([
            'business_id' => $business->id,
            'region_id' => $region->id,
            'title' => 'Botas de piel',
            'destination_url' => 'https://calzadotapatio.mx',
            'content_type' => 'image',
        ]);

        $start = Carbon::parse('2026-10-06 18:00:00');
        $slot = Slot::create([
            'region_id' => $region->id,
            'advertisement_id' => $ad->id,
            'starts_at' => $start,
        ]);

        // Usuario entra a los 23 segundos de iniciado el slot
        $entryTime = Carbon::parse('2026-10-06 18:00:23');

        $activeSlot = Slot::query()->forRegion($region->id)->active($entryTime)->first();

        $this->assertNotNull($activeSlot);
        $this->assertEquals($slot->id, $activeSlot->id);

        // El tiempo restante calculado por el servidor son 37 segundos, no 60
        $remainingSeconds = $activeSlot->remainingSeconds($entryTime);
        $this->assertEquals(37, $remainingSeconds);
    }

    public function test_regions_have_independent_slots_at_the_same_timestamp(): void
    {
        $regionColima = Region::create(['name' => 'Colima', 'state' => 'Colima', 'slug' => 'col']);
        $regionGDL = Region::create(['name' => 'Guadalajara', 'state' => 'Jalisco', 'slug' => 'gdl-2']);

        $user = User::create(['name' => 'Cadena Regional', 'email' => 'cadena@example.com', 'password' => bcrypt('secret')]);

        $bizColima = Business::create(['owner_user_id' => $user->id, 'region_id' => $regionColima->id, 'name' => 'Sucursal Colima']);
        $bizGDL = Business::create(['owner_user_id' => $user->id, 'region_id' => $regionGDL->id, 'name' => 'Sucursal GDL']);

        $adColima = Advertisement::create(['business_id' => $bizColima->id, 'region_id' => $regionColima->id, 'title' => 'Ad Colima', 'destination_url' => 'https://a.com', 'content_type' => 'image']);
        $adGDL = Advertisement::create(['business_id' => $bizGDL->id, 'region_id' => $regionGDL->id, 'title' => 'Ad GDL', 'destination_url' => 'https://b.com', 'content_type' => 'image']);

        $sameTimestamp = Carbon::parse('2026-10-06 20:00:00');

        $slotColima = Slot::create(['region_id' => $regionColima->id, 'advertisement_id' => $adColima->id, 'starts_at' => $sameTimestamp]);
        $slotGDL = Slot::create(['region_id' => $regionGDL->id, 'advertisement_id' => $adGDL->id, 'starts_at' => $sameTimestamp]);

        $this->assertNotNull($slotColima);
        $this->assertNotNull($slotGDL);
        $this->assertEquals($slotColima->starts_at, $slotGDL->starts_at);
        $this->assertNotEquals($slotColima->region_id, $slotGDL->region_id);
    }

    public function test_tags_categorization_and_anonymous_analytics_without_buyer_user_id(): void
    {
        $region = Region::create(['name' => 'Colima', 'state' => 'Colima', 'slug' => 'col-3']);
        $user = User::create(['name' => 'Empresa', 'email' => 'e3@example.com', 'password' => bcrypt('secret')]);
        $biz = Business::create(['owner_user_id' => $user->id, 'region_id' => $region->id, 'name' => 'Boutique Belleza']);
        $ad = Advertisement::create(['business_id' => $biz->id, 'region_id' => $region->id, 'title' => 'Labial mate', 'destination_url' => 'https://belleza.com', 'content_type' => 'image']);

        $tagBelleza = Tag::create(['name' => 'Belleza', 'slug' => 'belleza']);
        $tagCosmeticos = Tag::create(['name' => 'Cosméticos', 'slug' => 'cosmeticos']);

        $ad->tags()->attach([$tagBelleza->id, $tagCosmeticos->id]);

        $this->assertCount(2, $ad->fresh()->tags);

        // Registro de métricas anónimas: se comprueba que se registran asociadas a ad y region, sin comprador user_id
        $impression = AdImpression::create(['advertisement_id' => $ad->id, 'region_id' => $region->id]);
        $click = AdClick::create(['advertisement_id' => $ad->id, 'region_id' => $region->id]);

        $this->assertNotNull($impression->id);
        $this->assertNotNull($click->id);
        $this->assertFalse(array_key_exists('user_id', $impression->toArray()));
        $this->assertFalse(array_key_exists('user_id', $click->toArray()));
    }
}
