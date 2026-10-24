<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\Region;
use App\Models\Slot;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SlotSchedulerService
{
    /**
     * Asigna atómicamente un slot de 60 segundos al final de la cola FIFO de la región (D01, D03, D04, D08).
     *
     * Invariantes:
     * 1. Una única cola FIFO por región; máximo un anuncio activo por región e instante.
     * 2. ends_at = starts_at + 60 segundos y ausencia de solapamientos regionales.
     * 3. advertisement_id único en slots.
     * 4. Asignación atómica mediante transacción y bloqueo de fila (Pessimistic Locking).
     */
    public function scheduleSlot(Advertisement $advertisement, ?CarbonInterface $currentTime = null): array
    {
        $now = $currentTime ?? now();

        return DB::transaction(function () use ($advertisement, $now) {
            // Bloqueo pesimista sobre los slots de la región para garantizar asignación atómica sin carreras
            $lastSlot = Slot::query()
                ->where('region_id', $advertisement->region_id)
                ->where('ends_at', '>', $now)
                ->orderByDesc('ends_at')
                ->lockForUpdate()
                ->first();

            // Si hay un slot futuro o en curso, se coloca exactamente al final de ese slot;
            // si la cola está vacía, inicia inmediatamente en este instante.
            if ($lastSlot) {
                $startsAt = Carbon::parse($lastSlot->ends_at);
            } else {
                $startsAt = $now->copy();
            }

            $endsAt = $startsAt->copy()->addSeconds(Slot::DURATION_SECONDS);

            // Crear o actualizar el slot (garantiza la restricción de advertisement_id único)
            $slot = Slot::updateOrCreate(
                ['advertisement_id' => $advertisement->id],
                [
                    'region_id' => $advertisement->region_id,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]
            );

            // Actualizar estado del anuncio a 'scheduled' y moderación aprobada
            $isLiveImmediately = $startsAt->lessThanOrEqualTo($now) && $endsAt->greaterThan($now);
            $advertisement->update([
                'status' => $isLiveImmediately ? 'active' : 'scheduled',
                'moderation_status' => 'approved',
                'ai_charged' => true,
                'requires_ai_recharge' => false,
            ]);

            // Sincronización con Redis para encolado de alta velocidad y notificaciones
            try {
                $redisQueueKey = "region:{$advertisement->region_id}:fifo_slots";
                Redis::rpush($redisQueueKey, $slot->id);
            } catch (\Throwable $e) {
                Log::warning('Redis queue sync skipped: ' . $e->getMessage());
            }

            // Calcular posición en la cola
            $queuePosition = Slot::query()
                ->where('region_id', $advertisement->region_id)
                ->where('starts_at', '<=', $startsAt)
                ->where('ends_at', '>', $now)
                ->count();

            $waitInSeconds = max(0, (int) $now->diffInSeconds($startsAt, false));

            return [
                'slot' => $slot,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'is_live_immediately' => $isLiveImmediately,
                'queue_position' => $queuePosition,
                'wait_in_seconds' => $waitInSeconds,
            ];
        });
    }

    /**
     * Obtiene el estado actual de la cola regional (cuántos slots están en espera).
     */
    public function getRegionQueueStatus(Region $region, ?CarbonInterface $currentTime = null): array
    {
        $now = $currentTime ?? now();

        $activeSlot = Slot::query()
            ->forRegion($region->id)
            ->active($now)
            ->with('advertisement')
            ->first();

        $futureSlotsCount = Slot::query()
            ->forRegion($region->id)
            ->where('starts_at', '>', $now)
            ->count();

        $lastSlot = Slot::query()
            ->forRegion($region->id)
            ->where('ends_at', '>', $now)
            ->orderByDesc('ends_at')
            ->first();

        $estimatedWaitSeconds = 0;
        if ($lastSlot) {
            $estimatedWaitSeconds = max(0, (int) $now->diffInSeconds($lastSlot->ends_at));
        }

        return [
            'has_active_slot' => (bool) $activeSlot,
            'active_slot_remaining_seconds' => $activeSlot ? $activeSlot->remainingSeconds($now) : 0,
            'queued_slots_count' => $futureSlotsCount,
            'total_pending_slots' => ($activeSlot ? 1 : 0) + $futureSlotsCount,
            'estimated_wait_seconds' => $estimatedWaitSeconds,
        ];
    }
}
