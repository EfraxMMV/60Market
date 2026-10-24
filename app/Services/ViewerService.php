<?php

namespace App\Services;

use App\Models\AdClick;
use App\Models\AdImpression;
use App\Models\Advertisement;
use App\Models\Region;
use App\Models\Slot;
use Carbon\CarbonInterface;

class ViewerService
{
    /**
     * Resuelve la región solicitada o la primera activa por defecto.
     */
    public function resolveRegion(?string $slug = null): ?Region
    {
        if ($slug) {
            $region = Region::where('slug', $slug)->where('is_active', true)->first();
            if ($region) {
                return $region;
            }
        }

        return Region::where('is_active', true)->orderBy('id')->first();
    }

    /**
     * Obtiene el estado y anuncio en vivo de una región en un instante temporal (D02, D04).
     */
    public function getActiveSlotData(Region $region, ?CarbonInterface $time = null): array
    {
        $now = $time ?? now();

        /** @var Slot|null $slot */
        $slot = Slot::query()
            ->forRegion($region->id)
            ->active($now)
            ->with(['advertisement.business', 'advertisement.assets', 'advertisement.tags'])
            ->first();

        if (! $slot || ! $slot->advertisement) {
            // Verificar si hay anuncios programados a futuro en la cola
            $nextSlot = Slot::query()
                ->forRegion($region->id)
                ->where('starts_at', '>', $now)
                ->orderBy('starts_at')
                ->first();

            return [
                'has_ad' => false,
                'region' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'slug' => $region->slug,
                    'state' => $region->state,
                ],
                'server_time' => $now->toIso8601String(),
                'next_slot_in_seconds' => $nextSlot ? (int) $now->diffInSeconds($nextSlot->starts_at) : null,
            ];
        }

        $ad = $slot->advertisement;
        $remainingSeconds = $slot->remainingSeconds($now);

        return [
            'has_ad' => true,
            'region' => [
                'id' => $region->id,
                'name' => $region->name,
                'slug' => $region->slug,
                'state' => $region->state,
            ],
            'slot' => [
                'id' => $slot->id,
                'starts_at' => $slot->starts_at->toIso8601String(),
                'ends_at' => $slot->ends_at->toIso8601String(),
                'duration_seconds' => Slot::DURATION_SECONDS,
                'remaining_seconds' => $remainingSeconds,
            ],
            'advertisement' => [
                'id' => $ad->id,
                'title' => $ad->title,
                'content_type' => $ad->content_type,
                'generated_html' => $ad->generated_html,
                'assets' => $ad->assets->map(fn ($asset) => [
                    'type' => $asset->type,
                    'storage_key' => $asset->storage_key,
                    'url' => asset('storage/' . $asset->storage_key),
                ]),
                'business_name' => $ad->business->name,
                'destination_url' => $ad->destination_url,
                'tags' => $ad->tags->pluck('name')->values(),
            ],
            'server_time' => $now->toIso8601String(),
        ];
    }

    /**
     * Registra un clic anónimo en el botón Comprar y retorna la URL destino (D01, D09).
     */
    public function recordClickAndGetDestination(Advertisement $advertisement, Region $region): string
    {
        AdClick::create([
            'advertisement_id' => $advertisement->id,
            'region_id' => $region->id,
            'created_at' => now(),
        ]);

        return $advertisement->destination_url;
    }

    /**
     * Registra una visualización anónima en vivo (D09).
     */
    public function recordImpression(Advertisement $advertisement, Region $region): void
    {
        AdImpression::create([
            'advertisement_id' => $advertisement->id,
            'region_id' => $region->id,
            'created_at' => now(),
        ]);
    }
}
