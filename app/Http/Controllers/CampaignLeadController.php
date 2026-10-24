<?php

namespace App\Http\Controllers;

use App\Models\CampaignLead;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CampaignLeadController — Incremento 5: Módulo de Pre-registro.
 *
 * Captura interacciones de visitantes interesados (compradores y comercios)
 * antes del lanzamiento oficial de la plataforma.
 *
 * Ruta: POST /campaign/pre-register
 */
class CampaignLeadController extends Controller
{
    /**
     * Guarda un nuevo registro de interés de campaña.
     *
     * Acepta: email (nullable), phone (nullable), user_type, region_id (nullable).
     * Al menos uno de email o phone es requerido.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'user_type' => ['required', Rule::in(['merchant', 'shopper'])],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
        ]);

        // Al menos un canal de contacto debe estar presente
        if (empty($validated['email']) && empty($validated['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar al menos un correo electrónico o número de teléfono.',
            ], 422);
        }

        $lead = CampaignLead::create($validated);

        return response()->json([
            'success' => true,
            'message' => '¡Gracias! Te avisaremos cuando 60Market esté disponible en tu región.',
            'lead_id' => $lead->id,
        ], 201);
    }
}
