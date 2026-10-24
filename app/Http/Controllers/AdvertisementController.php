<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\AdvertisementAsset;
use App\Models\Business;
use App\Models\Region;
use App\Models\Tag;
use App\Services\AdAiGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    /**
     * Panel principal de la empresa con estadísticas y listado de anuncios (D06, D08).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $businessIds = $user->businesses()->pluck('id');

        $adsQuery = Advertisement::with(['business', 'region', 'tags', 'slot', 'assets'])
            ->withCount(['impressions', 'clicks'])
            ->whereIn('business_id', $businessIds)
            ->latest();

        // Filtro por estado
        if ($request->filled('status')) {
            $adsQuery->where('status', $request->status);
        }

        // Filtro por región
        if ($request->filled('region_id')) {
            $adsQuery->where('region_id', $request->region_id);
        }

        $advertisements = $adsQuery->paginate(10)->withQueryString();

        // Métricas resumidas
        $totalAds = Advertisement::whereIn('business_id', $businessIds)->count();
        $draftAds = Advertisement::whereIn('business_id', $businessIds)->where('status', 'draft')->count();
        $queuedAds = Advertisement::whereIn('business_id', $businessIds)->whereIn('status', ['queued', 'scheduled', 'active'])->count();
        $completedAds = Advertisement::whereIn('business_id', $businessIds)->where('status', 'completed')->count();

        $regions = Region::where('is_active', true)->orderBy('name')->get();
        $userBusinesses = $user->businesses()->get();

        return view('dashboard.index', compact(
            'advertisements',
            'totalAds',
            'draftAds',
            'queuedAds',
            'completedAds',
            'regions',
            'userBusinesses'
        ));
    }

    /**
     * Muestra el formulario para redactar un nuevo borrador de anuncio (D06, D07, D08).
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        $businesses = $user->businesses()->get();

        if ($businesses->isEmpty()) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Debes registrar al menos una empresa antes de crear anuncios.']);
        }

        $regions = Region::where('is_active', true)->orderBy('name')->get();
        $availableTags = Tag::orderBy('name')->get();

        return view('dashboard.ads.create', compact('businesses', 'regions', 'availableTags'));
    }

    /**
     * Guarda el nuevo borrador de anuncio y asocia sus recursos multimedia o HTML generado.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $userBusinessIds = $user->businesses()->pluck('id')->toArray();

        $validated = $request->validate([
            'business_id' => ['required', 'in:' . implode(',', $userBusinessIds)],
            'region_id' => ['required', 'exists:regions,id'],
            'title' => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:500'],
            'content_type' => ['required', 'in:image,video,html'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:20480'], // 20 MB
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov,mkv,avi', 'max:1048576'], // Máximo 1GB
            'generated_html' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'custom_tags' => ['nullable', 'string', 'max:255'],
        ], [
            'business_id.required' => 'Debes seleccionar la empresa anunciante.',
            'region_id.required' => 'Debes seleccionar la región donde se emitirá el anuncio.',
            'title.required' => 'El título de la oferta es obligatorio.',
            'destination_url.required' => 'La URL de destino (compra / apartar) es obligatoria.',
            'destination_url.url' => 'Ingresa una URL válida (ej. https://mi-tienda.com/oferta).',
            'content_type.required' => 'Selecciona el tipo de creativo publicitario.',
            'image_file.mimes' => 'La imagen debe estar en formato jpg, jpeg, png o webp.',
            'video_file.max' => 'El video no debe exceder el límite de 1GB.',
        ]);

        // Crear anuncio en estado borrador
        $ad = Advertisement::create([
            'business_id' => $validated['business_id'],
            'region_id' => $validated['region_id'],
            'title' => $validated['title'],
            'destination_url' => $validated['destination_url'],
            'content_type' => $validated['content_type'],
            'status' => 'draft',
            'moderation_status' => 'pending',
            'generated_html' => $validated['content_type'] === 'html' ? ($validated['generated_html'] ?? null) : null,
        ]);

        // Procesar archivo de imagen si aplica
        if ($validated['content_type'] === 'image' && $request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('ads/images', 'public');
            AdvertisementAsset::create([
                'advertisement_id' => $ad->id,
                'type' => 'image',
                'storage_key' => $path,
                'mime_type' => $request->file('image_file')->getMimeType(),
                'sort_order' => 1,
            ]);
        }

        // Procesar archivo de video si aplica
        if ($validated['content_type'] === 'video' && $request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('ads/videos', 'public');
            AdvertisementAsset::create([
                'advertisement_id' => $ad->id,
                'type' => 'video',
                'storage_key' => $path,
                'mime_type' => $request->file('video_file')->getMimeType(),
                'sort_order' => 1,
            ]);
        }

        // Sincronizar tags
        $tagIds = $validated['tags'] ?? [];
        if (!empty($validated['custom_tags'])) {
            $customTagNames = array_map('trim', explode(',', $validated['custom_tags']));
            foreach ($customTagNames as $tagName) {
                if ($tagName !== '') {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }
        $ad->tags()->sync(array_unique($tagIds));

        if ($request->input('submit_action') === 'buy_slot') {
            return redirect()->route('ads.checkout', $ad)
                ->with('status', '¡Borrador guardado! Procede a adquirir tu slot en el sandbox de Mercado Pago.');
        }

        return redirect()->route('dashboard')
            ->with('status', '¡Borrador de anuncio creado con éxito! Puedes editarlo para ver su previsualización o comprar su slot de emisión.');
    }

    /**
     * Muestra el formulario para editar un borrador existente con previsualizador en vivo integrado.
     */
    public function edit(Advertisement $advertisement): View
    {
        Gate::authorize('update', $advertisement);

        $user = Auth::user();
        $businesses = $user->businesses()->get();
        $regions = Region::where('is_active', true)->orderBy('name')->get();
        $availableTags = Tag::orderBy('name')->get();
        $selectedTagIds = $advertisement->tags()->pluck('tags.id')->toArray();
        $advertisement->load(['business', 'region', 'tags', 'assets']);

        return view('dashboard.ads.edit', compact('advertisement', 'businesses', 'regions', 'availableTags', 'selectedTagIds'));
    }

    /**
     * Actualiza el borrador del anuncio.
     */
    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        Gate::authorize('update', $advertisement);

        $user = Auth::user();
        $userBusinessIds = $user->businesses()->pluck('id')->toArray();

        $validated = $request->validate([
            'business_id' => ['required', 'in:' . implode(',', $userBusinessIds)],
            'region_id' => ['required', 'exists:regions,id'],
            'title' => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:500'],
            'content_type' => ['required', 'in:image,video,html'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:20480'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov,mkv,avi', 'max:1048576'],
            'generated_html' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'custom_tags' => ['nullable', 'string', 'max:255'],
        ]);

        $isHtml = $validated['content_type'] === 'html';
        $requiresAiRecharge = $advertisement->requires_ai_recharge;

        // Regla de negocio: Si el anuncio es HTML con IA y se edita tras guardar el borrador, se activa el recargo de reedición
        if ($isHtml) {
            $requiresAiRecharge = true;
        }

        $advertisement->update([
            'business_id' => $validated['business_id'],
            'region_id' => $validated['region_id'],
            'title' => $validated['title'],
            'destination_url' => $validated['destination_url'],
            'content_type' => $validated['content_type'],
            'generated_html' => $isHtml ? ($validated['generated_html'] ?? null) : null,
            'requires_ai_recharge' => $requiresAiRecharge,
        ]);

        if ($validated['content_type'] === 'image' && $request->hasFile('image_file')) {
            // Eliminar asset anterior si existía
            foreach ($advertisement->assets as $asset) {
                Storage::disk('public')->delete($asset->storage_key);
                $asset->delete();
            }
            $path = $request->file('image_file')->store('ads/images', 'public');
            AdvertisementAsset::create([
                'advertisement_id' => $advertisement->id,
                'type' => 'image',
                'storage_key' => $path,
                'mime_type' => $request->file('image_file')->getMimeType(),
                'sort_order' => 1,
            ]);
        }

        if ($validated['content_type'] === 'video' && $request->hasFile('video_file')) {
            foreach ($advertisement->assets as $asset) {
                Storage::disk('public')->delete($asset->storage_key);
                $asset->delete();
            }
            $path = $request->file('video_file')->store('ads/videos', 'public');
            AdvertisementAsset::create([
                'advertisement_id' => $advertisement->id,
                'type' => 'video',
                'storage_key' => $path,
                'mime_type' => $request->file('video_file')->getMimeType(),
                'sort_order' => 1,
            ]);
        }

        $tagIds = $validated['tags'] ?? [];
        if (!empty($validated['custom_tags'])) {
            $customTagNames = array_map('trim', explode(',', $validated['custom_tags']));
            foreach ($customTagNames as $tagName) {
                if ($tagName !== '') {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }
        $advertisement->tags()->sync(array_unique($tagIds));

        if ($request->input('submit_action') === 'buy_slot') {
            return redirect()->route('ads.checkout', $advertisement)
                ->with('status', '¡Cambios guardados! Procede a adquirir tu slot en el sandbox de Mercado Pago.');
        }

        return redirect()->route('dashboard')
            ->with('status', 'Borrador de anuncio actualizado correctamente.');
    }

    /**
     * Elimina un borrador de anuncio y sus assets.
     */
    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        Gate::authorize('delete', $advertisement);

        foreach ($advertisement->assets as $asset) {
            Storage::disk('public')->delete($asset->storage_key);
            $asset->delete();
        }

        $advertisement->tags()->detach();
        $advertisement->delete();

        return redirect()->route('dashboard')
            ->with('status', 'El borrador de anuncio ha sido eliminado.');
    }

    /**
     * Endpoint AJAX para generar HTML con IA (V0 integrada).
     */
    public function generateAi(Request $request, AdAiGeneratorService $aiService): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'generate_ai_image' => ['nullable', 'boolean'],
        ]);

        $uploadedImages = $request->file('images', []);
        $generateAiImage = $request->boolean('generate_ai_image');

        $result = $aiService->generate($request->prompt, $uploadedImages, $generateAiImage);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'html' => $result['html'],
        ]);
    }
}
