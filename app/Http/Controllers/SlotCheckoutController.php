<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Services\MercadoPagoSandboxService;
use App\Services\SlotSchedulerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SlotCheckoutController extends Controller
{
    public function __construct(
        protected MercadoPagoSandboxService $mpSandboxService,
        protected SlotSchedulerService $slotSchedulerService
    ) {}

    /**
     * Muestra la pantalla de checkout sandbox de Mercado Pago para comprar el slot de 60s.
     */
    public function checkout(Advertisement $advertisement): View|RedirectResponse
    {
        Gate::authorize('update', $advertisement);

        // Si ya tiene un slot activo o futuro, informar y redirigir
        if ($advertisement->slot && $advertisement->slot->ends_at->isFuture()) {
            return redirect()->route('ads.checkout.success', $advertisement)
                ->with('status', 'Este anuncio ya cuenta con un slot de emisión programado.');
        }

        $preference = $this->mpSandboxService->createSandboxPreference($advertisement);
        $queueStatus = $this->slotSchedulerService->getRegionQueueStatus($advertisement->region);
        $advertisement->load(['business', 'region', 'assets', 'tags']);

        return view('dashboard.checkout.show', compact('advertisement', 'preference', 'queueStatus'));
    }

    /**
     * Procesa la simulación de pago en el sandbox de Mercado Pago.
     */
    public function process(Request $request, Advertisement $advertisement): RedirectResponse
    {
        Gate::authorize('update', $advertisement);

        $simulatedOutcome = $request->input('simulated_outcome', 'approved');

        $result = $this->mpSandboxService->processSandboxPayment($advertisement, $simulatedOutcome);

        if (! $result['success']) {
            return redirect()->route('ads.checkout', $advertisement)
                ->withErrors(['payment_error' => $result['message']]);
        }

        return redirect()->route('ads.checkout.success', $advertisement)
            ->with('status', '¡Pago procesado con éxito en el Sandbox de Mercado Pago!');
    }

    /**
     * Muestra la pantalla de confirmación exitosa con la hora asignada y acceso directo a la emisión en vivo.
     */
    public function success(Advertisement $advertisement): View
    {
        Gate::authorize('view', $advertisement);

        $advertisement->load(['business', 'region', 'slot', 'payments' => fn ($q) => $q->latest()]);
        $latestPayment = $advertisement->payments->first();
        $slot = $advertisement->slot;

        return view('dashboard.checkout.success', compact('advertisement', 'latestPayment', 'slot'));
    }
}
