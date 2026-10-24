<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Slot extends Model
{
    use HasFactory;

    public const DURATION_SECONDS = 60;

    protected $fillable = [
        'region_id',
        'advertisement_id',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Slot $slot) {
            // Invariante D04: Cada slot dura exactamente 60 segundos
            if ($slot->starts_at && ! $slot->ends_at) {
                $slot->ends_at = Carbon::parse($slot->starts_at)->addSeconds(self::DURATION_SECONDS);
            }
        });
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    /**
     * Scope para consultar el slot activo en un instante dado (D04: starts_at <= ahora < ends_at).
     */
    public function scopeActive(Builder $query, ?CarbonInterface $time = null): Builder
    {
        $target = $time ?? now();

        return $query->where('starts_at', '<=', $target)
            ->where('ends_at', '>', $target);
    }

    /**
     * Scope para filtrar por región geográfica.
     */
    public function scopeForRegion(Builder $query, int $regionId): Builder
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Calcula los segundos restantes del slot respecto a un instante dado (D04).
     */
    public function remainingSeconds(?CarbonInterface $now = null): int
    {
        $current = $now ?? now();

        if ($current >= $this->ends_at) {
            return 0;
        }

        return (int) $current->diffInSeconds($this->ends_at);
    }
}
