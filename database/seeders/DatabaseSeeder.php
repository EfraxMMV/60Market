<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\AdvertisementAsset;
use App\Models\Business;
use App\Models\Region;
use App\Models\Slot;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Población de datos de prueba controlados para el visor de 60Market.
     */
    public function run(): void
    {
        // 1. Regiones de prueba (D03, D05)
        $colima = Region::firstOrCreate(
            ['slug' => 'colima'],
            ['name' => 'Colima', 'state' => 'Colima', 'is_active' => true]
        );

        $gdl = Region::firstOrCreate(
            ['slug' => 'guadalajara'],
            ['name' => 'Guadalajara', 'state' => 'Jalisco', 'is_active' => true]
        );

        // 2. Usuario empresarial (D06)
        $owner = User::firstOrCreate(
            ['email' => 'contacto@cafeparroquia.mx'],
            [
                'name' => 'Don Manuel (Café Parroquia)',
                'password' => bcrypt('parroquia2026'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Negocio de prueba (D06, D08)
        $business = Business::firstOrCreate(
            ['owner_user_id' => $owner->id, 'name' => 'Café de la Parroquia'],
            [
                'region_id' => $colima->id,
                'description' => 'Tradicional café de especialidad y repostería artesanal en el centro.',
                'status' => 'active',
            ]
        );

        // 4. Catálogo controlado de tags (D05, D08)
        $tagCafe = Tag::firstOrCreate(['slug' => 'cafeteria'], ['name' => 'Cafetería', 'is_active' => true]);
        $tagDescuento = Tag::firstOrCreate(['slug' => 'promocion'], ['name' => 'Promoción', 'is_active' => true]);
        $tagAlimentos = Tag::firstOrCreate(['slug' => 'alimentos'], ['name' => 'Alimentos y Bebidas', 'is_active' => true]);

        // 5. Anuncios de prueba (D01, D07, D08)
        $ad1 = Advertisement::firstOrCreate(
            ['title' => '2x1 en Café Americano de Especialidad'],
            [
                'business_id' => $business->id,
                'region_id' => $colima->id,
                'destination_url' => 'https://wa.me/523121234567?text=Hola,%20vi%20el%20anuncio%20en%2060Market%20y%20quiero%20el%202x1%20en%20Americano',
                'content_type' => 'image',
                'status' => 'scheduled',
                'moderation_status' => 'approved',
            ]
        );
        $ad1->tags()->syncWithoutDetaching([$tagCafe->id, $tagDescuento->id]);

        AdvertisementAsset::firstOrCreate(
            ['advertisement_id' => $ad1->id, 'storage_key' => 'demo/cafe_americano.jpg'],
            ['type' => 'image', 'mime_type' => 'image/jpeg', 'sort_order' => 0]
        );

        $ad2 = Advertisement::firstOrCreate(
            ['title' => 'Rebanada de Pastel Gratis en consumo de $150'],
            [
                'business_id' => $business->id,
                'region_id' => $colima->id,
                'destination_url' => 'https://wa.me/523121234567?text=Quiero%20mi%20rebanada%20de%20pastel%20gratis',
                'content_type' => 'html',
                'generated_html' => '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;background:linear-gradient(135deg,#78350f,#b45309);color:white;padding:2rem;border-radius:1rem;">'
                    . '<h2 style="font-size:2rem;font-weight:bold;margin-bottom:1rem;">🍰 Rebanada de Pastel Gratis</h2>'
                    . '<p style="font-size:1.2rem;max-width:32rem;">En el consumo de $150 en barra. Válido mencionando este anuncio en los próximos 60 minutos.</p>'
                    . '<span style="margin-top:1.5rem;padding:0.5rem 1rem;background:white;color:#78350f;font-weight:bold;border-radius:9999px;">Código: PARROQUIA60</span>'
                    . '</div>',
                'status' => 'scheduled',
                'moderation_status' => 'approved',
            ]
        );
        $ad2->tags()->syncWithoutDetaching([$tagDescuento->id, $tagAlimentos->id]);

        $ad3 = Advertisement::firstOrCreate(
            ['title' => 'Bolsa de Café de Altura 500g a solo $160'],
            [
                'business_id' => $business->id,
                'region_id' => $colima->id,
                'destination_url' => 'https://wa.me/523121234567?text=Quiero%20apartar%20mi%20bolsa%20de%20cafe%20a%20$160',
                'content_type' => 'image',
                'status' => 'scheduled',
                'moderation_status' => 'approved',
            ]
        );
        $ad3->tags()->syncWithoutDetaching([$tagCafe->id]);

        // 6. Slots secuenciales en cola FIFO (D03, D04)
        // El anuncio 1 está en vivo actualmente (comenzó hace 15 segundos)
        /*
        $now = now();
        $start1 = $now->copy()->subSeconds(15);
        $end1 = $start1->copy()->addSeconds(60);

        Slot::updateOrCreate(
            ['advertisement_id' => $ad1->id],
            ['region_id' => $colima->id, 'starts_at' => $start1, 'ends_at' => $end1]
        );

        // El anuncio 2 entra al término exacto del anuncio 1
        $start2 = $end1->copy();
        $end2 = $start2->copy()->addSeconds(60);
        Slot::updateOrCreate(
            ['advertisement_id' => $ad2->id],
            ['region_id' => $colima->id, 'starts_at' => $start2, 'ends_at' => $end2]
        );

        // El anuncio 3 entra al término exacto del anuncio 2
        $start3 = $end2->copy();
        $end3 = $start3->copy()->addSeconds(60);
        Slot::updateOrCreate(
            ['advertisement_id' => $ad3->id],
            ['region_id' => $colima->id, 'starts_at' => $start3, 'ends_at' => $end3]
        );
        */
    }
}
