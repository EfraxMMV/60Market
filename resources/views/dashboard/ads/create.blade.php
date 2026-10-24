@extends('layouts.app')

@section('title', 'Crear Anuncio — Un Minuto Para Vender')

@section('styles')
<style>
    .editor-layout {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .editor-layout {
            grid-template-columns: 1fr;
        }
    }

    .format-card-group {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .format-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 0.75rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
        background: #ffffff;
        position: relative;
    }

    .format-card:hover {
        border-color: var(--brand-secondary-orange);
        transform: translateY(-2px);
    }

    .format-card.active {
        border-color: var(--brand-primary-orange);
        background: rgba(240, 85, 0, 0.04);
        box-shadow: 0 4px 14px rgba(240, 85, 0, 0.15);
    }

    .format-card input[type="radio"] {
        display: none;
    }

    .format-icon {
        font-size: 1.75rem;
        margin-bottom: 0.4rem;
    }

    .format-title {
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--brand-deep-navy);
    }

    .format-desc {
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 0.25rem;
        line-height: 1.25;
    }

    .badge-premium-ia {
        position: absolute;
        top: -8px;
        right: -6px;
        background: linear-gradient(135deg, #F05500, #E8955D);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 900;
        padding: 0.2rem 0.5rem;
        border-radius: 9999px;
        box-shadow: 0 2px 6px rgba(240, 85, 0, 0.4);
        letter-spacing: 0.04em;
    }

    /* Simulador de 60 Segundos (Visualizador Previo Integrado) */
    .preview-sticky-container {
        position: sticky;
        top: 88px;
    }

    .preview-device-shell {
        background: #021230;
        border: 3px solid var(--brand-deep-navy);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        height: 580px;
        position: relative;
    }

    .preview-header {
        height: 48px;
        background: var(--brand-pure-white);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
        border-bottom: 1px solid rgba(3, 39, 101, 0.15);
        z-index: 10;
    }

    .preview-stage {
        flex: 1;
        position: relative;
        background: radial-gradient(circle at center, rgba(2, 18, 48, 0.92) 0%, var(--brand-slate-blue) 85%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .preview-floating-info {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        background: rgba(3, 39, 101, 0.85);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(232, 149, 93, 0.3);
        padding: 0.45rem 0.75rem;
        border-radius: 8px;
        max-width: 240px;
        z-index: 20;
    }

    .preview-floating-action {
        position: absolute;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 30;
    }

    .preview-btn-buy {
        background: var(--brand-primary-orange);
        color: #fff;
        font-size: 0.95rem;
        font-weight: 900;
        padding: 0.6rem 1.75rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(240, 85, 0, 0.6);
        pointer-events: none;
    }

    .preview-dock {
        height: 60px;
        background: var(--brand-pure-white);
        border-top: 1px solid var(--brand-deep-navy);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
        position: relative;
        z-index: 10;
    }

    .preview-progress-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: rgba(2, 18, 48, 0.8);
    }

    .preview-progress-fill {
        width: 80%;
        height: 100%;
        background: linear-gradient(90deg, var(--brand-primary-orange), var(--brand-secondary-orange));
    }

    .ia-module-box {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 2px dashed rgba(240, 85, 0, 0.35);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .spinner-ia {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
    }

    .btn-remove-asset {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.5rem;
    }

    .btn-remove-asset:hover {
        background: #fecaca;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="card-header-flex" style="margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title">Crear Nuevo Anuncio</h1>
        <p class="page-subtitle">Configura tu oferta, sube tu creativo o genéralo con IA y visualiza el resultado exacto de 60 segundos.</p>
    </div>
    <div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">
            ← Volver al Panel
        </a>
    </div>
</div>

<div class="editor-layout">
    <!-- Formulario de Configuración -->
    <div class="card" style="padding: 2rem;">
        <form method="POST" action="{{ route('ads.store') }}" enctype="multipart/form-data" id="adForm">
            @csrf

            <!-- 1. Empresa y Región -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label for="business_id" class="form-label">Empresa anunciante *</label>
                    <select id="business_id" name="business_id" class="form-control" required onchange="updatePreview()">
                        @foreach ($businesses as $b)
                            <option value="{{ $b->id }}" {{ old('business_id') == $b->id ? 'selected' : '' }} data-name="{{ $b->name }}">
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="region_id" class="form-label">Región de emisión *</label>
                    <select id="region_id" name="region_id" class="form-control" required onchange="updatePreview()">
                        <option value="">-- Selecciona región --</option>
                        @foreach ($regions as $r)
                            <option value="{{ $r->id }}" {{ old('region_id') == $r->id ? 'selected' : '' }} data-name="{{ $r->name }}">
                                {{ $r->name }} ({{ $r->state }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. Título de la Oferta -->
            <div class="form-group">
                <label for="title" class="form-label">Título de la oferta / anuncio *</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Ej: 2x1 en Café Americano de Especialidad" maxlength="255" oninput="updatePreview()">
                <div class="form-hint">Mensaje principal visible en el visor durante los 60 segundos.</div>
            </div>

            <!-- 3. Enlace de Destino (Botón Comprar) -->
            <div class="form-group">
                <label for="destination_url" class="form-label">Enlace de Compra / Apartar (WhatsApp, Tienda Online, etc.) *</label>
                <input type="url" id="destination_url" name="destination_url" class="form-control" value="{{ old('destination_url') }}" required placeholder="https://wa.me/523121234567 o https://tienda.com/promo">
                <div class="form-hint">Destino externo hacia donde será canalizado el comprador al pulsar «Comprar».</div>
            </div>

            <!-- 4. Selector de Tipo de Creativo (3 Formatos) -->
            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Selecciona el Tipo de Creativo Publicitario *</label>
                
                <div class="format-card-group">
                    <!-- Imagen -->
                    <label class="format-card {{ old('content_type', 'image') === 'image' ? 'active' : '' }}" onclick="selectFormat('image', this)">
                        <input type="radio" name="content_type" value="image" {{ old('content_type', 'image') === 'image' ? 'checked' : '' }}>
                        <div class="format-icon">🖼️</div>
                        <div class="format-title">Imagen</div>
                        <div class="format-desc">Sube un diseño o banner (JPG, PNG, WebP)</div>
                    </label>

                    <!-- Video -->
                    <label class="format-card {{ old('content_type') === 'video' ? 'active' : '' }}" onclick="selectFormat('video', this)">
                        <input type="radio" name="content_type" value="video" {{ old('content_type') === 'video' ? 'checked' : '' }}>
                        <div class="format-icon">🎬</div>
                        <div class="format-title">Video</div>
                        <div class="format-desc">Sube spot en video (máx. 1GB / loop continuo)</div>
                    </label>

                    <!-- Generar HTML con IA -->
                    <label class="format-card {{ old('content_type') === 'html' ? 'active' : '' }}" onclick="selectFormat('html', this)">
                        <span class="badge-premium-ia">MÁS RENTABLE</span>
                        <input type="radio" name="content_type" value="html" {{ old('content_type') === 'html' ? 'checked' : '' }}>
                        <div class="format-icon">✨</div>
                        <div class="format-title">HTML con IA</div>
                        <div class="format-desc">Generación inteligente y nativa para máxima conversión</div>
                    </label>
                </div>
            </div>

            <!-- Panel Específico: Subida de Imagen -->
            <div id="section-image" style="display: {{ old('content_type', 'image') === 'image' ? 'block' : 'none' }}; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label for="image_file" class="form-label">Subir Archivo de Imagen (JPG, JPEG, PNG, WEBP)</label>
                    <input type="file" id="image_file" name="image_file" class="form-control" accept="image/jpeg,image/png,image/webp" onchange="previewUploadedImage(this)">
                    <div id="image_file_actions" style="display: none;">
                        <button type="button" class="btn-remove-asset" onclick="clearUploadedImage()">
                            ✕ Quitar imagen seleccionada
                        </button>
                    </div>
                    <div class="form-hint">Se mostrará centrada optimizando todo el espacio visual sin cortes de contenido.</div>
                </div>
            </div>

            <!-- Panel Específico: Subida de Video -->
            <div id="section-video" style="display: {{ old('content_type') === 'video' ? 'block' : 'none' }}; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label for="video_file" class="form-label">Subir Archivo de Video (MP4, WebM, MOV — Máx. 1 GB)</label>
                    <input type="file" id="video_file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/quicktime" onchange="previewUploadedVideo(this)">
                    <div id="video_file_actions" style="display: none;">
                        <button type="button" class="btn-remove-asset" onclick="clearUploadedVideo()">
                            ✕ Quitar video seleccionado
                        </button>
                    </div>
                    <div class="form-hint">Se reproduce automáticamente sin sonido (muted). Si dura menos de 60s se repite en bucle; si dura más, se corta a los 60 segundos del slot.</div>
                </div>
            </div>

            <!-- Panel Específico: Generador IA (V0 integrada) -->
            <div id="section-html" style="display: {{ old('content_type') === 'html' ? 'block' : 'none' }}; margin-bottom: 1.5rem;">
                <div class="ia-module-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.3rem;">✨</span>
                            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--brand-deep-navy);">
                                Generador Inteligente de Anuncios HTML (60Market AI)
                            </h3>
                        </div>
                        <span style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: 700;">
                            1 solo cobro por anuncio (Regeneraciones ilimitadas)
                        </span>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1rem;">
                        Describe tu producto o negocio. La IA analizará tus fotos, generará imágenes publicitarias complementarias y construirá un creativo HTML/CSS responsivo y autocontenido.
                    </p>

                    <div class="form-group">
                        <label for="ai_prompt" class="form-label">Describe la oferta que deseas que la IA diseñe</label>
                        <textarea id="ai_prompt" class="form-control" rows="3" placeholder="Ej: Somos Café Colima y tenemos frappés de chocolate al 2x1 todo el día. Quiero un diseño moderno, llamativo y juvenil con colores cálidos."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="ai_images" class="form-label">Fotos de tu producto, local o logotipo (Opcional, máx. 3)</label>
                        <input type="file" id="ai_images" class="form-control" accept="image/jpeg,image/png,image/webp" multiple onchange="checkAiImages(this)">
                        <div id="ai_images_actions" style="display: none;">
                            <button type="button" class="btn-remove-asset" onclick="clearAiImages()">
                                ✕ Quitar fotos adjuntas
                            </button>
                        </div>
                        <div class="form-hint">La IA las integrará de forma equilibrada dentro del diseño publicitario.</div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                        <input type="checkbox" id="ai_generate_image" style="accent-color: var(--brand-primary-orange); cursor: pointer;">
                        <label for="ai_generate_image" style="font-size: 0.85rem; color: #334155; cursor: pointer; font-weight: 600;">
                            🎨 Generar una imagen publicitaria complementaria con IA (FLUX AI)
                        </label>
                    </div>

                    <div style="margin-top: 1rem; display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                        <button type="button" class="btn-primary" id="btnGenerateAi" onclick="triggerAiGeneration()">
                            <span id="btnAiIcon">✨</span>
                            <span id="btnAiText">Generar Anuncio con IA</span>
                        </button>
                        <button type="button" class="btn-secondary" id="btnClearHtml" onclick="clearGeneratedHtml()" style="display: none; padding: 0.75rem 1rem; font-size: 0.85rem;">
                            ✕ Limpiar y reiniciar
                        </button>
                        <span id="aiStatusMsg" style="font-size: 0.85rem; font-weight: 600; color: var(--brand-primary-orange);"></span>
                    </div>

                    <!-- Input oculto que almacena el HTML validado final -->
                    <input type="hidden" name="generated_html" id="generated_html" value="{{ old('generated_html') }}">
                </div>
            </div>

            <!-- 5. Etiquetas (Tags) -->
            <div class="form-group" style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-subtle);">
                <label class="form-label">Etiquetas Temáticas (Tags)</label>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                    @foreach ($availableTags as $tag)
                        <label style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f1f5f9; padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 600; cursor: pointer; border: 1px solid #cbd5e1;">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" data-tag="{{ $tag->name }}" onchange="updatePreviewTags()" style="accent-color: var(--brand-primary-orange);">
                            <span>#{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>

                <label for="custom_tags" class="form-label" style="font-size: 0.8rem; color: #64748b;">Nuevas etiquetas (separadas por coma)</label>
                <input type="text" id="custom_tags" name="custom_tags" class="form-control" value="{{ old('custom_tags') }}" placeholder="descuento, café, desayunos" oninput="updatePreviewTags()">
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" name="submit_action" value="save_draft" class="btn-secondary" style="border: 1px solid #cbd5e1;">
                    💾 Guardar Borrador
                </button>
                <button type="submit" name="submit_action" value="buy_slot" class="btn-primary" style="padding: 0.85rem 1.75rem; background: linear-gradient(135deg, #F05500, #E8955D);">
                    ⚡ Comprar Slot (60s)
                </button>
            </div>
        </form>
    </div>

    <!-- 6. Visualizador Previo de 60 Segundos (En tiempo real) -->
    <div class="preview-sticky-container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
            <span style="font-size: 0.88rem; font-weight: 800; color: var(--brand-deep-navy); display: flex; align-items: center; gap: 0.35rem;">
                👁️ Visualizador Previo (60s)
            </span>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                Aspecto exacto en plataforma
            </span>
        </div>

        <div class="preview-device-shell">
            <!-- Header Simulado -->
            <div class="preview-header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <img src="{{ asset('images/60Logo.png') }}" style="height: 28px;">
                    <span style="font-size: 0.85rem; font-weight: 800; color: var(--brand-deep-navy);">
                        Un Minuto Para <span style="color: var(--brand-primary-orange);">Vender</span>
                    </span>
                </div>
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--brand-deep-navy);" id="previewRegionLabel">
                    📍 Colima
                </div>
            </div>

            <!-- Escenario Simulado -->
            <div class="preview-stage" id="previewStage">
                <!-- Info Flotante Superior -->
                <div class="preview-floating-info">
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--brand-secondary-orange); text-transform: uppercase;" id="previewBusinessName">
                        Tu Empresa
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #fff; margin-top: 0.15rem; line-height: 1.2;" id="previewTitle">
                        Título de tu anuncio aquí...
                    </div>
                    <div id="previewTagsList" style="display: flex; flex-wrap: wrap; gap: 0.2rem; margin-top: 0.35rem;"></div>
                </div>

                <!-- Contenido Dinámico Centrado -->
                <div id="previewMediaContainer" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; position: relative;">
                    <div style="text-align: center; padding: 1.5rem; color: #94a3b8;" id="previewDefaultPlaceholder">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📢</div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #fff;">Configura tu creativo</div>
                        <div style="font-size: 0.75rem; color: #cbd5e1; margin-top: 0.2rem;">Sube una imagen, video o genera HTML con IA</div>
                    </div>
                </div>

                <!-- Botón Comprar Centrado en la parte inferior -->
                <div class="preview-floating-action">
                    <div class="preview-btn-buy">
                        <span>Comprar</span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Dock Inferior Simulado -->
            <div class="preview-dock">
                <div class="preview-progress-line">
                    <div class="preview-progress-fill"></div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.65rem; font-weight: 800; background: #fee2e2; color: #ef4444; padding: 0.2rem 0.5rem; border-radius: 9999px;">EN VIVO</span>
                    <div style="background: rgba(3, 39, 101, 0.85); padding: 0.2rem 0.5rem; border-radius: 6px; font-weight: 900; font-size: 0.9rem; color: #fff;">
                        52 <span style="font-size: 0.6rem; color: var(--brand-secondary-orange);">seg</span>
                    </div>
                </div>

                <div style="font-size: 0.72rem; color: var(--brand-deep-navy); font-weight: 800; text-align: right;" id="previewDockTitle">
                    Sincronizando anuncio...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentFormat = "{{ old('content_type', 'image') }}";

    function selectFormat(format, element) {
        currentFormat = format;
        document.querySelectorAll('.format-card').forEach(c => c.classList.remove('active'));
        element.classList.add('active');
        element.querySelector('input[type="radio"]').checked = true;

        document.getElementById('section-image').style.display = (format === 'image') ? 'block' : 'none';
        document.getElementById('section-video').style.display = (format === 'video') ? 'block' : 'none';
        document.getElementById('section-html').style.display = (format === 'html') ? 'block' : 'none';

        updatePreview();
    }

    function updatePreview() {
        const titleVal = document.getElementById('title').value || 'Título de tu anuncio aquí...';
        const businessSelect = document.getElementById('business_id');
        const businessName = businessSelect.options[businessSelect.selectedIndex]?.dataset.name || 'Tu Empresa';
        const regionSelect = document.getElementById('region_id');
        const regionName = regionSelect.options[regionSelect.selectedIndex]?.dataset.name || 'Colima';

        document.getElementById('previewTitle').textContent = titleVal;
        document.getElementById('previewBusinessName').textContent = businessName;
        document.getElementById('previewRegionLabel').textContent = `📍 ${regionName}`;
        document.getElementById('previewDockTitle').textContent = titleVal;

        const htmlContent = document.getElementById('generated_html').value;
        if (currentFormat === 'html' && htmlContent) {
            renderHtmlInPreview(htmlContent);
            document.getElementById('btnClearHtml').style.display = 'inline-flex';
        }
    }

    function updatePreviewTags() {
        const list = document.getElementById('previewTagsList');
        list.innerHTML = '';

        document.querySelectorAll('input[name="tags[]"]:checked').forEach(cb => {
            const span = document.createElement('span');
            span.style.cssText = 'background: rgba(76, 97, 137, 0.4); color: #fff; font-size: 0.6rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 9999px;';
            span.textContent = `#${cb.dataset.tag}`;
            list.appendChild(span);
        });

        const custom = document.getElementById('custom_tags').value;
        if (custom) {
            custom.split(',').map(s => s.trim()).filter(Boolean).forEach(t => {
                const span = document.createElement('span');
                span.style.cssText = 'background: rgba(232, 149, 93, 0.4); color: #fff; font-size: 0.6rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 9999px;';
                span.textContent = `#${t}`;
                list.appendChild(span);
            });
        }
    }

    function previewUploadedImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('previewMediaContainer');
                container.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%; object-fit: contain; margin: auto; display: block; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">`;
                document.getElementById('image_file_actions').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearUploadedImage() {
        const input = document.getElementById('image_file');
        input.value = '';
        document.getElementById('image_file_actions').style.display = 'none';
        resetPreviewMedia();
    }

    function previewUploadedVideo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const videoUrl = URL.createObjectURL(file);
            const container = document.getElementById('previewMediaContainer');
            container.innerHTML = `<video src="${videoUrl}" autoplay muted loop playsinline style="max-width: 100%; max-height: 100%; object-fit: contain; margin: auto; display: block; box-shadow: 0 10px 30px rgba(0,0,0,0.5);"></video>`;
            document.getElementById('video_file_actions').style.display = 'block';
        }
    }

    function clearUploadedVideo() {
        const input = document.getElementById('video_file');
        input.value = '';
        document.getElementById('video_file_actions').style.display = 'none';
        resetPreviewMedia();
    }

    function checkAiImages(input) {
        if (input.files && input.files.length > 0) {
            document.getElementById('ai_images_actions').style.display = 'block';
        }
    }

    function clearAiImages() {
        const input = document.getElementById('ai_images');
        input.value = '';
        document.getElementById('ai_images_actions').style.display = 'none';
    }

    function clearGeneratedHtml() {
        document.getElementById('generated_html').value = '';
        document.getElementById('btnClearHtml').style.display = 'none';
        document.getElementById('aiStatusMsg').textContent = '';
        resetPreviewMedia();
    }

    function resetPreviewMedia() {
        const container = document.getElementById('previewMediaContainer');
        container.innerHTML = `
            <div style="text-align: center; padding: 1.5rem; color: #94a3b8;" id="previewDefaultPlaceholder">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">📢</div>
                <div style="font-size: 0.85rem; font-weight: 700; color: #fff;">Configura tu creativo</div>
                <div style="font-size: 0.75rem; color: #cbd5e1; margin-top: 0.2rem;">Sube una imagen, video o genera HTML con IA</div>
            </div>
        `;
    }

    function renderHtmlInPreview(html) {
        const container = document.getElementById('previewMediaContainer');
        container.innerHTML = `<iframe srcdoc="${escapeHtmlAttr(html)}" style="width: 100%; height: 100%; border: none; overflow: hidden; pointer-events: none; display: block;"></iframe>`;
    }

    function escapeHtmlAttr(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    async function triggerAiGeneration() {
        const promptVal = document.getElementById('ai_prompt').value.trim();
        if (!promptVal) {
            alert('Por favor describe tu anuncio antes de generar con IA.');
            return;
        }

        const btn = document.getElementById('btnGenerateAi');
        const icon = document.getElementById('btnAiIcon');
        const text = document.getElementById('btnAiText');
        const statusMsg = document.getElementById('aiStatusMsg');

        btn.disabled = true;
        icon.className = 'spinner-ia';
        icon.textContent = '';
        text.textContent = 'Diseñando anuncio con IA...';
        statusMsg.style.color = 'var(--brand-primary-orange)';
        statusMsg.textContent = 'Analizando contexto visual y generando HTML...';

        const formData = new FormData();
        formData.append('prompt', promptVal);
        formData.append('_token', '{{ csrf_token() }}');
        
        if (document.getElementById('ai_generate_image').checked) {
            formData.append('generate_ai_image', '1');
        }

        const imageInput = document.getElementById('ai_images');
        if (imageInput.files) {
            for (let i = 0; i < imageInput.files.length && i < 3; i++) {
                formData.append('images[]', imageInput.files[i]);
            }
        }

        try {
            const response = await fetch('{{ route('ads.generate-ai') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                document.getElementById('generated_html').value = data.html;
                renderHtmlInPreview(data.html);
                document.getElementById('btnClearHtml').style.display = 'inline-flex';
                statusMsg.style.color = '#10b981';
                statusMsg.textContent = '¡Anuncio generado con éxito!';
            } else {
                statusMsg.style.color = '#dc2626';
                statusMsg.textContent = data.error || 'Ocurrió un error generando el anuncio.';
                alert(data.error || 'No se pudo generar el anuncio.');
            }
        } catch (err) {
            statusMsg.style.color = '#dc2626';
            statusMsg.textContent = 'Error de conexión con el servicio de IA.';
        } finally {
            btn.disabled = false;
            icon.className = '';
            icon.textContent = '✨';
            text.textContent = 'Generar Anuncio con IA';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updatePreview();
        updatePreviewTags();
    });
</script>
@endsection
