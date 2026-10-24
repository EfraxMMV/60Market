<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Region;
use App\Services\ViewerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ViewerController extends Controller
{
    public function __construct(
        protected ViewerService $viewerService
    ) {}

    /**
     * Muestra la pantalla principal del visor para la región solicitada o por defecto (D02).
     */
    public function index(Request $request, ?string $regionSlug = null): View|JsonResponse
    {
        $region = $this->viewerService->resolveRegion($regionSlug);

        if (! $region) {
            abort(404, 'No hay regiones activas configuradas.');
        }

        $slotData = $this->viewerService->getActiveSlotData($region);
        $availableRegions = Region::where('is_active', true)->select('id', 'name', 'slug', 'state')->get();

        if ($request->wantsJson()) {
            return response()->json($slotData);
        }

        return view('viewer', [
            'initialData' => $slotData,
            'currentRegion' => $region,
            'availableRegions' => $availableRegions,
        ]);
    }

    /**
     * Endpoint API para consultar el estado del slot activo de una región (D02, D04).
     */
    public function currentSlot(string $regionSlug): JsonResponse
    {
        $region = Region::where('slug', $regionSlug)->where('is_active', true)->first();

        if (! $region) {
            return response()->json(['error' => 'Región no encontrada'], 404);
        }

        $data = $this->viewerService->getActiveSlotData($region);

        return response()->json($data);
    }

    /**
     * Registra el clic anónimo y redirige al canal de venta externo del negocio (D01, D09).
     */
    public function click(int $advertisementId, int $regionId): RedirectResponse
    {
        $ad = Advertisement::findOrFail($advertisementId);
        $region = Region::findOrFail($regionId);

        $destinationUrl = $this->viewerService->recordClickAndGetDestination($ad, $region);

        return redirect()->away($destinationUrl);
    }

    /**
     * Registra una impresión anónima cuando el anuncio se proyecta en el navegador (D09).
     */
    public function impression(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'advertisement_id' => 'required|integer|exists:advertisements,id',
            'region_id' => 'required|integer|exists:regions,id',
        ]);

        $ad = Advertisement::findOrFail($validated['advertisement_id']);
        $region = Region::findOrFail($validated['region_id']);

        $this->viewerService->recordImpression($ad, $region);

        return response()->json(['status' => 'ok']);
    }
}
