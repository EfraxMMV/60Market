<?php

namespace App\Http\Controllers;

use App\Models\PreRegistration;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PreRegisterController — módulo "Avisarme del lanzamiento".
 *
 *  - store():  público (visor). POST /pre-register — JSON 201/200/422.
 *  - index():  panel admin. GET /dashboard/pre-registros — listado con filtros.
 *  - export(): panel admin. GET /dashboard/pre-registros/export — CSV.
 */
class PreRegisterController extends Controller
{
    /** Teléfono: '+' opcional y 8–15 dígitos (E.164) tras normalizar. */
    private const PHONE_PATTERN = '/^\+?\d{8,15}$/';

    /**
     * Guarda un pre-registro desde el modal del visor.
     */
    public function store(Request $request): JsonResponse
    {
        // Normaliza antes de validar: correos en minúsculas, teléfonos sin espacios/guiones/paréntesis.
        $request->merge(['contact' => $this->normalizeContact((string) $request->input('contact'))]);

        $validated = $request->validate([
            'contact' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, string $value, \Closure $fail) {
                    $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                    $isPhone = preg_match(self::PHONE_PATTERN, $value) === 1;

                    if (! $isEmail && ! $isPhone) {
                        $fail('Ingresa un correo electrónico o un teléfono válido (8 a 15 dígitos).');
                    }
                },
            ],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'user_type' => ['nullable', Rule::in(PreRegistration::USER_TYPES)],
        ], [
            'contact.required' => 'Ingresa tu correo electrónico o teléfono.',
            'region_id.required' => 'No se pudo identificar la región del visor. Recarga la página.',
            'region_id.exists' => 'La región seleccionada no existe.',
            'user_type.in' => 'El tipo de usuario no es válido.',
        ]);

        // Idempotente: el mismo contacto en la misma región no se duplica.
        $registration = PreRegistration::firstOrCreate(
            ['contact' => $validated['contact'], 'region_id' => $validated['region_id']],
            ['user_type' => $validated['user_type'] ?? null],
        );

        $created = $registration->wasRecentlyCreated;

        // Visible en Render → Logs (requiere LOG_CHANNEL=stderr). Contacto enmascarado por privacidad.
        Log::info('pre_registration.' . ($created ? 'created' : 'duplicate'), [
            'id' => $registration->id,
            'region_id' => $registration->region_id,
            'user_type' => $registration->user_type,
            'contact' => $this->maskContact($registration->contact),
        ]);

        return response()->json([
            'success' => true,
            'already_registered' => ! $created,
            'message' => $created
                ? '¡Listo! Te avisaremos en cuanto 60Market se lance en tu región.'
                : 'Ya estabas registrado. Te avisaremos en cuanto lancemos en tu región.',
            'id' => $registration->id,
        ], $created ? 201 : 200);
    }

    /**
     * Listado paginado con filtros por fecha, región y tipo.
     */
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        $registrations = PreRegistration::with('region:id,name,state')
            ->filter($filters)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.pre-registrations.index', [
            'registrations' => $registrations,
            'regions' => Region::orderBy('name')->get(['id', 'name', 'state']),
            'filters' => $filters,
            'totalAll' => PreRegistration::count(),
        ]);
    }

    /**
     * Exporta a CSV los registros que cumplen los filtros activos.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $filename = 'pre-registros-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para que Excel muestre acentos correctamente
            fputcsv($out, ['id', 'contacto', 'tipo_contacto', 'tipo_usuario', 'region', 'estado', 'fecha_registro']);

            PreRegistration::with('region:id,name,state')
                ->filter($filters)
                ->orderBy('id')
                ->lazy(500)
                ->each(function (PreRegistration $r) use ($out) {
                    fputcsv($out, [
                        $r->id,
                        $this->csvSafe($r->contact),
                        $r->isEmail() ? 'email' : 'telefono',
                        $r->user_type,
                        $this->csvSafe($r->region?->name),
                        $this->csvSafe($r->region?->state),
                        $r->created_at?->toDateTimeString(),
                    ]);
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'user_type' => ['nullable', Rule::in(PreRegistration::USER_TYPES)],
        ]);
    }

    private function normalizeContact(string $contact): string
    {
        $contact = trim($contact);

        return str_contains($contact, '@')
            ? strtolower($contact)
            : preg_replace('/[\s\-().]/', '', $contact);
    }

    private function maskContact(string $contact): string
    {
        if (str_contains($contact, '@')) {
            [$user, $domain] = explode('@', $contact, 2);

            return substr($user, 0, 2) . '***@' . $domain;
        }

        return str_repeat('*', max(strlen($contact) - 4, 0)) . substr($contact, -4);
    }

    /** Evita inyección de fórmulas en Excel/Sheets (CSV injection). */
    private function csvSafe(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $isPhone = preg_match(self::PHONE_PATTERN, $value) === 1;

        return (! $isPhone && preg_match('/^[=+\-@\t\r]/', $value)) ? "'" . $value : $value;
    }
}
