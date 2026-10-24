<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'region_id',
        'title',
        'destination_url',
        'content_type',
        'status',
        'moderation_status',
        'moderation_reason',
        'generated_html',
        'requires_ai_recharge',
        'ai_charged',
    ];

    protected function casts(): array
    {
        return [
            'requires_ai_recharge' => 'boolean',
            'ai_charged' => 'boolean',
        ];
    }

    /**
     * Calcula el desglose de precios para el slot y servicios asociados.
     */
    public function calculatePricing(): array
    {
        $slotPrice = 100.00;
        $aiBasePrice = ($this->content_type === 'html') ? 50.00 : 0.00;
        $aiRechargePrice = ($this->content_type === 'html' && $this->requires_ai_recharge) ? 50.00 : 0.00;

        $total = $slotPrice + $aiBasePrice + $aiRechargePrice;

        return [
            'slot_price' => $slotPrice,
            'ai_base_price' => $aiBasePrice,
            'ai_recharge_price' => $aiRechargePrice,
            'requires_ai_recharge' => $this->requires_ai_recharge,
            'total' => $total,
            'currency' => 'MXN',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Máximo un slot por anuncio (Invariante D04, D08).
     */
    public function slot(): HasOne
    {
        return $this->hasOne(Slot::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(AdvertisementAsset::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'advertisement_tag');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(AdImpression::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }
}
