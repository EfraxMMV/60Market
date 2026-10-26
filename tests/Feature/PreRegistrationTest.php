<?php

namespace Tests\Feature;

use App\Models\PreRegistration;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Módulo "Avisarme del lanzamiento": captura (POST /pre-register),
 * persistencia, validación y panel/exportación restringidos a ADMIN_EMAILS.
 */
class PreRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Region $region;

    protected function setUp(): void
    {
        parent::setUp();

        $this->region = Region::create([
            'name' => 'Colima',
            'slug' => 'colima',
            'state' => 'Colima',
            'is_active' => true,
        ]);
    }

    // ─── Captura ──────────────────────────────────────────────────────────────

    public function test_email_pre_registration_is_stored_and_returns_201(): void
    {
        Log::spy();

        $response = $this->postJson('/pre-register', [
            'contact' => '  Cliente@Ejemplo.COM ',
            'region_id' => $this->region->id,
            'user_type' => 'shopper',
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true, 'already_registered' => false]);

        // Persistido y normalizado a minúsculas sin espacios
        $this->assertDatabaseHas('pre_registrations', [
            'contact' => 'cliente@ejemplo.com',
            'region_id' => $this->region->id,
            'user_type' => 'shopper',
        ]);

        Log::shouldHaveReceived('info')->withArgs(fn ($msg) => $msg === 'pre_registration.created')->once();
    }

    public function test_phone_pre_registration_is_normalized(): void
    {
        $this->postJson('/pre-register', [
            'contact' => '+52 (312) 123-4567',
            'region_id' => $this->region->id,
            'user_type' => 'merchant',
        ])->assertCreated();

        $this->assertDatabaseHas('pre_registrations', [
            'contact' => '+523121234567',
            'user_type' => 'merchant',
        ]);
    }

    public function test_duplicate_contact_in_same_region_is_not_stored_twice(): void
    {
        $payload = ['contact' => 'repetido@ejemplo.com', 'region_id' => $this->region->id];

        $this->postJson('/pre-register', $payload)->assertCreated();
        $this->postJson('/pre-register', $payload)
            ->assertOk()
            ->assertJson(['success' => true, 'already_registered' => true]);

        $this->assertSame(1, PreRegistration::count());
    }

    // ─── Validación ───────────────────────────────────────────────────────────

    public function test_contact_is_required(): void
    {
        $this->postJson('/pre-register', ['region_id' => $this->region->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contact');

        $this->assertDatabaseCount('pre_registrations', 0);
    }

    public function test_contact_must_be_valid_email_or_phone(): void
    {
        foreach (['no-es-contacto', '12345', 'correo@', '+52 abc 123'] as $invalid) {
            $this->postJson('/pre-register', ['contact' => $invalid, 'region_id' => $this->region->id])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('contact');
        }

        $this->assertDatabaseCount('pre_registrations', 0);
    }

    public function test_region_must_exist(): void
    {
        $this->postJson('/pre-register', ['contact' => 'a@ejemplo.com', 'region_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('region_id');
    }

    public function test_user_type_must_be_shopper_or_merchant(): void
    {
        $this->postJson('/pre-register', [
            'contact' => 'a@ejemplo.com',
            'region_id' => $this->region->id,
            'user_type' => 'admin',
        ])->assertUnprocessable()->assertJsonValidationErrors('user_type');
    }

    // ─── Panel y exportación ──────────────────────────────────────────────────

    public function test_dashboard_is_forbidden_for_non_admin_users(): void
    {
        config(['app.admin_emails' => ['admin@60market.mx']]);
        $merchant = User::factory()->create(['email' => 'comercio@ejemplo.com']);

        $this->actingAs($merchant)->get('/dashboard/pre-registros')->assertForbidden();
        $this->actingAs($merchant)->get('/dashboard/pre-registros/export')->assertForbidden();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard/pre-registros')->assertRedirect('/login');
    }

    public function test_admin_can_list_and_filter_by_region(): void
    {
        config(['app.admin_emails' => ['admin@60market.mx']]);
        $admin = User::factory()->create(['email' => 'Admin@60market.mx']);
        $otherRegion = Region::create(['name' => 'Manzanillo', 'slug' => 'manzanillo', 'state' => 'Colima', 'is_active' => true]);

        PreRegistration::create(['contact' => 'colima@ejemplo.com', 'region_id' => $this->region->id, 'user_type' => 'shopper']);
        PreRegistration::create(['contact' => 'manzanillo@ejemplo.com', 'region_id' => $otherRegion->id, 'user_type' => 'merchant']);

        $this->actingAs($admin)->get('/dashboard/pre-registros')
            ->assertOk()
            ->assertSee('colima@ejemplo.com')
            ->assertSee('manzanillo@ejemplo.com');

        $this->actingAs($admin)->get('/dashboard/pre-registros?region_id=' . $otherRegion->id)
            ->assertOk()
            ->assertSee('manzanillo@ejemplo.com')
            ->assertDontSee('colima@ejemplo.com');
    }

    public function test_admin_can_filter_by_date_range(): void
    {
        config(['app.admin_emails' => ['admin@60market.mx']]);
        $admin = User::factory()->create(['email' => 'admin@60market.mx']);

        $old = PreRegistration::create(['contact' => 'viejo@ejemplo.com', 'region_id' => $this->region->id]);
        $old->forceFill(['created_at' => '2026-01-10 10:00:00'])->save();
        PreRegistration::create(['contact' => 'nuevo@ejemplo.com', 'region_id' => $this->region->id]);

        $this->actingAs($admin)->get('/dashboard/pre-registros?from=' . now()->toDateString())
            ->assertOk()
            ->assertSee('nuevo@ejemplo.com')
            ->assertDontSee('viejo@ejemplo.com');
    }

    public function test_admin_can_export_csv(): void
    {
        config(['app.admin_emails' => ['admin@60market.mx']]);
        $admin = User::factory()->create(['email' => 'admin@60market.mx']);

        PreRegistration::create(['contact' => 'csv@ejemplo.com', 'region_id' => $this->region->id, 'user_type' => 'shopper']);
        PreRegistration::create(['contact' => '+523121234567', 'region_id' => $this->region->id, 'user_type' => 'merchant']);

        $response = $this->actingAs($admin)->get('/dashboard/pre-registros/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('id,contacto,tipo_contacto,tipo_usuario,region,estado,fecha_registro', $csv);
        $this->assertStringContainsString('csv@ejemplo.com,email,shopper,Colima', $csv);
        // Los teléfonos con '+' no se alteran por la protección anti-fórmulas
        $this->assertStringContainsString('+523121234567,telefono,merchant', $csv);
    }
}
