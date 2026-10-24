<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Region::create([
            'name' => 'Colima',
            'slug' => 'colima',
            'state' => 'Colima',
            'country_code' => 'MX',
            'timezone' => 'America/Mexico_City',
            'is_active' => true,
        ]);
    }

    public function test_guest_can_see_login_and_registration_screens(): void
    {
        $responseLogin = $this->get('/login');
        $responseLogin->assertStatus(200);
        $responseLogin->assertSee('Acceso para Anunciantes');

        $responseRegister = $this->get('/register');
        $responseRegister->assertStatus(200);
        $responseRegister->assertSee('Crea tu Cuenta Empresarial');
    }

    public function test_business_owner_can_register_and_creates_business_entity(): void
    {
        $region = Region::first();

        $response = $this->post('/register', [
            'name' => 'Ana López',
            'business_name' => 'Panadería El Trigo',
            'region_id' => $region->id,
            'email' => 'ana@eltrigo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'ana@eltrigo.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Ana López', $user->name);

        $business = $user->businesses()->first();
        $this->assertNotNull($business);
        $this->assertEquals('Panadería El Trigo', $business->name);
        $this->assertEquals($region->id, $business->region_id);
    }

    public function test_business_owner_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'contacto@cafe.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'contacto@cafe.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'contacto@cafe.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'contacto@cafe.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('viewer.home'));
        $this->assertGuest();
    }
}
