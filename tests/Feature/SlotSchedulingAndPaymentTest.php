<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Business;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotSchedulingAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;
    protected Region $region;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->region = Region::create([
            'name' => 'Colima Centro',
            'state' => 'Colima',
            'slug' => 'colima-centro',
            'is_active' => true,
        ]);

        $this->business = Business::create([
            'owner_user_id' => $this->user->id,
            'region_id' => $this->region->id,
            'name' => 'Café Don Colima',
            'status' => 'active',
        ]);
    }

    public function test_create_ad_with_buy_slot_action_redirects_directly_to_checkout(): void
    {
        $response = $this->actingAs($this->user)->post(route('ads.store'), [
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Frappé 2x1 Martes',
            'destination_url' => 'https://cafecolima.com/oferta',
            'content_type' => 'image',
            'submit_action' => 'buy_slot',
        ]);

        $ad = Advertisement::where('title', 'Frappé 2x1 Martes')->first();
        $this->assertNotNull($ad);
        $this->assertEquals('draft', $ad->status);

        $response->assertRedirect(route('ads.checkout', $ad));
    }

    public function test_editing_saved_html_draft_triggers_ai_recharge_flag(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio Inteligente',
            'destination_url' => 'https://cafecolima.com',
            'content_type' => 'html',
            'status' => 'draft',
            'generated_html' => '<div>Original</div>',
            'requires_ai_recharge' => false,
        ]);

        // Al editar y guardar tras haber guardado el borrador previo:
        $response = $this->actingAs($this->user)->put(route('ads.update', $ad), [
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio Inteligente Renovado',
            'destination_url' => 'https://cafecolima.com',
            'content_type' => 'html',
            'generated_html' => '<div>Nuevo Diseño Regenerado</div>',
            'submit_action' => 'buy_slot',
        ]);

        $ad->refresh();
        $this->assertTrue($ad->requires_ai_recharge);
        $pricing = $ad->calculatePricing();
        $this->assertEquals(100.00, $pricing['slot_price']);
        $this->assertEquals(50.00, $pricing['ai_base_price']);
        $this->assertEquals(50.00, $pricing['ai_recharge_price']);
        $this->assertEquals(200.00, $pricing['total']);

        $response->assertRedirect(route('ads.checkout', $ad));
    }

    public function test_mercadopago_sandbox_checkout_view_displays_pricing_and_queue(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Oferta Especial',
            'destination_url' => 'https://cafecolima.com',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->get(route('ads.checkout', $ad));

        $response->assertOk();
        $response->assertSee('Mercado Pago');
        $response->assertSee('Sandbox de Pruebas');
        $response->assertSee('Slot Publicitario (60 segundos)');
        $response->assertSee('$100.00 MXN');
    }

    public function test_mercadopago_sandbox_successful_payment_allocates_atomic_fifo_slot_and_activates_ad(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Promo En Vivo',
            'destination_url' => 'https://cafecolima.com/live',
            'content_type' => 'html',
            'generated_html' => '<div style="color:red;">Oferta Viva</div>',
            'status' => 'draft',
            'moderation_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('ads.checkout.process', $ad), [
            'simulated_outcome' => 'approved',
        ]);

        $response->assertRedirect(route('ads.checkout.success', $ad));

        // 1. Pago registrado
        $payment = Payment::where('advertisement_id', $ad->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('mercadopago_sandbox', $payment->provider);
        $this->assertEquals(150.00, (float) $payment->amount);

        // 2. Anuncio programado y aprobado
        $ad->refresh();
        $this->assertContains($ad->status, ['active', 'scheduled']);
        $this->assertEquals('approved', $ad->moderation_status);
        $this->assertTrue($ad->ai_charged);
        $this->assertFalse($ad->requires_ai_recharge);

        // 3. Slot de 60 segundos creado atómicamente
        $this->assertNotNull($ad->slot);
        $this->assertEquals(60, $ad->slot->starts_at->diffInSeconds($ad->slot->ends_at));
        $this->assertEquals($this->region->id, $ad->slot->region_id);
    }

    public function test_fifo_queue_guarantees_non_overlapping_order(): void
    {
        // Primer anuncio
        $ad1 = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Primer Anuncio',
            'destination_url' => 'https://cafecolima.com/1',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)->post(route('ads.checkout.process', $ad1), [
            'simulated_outcome' => 'approved',
        ]);

        $ad1->refresh();
        $slot1 = $ad1->slot;

        // Segundo anuncio
        $ad2 = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Segundo Anuncio',
            'destination_url' => 'https://cafecolima.com/2',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)->post(route('ads.checkout.process', $ad2), [
            'simulated_outcome' => 'approved',
        ]);

        $ad2->refresh();
        $slot2 = $ad2->slot;

        // Invariante D01, D03, D04: El segundo slot inicia exactamente cuando termina el primero
        $this->assertEquals(
            $slot1->ends_at->toIso8601String(),
            $slot2->starts_at->toIso8601String()
        );
        $this->assertEquals(60, $slot2->starts_at->diffInSeconds($slot2->ends_at));
    }

    public function test_after_successful_purchase_ad_is_immediately_visible_on_viewer(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Super Descuento Café',
            'destination_url' => 'https://cafecolima.com/compra',
            'content_type' => 'html',
            'generated_html' => '<h1>¡50% de Descuento!</h1>',
            'status' => 'draft',
        ]);

        // Comprar slot
        $this->actingAs($this->user)->post(route('ads.checkout.process', $ad), [
            'simulated_outcome' => 'approved',
        ]);

        // Consultar la página principal del visor
        $viewerResponse = $this->get(route('viewer.home'));
        $viewerResponse->assertOk();
        $viewerResponse->assertSee('Super Descuento Café');
        $viewerResponse->assertSee('Café Don Colima');

        // Consultar el endpoint API de sincronización del visor en tiempo real
        $apiResponse = $this->getJson(route('api.viewer.slot', $this->region->slug));
        $apiResponse->assertOk();
        $apiResponse->assertJson([
            'has_ad' => true,
            'advertisement' => [
                'title' => 'Super Descuento Café',
                'content_type' => 'html',
                'business_name' => 'Café Don Colima',
                'destination_url' => 'https://cafecolima.com/compra',
            ],
        ]);
    }

    public function test_simulated_payment_rejection_does_not_schedule_slot(): void
    {
        $ad = Advertisement::create([
            'business_id' => $this->business->id,
            'region_id' => $this->region->id,
            'title' => 'Anuncio Con Tarjeta Rechazada',
            'destination_url' => 'https://cafecolima.com',
            'content_type' => 'image',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->post(route('ads.checkout.process', $ad), [
            'simulated_outcome' => 'rejected',
        ]);

        $response->assertRedirect(route('ads.checkout', $ad));
        $response->assertSessionHasErrors('payment_error');

        $ad->refresh();
        $this->assertEquals('draft', $ad->status);
        $this->assertNull($ad->slot);

        $failedPayment = Payment::where('advertisement_id', $ad->id)->first();
        $this->assertNotNull($failedPayment);
        $this->assertEquals('failed', $failedPayment->status);
    }
}
