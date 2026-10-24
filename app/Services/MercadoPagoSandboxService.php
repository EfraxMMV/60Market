<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MercadoPagoSandboxService
{
    public function __construct(
        protected SlotSchedulerService $slotSchedulerService
    ) {}

    /**
     * Prepara la orden de compra y preferencia del Sandbox de Mercado Pago / Mercado Libre.
     */
    public function createSandboxPreference(Advertisement $advertisement): array
    {
        $pricing = $advertisement->calculatePricing();

        $items = [
            [
                'id' => 'SLOT-60S-' . $advertisement->region_id,
                'title' => 'Espacio Publicitario de 60 Segundos (Región: ' . $advertisement->region->name . ')',
                'description' => 'Slot publicitario en cola FIFO local de 60 segundos con botón de compra integrado.',
                'quantity' => 1,
                'unit_price' => $pricing['slot_price'],
                'currency_id' => 'MXN',
            ]
        ];

        if ($pricing['ai_base_price'] > 0) {
            $items[] = [
                'id' => 'AI-HTML-GEN',
                'title' => 'Generación de Creativo HTML con IA (60Market AI)',
                'description' => 'Diseño inteligente de alto rendimiento para el anuncio.',
                'quantity' => 1,
                'unit_price' => $pricing['ai_base_price'],
                'currency_id' => 'MXN',
            ];
        }

        if ($pricing['ai_recharge_price'] > 0) {
            $items[] = [
                'id' => 'AI-HTML-REEDIT',
                'title' => 'Recargo por Reedición con IA tras Borrador Guardado',
                'description' => 'Regeneración o re-diseño solicitado tras haber guardado el borrador previo.',
                'quantity' => 1,
                'unit_price' => $pricing['ai_recharge_price'],
                'currency_id' => 'MXN',
            ];
        }

        $preferenceId = 'PREF-MP-' . strtoupper(Str::random(16));

        return [
            'preference_id' => $preferenceId,
            'items' => $items,
            'pricing' => $pricing,
            'sandbox_mode' => true,
            'advertisement_id' => $advertisement->id,
            'region_name' => $advertisement->region->name,
        ];
    }

    /**
     * Procesa la simulación de cobro en el Sandbox de Mercado Pago / Mercado Libre.
     */
    public function processSandboxPayment(Advertisement $advertisement, string $simulatedOutcome = 'approved'): array
    {
        $pricing = $advertisement->calculatePricing();
        $totalAmount = $pricing['total'];

        if ($simulatedOutcome !== 'approved') {
            // Registrar intento fallido
            Payment::create([
                'advertisement_id' => $advertisement->id,
                'provider' => 'mercadopago_sandbox',
                'provider_payment_id' => 'MP-FAIL-' . strtoupper(Str::random(10)),
                'amount' => $totalAmount,
                'currency' => 'MXN',
                'status' => 'failed',
                'paid_at' => null,
            ]);

            return [
                'success' => false,
                'status' => 'rejected',
                'message' => 'El pago fue rechazado en el Sandbox de Mercado Pago (Fondos insuficientes o tarjeta denegada en simulación).',
            ];
        }

        return DB::transaction(function () use ($advertisement, $totalAmount) {
            // 1. Registrar pago exitoso en la tabla payments (D01, D06)
            $providerPaymentId = 'MP-SANDBOX-' . strtoupper(Str::random(12));
            $payment = Payment::create([
                'advertisement_id' => $advertisement->id,
                'provider' => 'mercadopago_sandbox',
                'provider_payment_id' => $providerPaymentId,
                'amount' => $totalAmount,
                'currency' => 'MXN',
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // 2. Programar y encolar el slot atómicamente en la región (D01, D03, D04, D08)
            $scheduleResult = $this->slotSchedulerService->scheduleSlot($advertisement);

            return [
                'success' => true,
                'status' => 'approved',
                'payment_id' => $payment->id,
                'provider_payment_id' => $providerPaymentId,
                'amount' => $totalAmount,
                'starts_at' => $scheduleResult['starts_at'],
                'ends_at' => $scheduleResult['ends_at'],
                'is_live_immediately' => $scheduleResult['is_live_immediately'],
                'queue_position' => $scheduleResult['queue_position'],
                'wait_in_seconds' => $scheduleResult['wait_in_seconds'],
                'region_slug' => $advertisement->region->slug,
            ];
        });
    }
}
