<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo CampaignLead — Incremento 5: Módulo de Pre-registro.
 *
 * Registra visitantes (compradores y comercios) interesados en la plataforma
 * antes de su lanzamiento. El comprador es anónimo (D02): email y phone son
 * opcionales. region_id es nullable hasta que P03 esté resuelto.
 *
 * @property int         $id
 * @property string|null $email
 * @property string|null $phone
 * @property string      $user_type  'merchant' | 'shopper'
 * @property int|null    $region_id
 */
class CampaignLead extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'user_type',
        'region_id',
    ];

    protected function casts(): array
    {
        return [
            'user_type' => 'string',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
