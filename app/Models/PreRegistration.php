<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PreRegistration — contacto capturado desde el modal "Avisarme del lanzamiento".
 *
 * @property int         $id
 * @property string      $contact    Correo o teléfono normalizado
 * @property int|null    $region_id
 * @property string|null $user_type  'shopper' | 'merchant'
 */
class PreRegistration extends Model
{
    public const USER_TYPES = ['shopper', 'merchant'];

    protected $fillable = [
        'contact',
        'region_id',
        'user_type',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** Indica si el contacto es un correo (si no, es teléfono). */
    public function isEmail(): bool
    {
        return str_contains($this->contact, '@');
    }

    /**
     * Aplica los filtros del panel: from, to (Y-m-d), region_id, user_type.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['region_id'] ?? null, fn ($q, $id) => $q->where('region_id', $id))
            ->when($filters['user_type'] ?? null, fn ($q, $type) => $q->where('user_type', $type));
    }
}
