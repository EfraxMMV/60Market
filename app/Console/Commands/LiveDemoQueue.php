<?php

namespace App\Console\Commands;

use App\Models\Advertisement;
use App\Models\AdvertisementAsset;
use App\Models\Business;
use App\Models\Region;
use App\Models\Slot;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class LiveDemoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '60market:live-demo {region=colima}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Programa la demostración en vivo con los dos anuncios oficiales de 60Market (Video IA y presentación de Un Minuto Para Vender)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $regionSlug = $this->argument('region');
        $region = Region::where('slug', $regionSlug)->first();

        if (! $region) {
            $region = Region::first();
        }

        if (! $region) {
            $this->error("No se encontró la región '{$regionSlug}'. Asegúrate de que existan regiones registradas.");
            return Command::FAILURE;
        }

        // 1. Obtener o crear el usuario y empresa '60Market'
        $user = User::firstOrCreate(
            ['email' => 'admin@60market.mx'],
            [
                'name' => '60Market Admin',
                'password' => bcrypt('secret123'),
            ]
        );

        $business = Business::firstOrCreate(
            ['name' => '60Market'],
            [
                'owner_user_id' => $user->id,
                'region_id' => $region->id,
                'description' => 'Plataforma oficial 60Market: Un Minuto Para Vender',
                'status' => 'active',
            ]
        );

        // 2. Comprobar y copiar los archivos desde contenido_prueba/ hacia storage público
        $sourceDir = base_path('contenido_prueba');
        $videoSource = $sourceDir . '/60marketaitool.mp4';
        $imageSource = $sourceDir . '/Unminutoparavender.png';

        if (! File::exists($videoSource) || ! File::exists($imageSource)) {
            $this->error("No se encontraron los archivos requeridos en '{$sourceDir}'.");
            $this->line("Asegúrate de que '60marketaitool.mp4' y 'Unminutoparavender.png' estén en la carpeta contenido_prueba/.");
            return Command::FAILURE;
        }

        $videoStorageDir = storage_path('app/public/ads/videos');
        $imageStorageDir = storage_path('app/public/ads/images');
        File::ensureDirectoryExists($videoStorageDir);
        File::ensureDirectoryExists($imageStorageDir);

        $videoDest = $videoStorageDir . '/60marketaitool.mp4';
        $imageDest = $imageStorageDir . '/Unminutoparavender.png';

        File::copy($videoSource, $videoDest);
        File::copy($imageSource, $imageDest);

        $this->info("Archivos multimedia transferidos a storage público exitosamente.");

        // 3. Crear Anuncio 1: Video de la herramienta de IA
        $adVideo = Advertisement::updateOrCreate(
            [
                'business_id' => $business->id,
                'title' => 'Herramienta de Generación con IA — Compra tu Slot y Prueba la Herramienta',
            ],
            [
                'region_id' => $region->id,
                'destination_url' => url('/ads/create'),
                'content_type' => 'video',
                'status' => 'active',
                'moderation_status' => 'approved',
                'generated_html' => null,
            ]
        );

        AdvertisementAsset::updateOrCreate(
            [
                'advertisement_id' => $adVideo->id,
                'type' => 'video',
            ],
            [
                'storage_key' => 'ads/videos/60marketaitool.mp4',
                'mime_type' => 'video/mp4',
                'file_size' => File::size($videoDest),
                'sort_order' => 1,
            ]
        );

        $videoTags = ['IA', 'Herramienta IA', '60Market', 'Compra tu Slot'];
        $videoTagIds = [];
        foreach ($videoTags as $tName) {
            $tag = Tag::firstOrCreate(['slug' => Str::slug($tName)], ['name' => $tName]);
            $videoTagIds[] = $tag->id;
        }
        $adVideo->tags()->sync($videoTagIds);

        // 4. Crear Anuncio 2: Imagen de presentación / invitación Un Minuto Para Vender
        $adImage = Advertisement::updateOrCreate(
            [
                'business_id' => $business->id,
                'title' => 'Un Minuto Para Vender — La Solución Radical para tu Negocio',
            ],
            [
                'region_id' => $region->id,
                'destination_url' => url('/register'),
                'content_type' => 'image',
                'status' => 'scheduled',
                'moderation_status' => 'approved',
                'generated_html' => null,
            ]
        );

        AdvertisementAsset::updateOrCreate(
            [
                'advertisement_id' => $adImage->id,
                'type' => 'image',
            ],
            [
                'storage_key' => 'ads/images/Unminutoparavender.png',
                'mime_type' => 'image/png',
                'file_size' => File::size($imageDest),
                'sort_order' => 1,
            ]
        );

        $imageTags = ['UnMinutoParaVender', '60Market', 'Emprendedores', 'Comercio Local'];
        $imageTagIds = [];
        foreach ($imageTags as $tName) {
            $tag = Tag::firstOrCreate(['slug' => Str::slug($tName)], ['name' => $tName]);
            $imageTagIds[] = $tag->id;
        }
        $adImage->tags()->sync($imageTagIds);

        // 5. Reiniciar y programar los slots secuenciales de 60 segundos
        // Limpiamos los slots previos de la región para que corra únicamente esta demostración
        Slot::where('region_id', $region->id)->delete();

        $now = now();
        $slot1Start = $now->copy();
        $slot1End = $slot1Start->copy()->addSeconds(60);

        $slot1 = Slot::create([
            'advertisement_id' => $adVideo->id,
            'region_id' => $region->id,
            'starts_at' => $slot1Start,
            'ends_at' => $slot1End,
        ]);

        $slot2Start = $slot1End->copy();
        $slot2End = $slot2Start->copy()->addSeconds(60);

        $slot2 = Slot::create([
            'advertisement_id' => $adImage->id,
            'region_id' => $region->id,
            'starts_at' => $slot2Start,
            'ends_at' => $slot2End,
        ]);

        // Sincronizar con Redis
        try {
            $redisKey = "region:{$region->id}:fifo_slots";
            Redis::del($redisKey);
            Redis::rpush($redisKey, $slot1->id);
            Redis::rpush($redisKey, $slot2->id);
        } catch (\Throwable $e) {
            // Ignorar si Redis no está disponible
        }

        $this->info("==================================================================");
        $this->info("¡DEMOSTRACIÓN EN VIVO PROGRAMADA PARA: {$region->name} ({$region->state})!");
        $this->info("Empresa emisora: 60Market");
        $this->info("==================================================================");
        $this->line("1. 🎬 [VIDEO EN VIVO AHORA] ({$slot1Start->format('H:i:s')} -> {$slot1End->format('H:i:s')} - 60s):");
        $this->line("   Título: {$adVideo->title}");
        $this->line("   Archivo: 60marketaitool.mp4 (Herramienta de Generación con IA)");
        $this->line("   Enlace Comprar: {$adVideo->destination_url}");
        $this->line("");
        $this->line("2. 🖼️ [IMAGEN EN COLA] ({$slot2Start->format('H:i:s')} -> {$slot2End->format('H:i:s')} - 60s):");
        $this->line("   Título: {$adImage->title}");
        $this->line("   Archivo: Unminutoparavender.png (Presentación de Un Minuto Para Vender)");
        $this->line("   Enlace Comprar: {$adImage->destination_url}");
        $this->info("==================================================================");
        $this->info("Abre en tu navegador: http://localhost:8000 (o http://localhost:8000/r/{$region->slug})");

        return Command::SUCCESS;
    }
}
