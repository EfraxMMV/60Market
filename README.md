# 60Market — Un Minuto Para Vender

> _"Un minuto puede cambiar una venta."_

Plataforma web de publicidad efímera y bajo demanda que conecta comercios locales y PyMES con consumidores de su propia región geográfica. Los negocios adquieren turnos publicitarios de **exactamente sesenta segundos (60 s)** de duración en una cola secuencial en vivo, integrando un botón directo (`Comprar` / `Apartar`) que canaliza la transacción comercial hacia el canal propio del vendedor sin comisiones intermediarias.

---

## 🏗️ Arquitectura y Stack Tecnológico

La plataforma está diseñada bajo un enfoque de **monolito modular** en Laravel, priorizando baja latencia, sincronización estricta de tiempo y simplicidad operativa sin sobreingeniería:

- **Backend:** PHP 8.4 + Laravel 13
- **Base de datos principal:** PostgreSQL 16 con extensión geoespacial **PostGIS 3.4** (fuente de verdad de la programación y límites regionales).
- **Coordinación y Caché en vivo:** Redis 7 (gestión de estado efímero y eventos en memoria).
- **Entorno de desarrollo local:** Docker Compose (`docker-compose.yml`) con servicios aislados para PostgreSQL+PostGIS y Redis.
- **Testing:** PHPUnit / Pest con suite automatizada de invariantes de dominio (`tests/Feature/DomainModelTest.php`, `tests/Feature/ViewerTest.php`).

---

## 🎨 Identidad Visual y Paleta Corporativa

El sistema implementa de forma estricta los lineamientos de marca y la paleta oficial de la empresa:

- **Nombre del producto en web:** **Un Minuto Para Vender**
- **Logotipo oficial:** [`60Logo.png`](./public/images/60Logo.png) (icono de bolsa comercial con cronómetro integrado).
- **Paleta cromática oficial:**
    - **Color principal 1 (Acción / Botones / Acento):** `#F05500` (Naranja vibrante).
    - **Color principal 2 (Estructura / Encabezado / Fondo profundo):** `#032765` (Azul marino corporativo).
    - **Color de soporte cálido:** `#E8955D` (Naranja suave).
    - **Color de soporte frío / neutro:** `#4C6189` (Azul grisáceo).
    - **Color de texto y contraste:** `#FEFEFE` (Blanco puro).
- **Regla de interfaz obligatoria:** El botón de acción única de compra (`Comprar / Apartar`) debe ser **siempre de color `#F05500`**.
- **Composición de pantalla inmersiva (D02):** El anuncio abarca la totalidad del espacio disponible en pantalla entre el encabezado superior (64px) y el dock inferior (84px), donde residen el cronómetro regresivo de 60 segundos y el botón transaccional directo.

---

## 📌 Decisiones Técnicas y de Dominio (D01–D09)

Las siguientes decisiones forman el núcleo funcional y técnico acordado para el sistema:

- **D01. Producto y Transacción Externa:** 60Market cobra exclusivamente por el espacio publicitario temporal de 60 segundos. La venta, pago del producto, atención y envío ocurren en el canal externo del anunciante (WhatsApp, eCommerce propio, pasarela externa).
- **D02. Visor y Comprador Anónimo:** El comprador no requiere registro, login, correo ni perfil para ver anuncios ni para pulsar `Comprar`. El visor muestra el creativo a pantalla completa, temporizador regresivo, selector/indicador regional y un botón `Comprar` controlado por 60Market.
- **D03. Cola FIFO Regional Estricta:** Existe una única cola secuencial por región. Al confirmarse el pago, el anuncio se programa al final de la cola regional disponible. Regiones distintas operan con colas independientes y simultáneas.
- **D04. Tiempo, Persistencia e Invariantes:** Cada slot dura exactamente 60 segundos y no puede solaparse con otro de la misma región. Máximo un slot por anuncio (`advertisement_id UNIQUE`). PostgreSQL es la fuente de verdad. El cálculo del tiempo restante se deriva del servidor (`starts_at <= ahora < ends_at`), por lo que ingresar a mitad de un slot no reinicia el cronómetro. La política de inicio alineado al minuto cerrado es obligatoria.
- **D05. Regiones e Intereses:** La región se resuelve geográficamente (PostGIS). Los tags clasifican anuncios y permiten al chatbot informar al usuario cuándo saldrá una oferta de su interés en la cola, sin alterar el orden FIFO ni crear canales paralelos.
- **D06. Cuentas Exclusivamente Empresariales:** La tabla `users` representa únicamente a anunciantes y administradores de empresas (`owner_user_id` en `businesses`). No existen cuentas de compradores.
- **D07. Formatos y Moderación Obligatoria:** Formatos admitidos: imagen, video o HTML generado vía backend. El botón de compra y temporizador son elementos nativos de 60Market. Todo contenido debe pasar por moderación preventiva antes de emitirse.
- **D08. Modelo de Datos Relacional:** Estructura normalizada en 11 tablas con restricciones estrictas de integridad referencial e índices de consulta.
- **D09. Analíticas Anónimas desde V1:** Registro en base de datos de visualizaciones (`ad_impressions`) y clics (`ad_clicks`) asociados al anuncio y la región, sin persistir identidad del comprador.

---

## 🗄️ Modelo de Datos (11 Tablas)

| Tabla                  | Campos clave                                                                                                                        | Responsabilidad / Invariantes                           |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- |
| `users`                | `id`, `name`, `email UNIQUE`, `password`, `email_verified_at`                                                                       | Cuentas empresariales para gestión de anuncios.         |
| `businesses`           | `id`, `owner_user_id FK`, `region_id FK`, `name`, `status`                                                                          | Empresa anunciante y sede comercial.                    |
| `regions`              | `id`, `name`, `state`, `slug UNIQUE`, `boundary GEOMETRY`, `is_active`                                                              | Cola regional y cobertura territorial (PostGIS).        |
| `advertisements`       | `id`, `business_id FK`, `region_id FK`, `title`, `destination_url`, `content_type`, `status`, `moderation_status`, `generated_html` | Anuncio, creativo, destino y estados independientes.    |
| `advertisement_assets` | `id`, `advertisement_id FK`, `type`, `storage_key`, `mime_type`, `sort_order`                                                       | Archivos multimedia asociados (imágenes/video).         |
| `slots`                | `id`, `region_id FK`, `advertisement_id UNIQUE FK`, `starts_at`, `ends_at`                                                          | Turno de 60 s exactos en cola regional FIFO.            |
| `tags`                 | `id`, `name`, `slug UNIQUE`, `is_active`                                                                                            | Catálogo controlado de intereses comerciales.           |
| `advertisement_tag`    | `advertisement_id FK`, `tag_id FK`                                                                                                  | Pivote de clasificación sin alteración de cola.         |
| `payments`             | `id`, `advertisement_id FK`, `provider`, `amount`, `status`, `paid_at`                                                              | Registro de intentos y cobro de espacios publicitarios. |
| `ad_impressions`       | `id`, `advertisement_id FK`, `region_id FK`, `created_at`                                                                           | Métrica anónima de visualización en vivo.               |
| `ad_clicks`            | `id`, `advertisement_id FK`, `region_id FK`, `created_at`                                                                           | Métrica anónima de clics en el botón `Comprar`.         |

---

## 🚀 Entorno de Desarrollo Local

### Requisitos previos

- PHP 8.4 con extensiones `pdo_pgsql`, `redis` y `intl`.
- Composer
- Docker & Docker Compose

### Puesta en marcha

1. **Levantar base de datos y caché en Docker:**

    ```bash
    docker compose up -d
    ```

    - PostgreSQL + PostGIS disponible en `127.0.0.1:5432` (`DB_DATABASE=60market`, `DB_USERNAME=60market`, `DB_PASSWORD=secret`).
    - Redis disponible en `127.0.0.1:6379`.

2. **Configuración del entorno:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3. **Ejecutar migraciones:**

    ```bash
    php artisan migrate
    ```

4. **Correr suite de pruebas automatizadas:**
    ```bash
    php artisan test
    ```

---

## 📈 Estado de Implementación

### ✅ Incremento 1: Inicialización y Núcleo de Datos (Completado)

- Aplicación base inicializada en Laravel 13 con PHP 8.4 en [`UMPV/`](./).
- Contenedores de desarrollo Docker configurados con PostGIS 3.4 y Redis.
- 11 migraciones creadas y ejecutadas en PostgreSQL.
- 10 modelos Eloquent con relaciones, casts, scopes e invariantes temporales de 60 s implementados.
- Suite de pruebas [`tests/Feature/DomainModelTest.php`](./tests/Feature/DomainModelTest.php) creada: 8 pruebas y 22 aserciones verificadas (duración estricta de 60 s, unicidad de slot, tiempo restante sin reinicio al entrar a mitad de slot, colas independientes y analíticas anónimas).

### ✅ Incremento 2: Núcleo Regional y Visor Básico en Vivo (Completado y verificado)

- **Datos de prueba:** [`DatabaseSeeder.php`](./database/seeders/DatabaseSeeder.php) implementado para poblar regiones, usuarios empresariales, anuncios y slots secuenciales de 60 s en cola.
- **Servicio y Controlador:** [`ViewerService.php`](./app/Services/ViewerService.php) y [`ViewerController.php`](./app/Http/Controllers/ViewerController.php) con endpoints para resolución de región, consulta de slot activo en tiempo real (`/api/viewer/{region}`) y redirección de compra con registro anónimo (`/ads/{id}/click/{region}`).
- **Interfaz del Visor:** [`resources/views/viewer.blade.php`](./resources/views/viewer.blade.php) responsiva con temporizador regresivo de 60 s dinámico, barra de progreso, selector de región, anuncio en pantalla completa y botón único `Comprar`.
- **Pruebas automatizadas:** [`tests/Feature/ViewerTest.php`](./tests/Feature/ViewerTest.php) verificando carga de la página, cálculo exacto de segundos restantes, persistencia anónima de clics e impresiones y manejo de cola vacía (13 pruebas totales y 40 aserciones en suite completa).

### ✅ Incremento 3: Módulo y Panel de Acceso Empresarial (Completado y verificado)

- **Autenticación empresarial (D06):** [`AuthController.php`](./app/Http/Controllers/AuthController.php) con registro de anunciante (creando usuario empresarial y entidad `Business` vinculada), inicio de sesión con validación de credenciales y cierre de sesión.
- **Tres tipos de creativos publicitarios soportados (D07):**
  1. **Imagen:** Subida de archivos (`jpg, jpeg, png, webp`), almacenados en disco y renderizados centrados con `object-fit: contain` para no perder contenido visual.
  2. **Video:** Subida de spots en video hasta 1GB (`mp4, webm, mov, mkv, avi`), con reproducción automática en bucle (`loop`) si dura menos de 60 s, o corte automático a los 60 segundos del slot si dura más.
  3. **Generar Anuncio HTML con IA (Opción Premium / Alta Conversión):** Integración completa del generador V0 ([`AdAiGeneratorService.php`](./app/Services/AdAiGeneratorService.php)) mediante Groq (`openai/gpt-oss-120b`, `llama-3.2-11b-vision-preview`), análisis de hasta 3 imágenes de referencia, generación complementaria con Cloudflare Workers AI (`FLUX AI`) y sanitización estricta de seguridad sin JS, iframes ni formularios.
- **Visualizador previo en tiempo real integrado:** Simulador de 60 segundos integrado en la interfaz de creación y edición ([`dashboard/ads/create.blade.php`](./resources/views/dashboard/ads/create.blade.php), [`dashboard/ads/edit.blade.php`](./resources/views/dashboard/ads/edit.blade.php)), restringido exclusivamente al flujo de diseño/edición.
- **Políticas de autorización (D07, D08):** [`AdvertisementPolicy.php`](./app/Policies/AdvertisementPolicy.php) asegurando que solo el dueño del negocio puede ver, editar y eliminar sus anuncios, restringiendo modificaciones a anuncios en estado borrador (`draft`).
- **Controlador y gestión de anuncios:** [`AdvertisementController.php`](./app/Http/Controllers/AdvertisementController.php) con listado paginado, filtrado por estado/región, métricas de visualizaciones/clics por anuncio, creación y edición de borradores con asociación de tags temáticos (`Tag`), selección de región de emisión y endpoint AJAX de generación IA (`/ads/generate-ai`).
- **Pruebas automatizadas:** [`tests/Feature/BusinessAuthTest.php`](./tests/Feature/BusinessAuthTest.php) y [`tests/Feature/AdvertisementManagementTest.php`](./tests/Feature/AdvertisementManagementTest.php) verificando registro, autenticación, subida de imágenes, videos, HTML con IA, inmutabilidad de anuncios en cola y bloqueo de acceso no autorizado (26 pruebas y 87 aserciones en total).

### ✅ Incremento 4: Programación de Turnos, Pasarela de Pago Sandbox y Encolado FIFO (Completado y verificado)

- **Pasarela de pagos en Sandbox de Mercado Pago / Mercado Libre:**
  - Servicio [`MercadoPagoSandboxService.php`](./app/Services/MercadoPagoSandboxService.php) para generación de preferencias, desglose transparente de conceptos y simulación de transacciones (aprobadas y rechazadas).
  - Vistas dedicadas de checkout ([`dashboard/checkout/show.blade.php`](./resources/views/dashboard/checkout/show.blade.php)) y confirmación de emisión ([`dashboard/checkout/success.blade.php`](./resources/views/dashboard/checkout/success.blade.php)).
- **Regla de negocio de cobro único y recargo por reedición con IA:**
  - La generación con IA en sesión de borrador incluye regeneraciones ilimitadas con un único cobro.
  - Si el anuncio se reedita o regenera tras haber guardado el borrador, se activa el recargo de reedición (`requires_ai_recharge`), cobrando nuevamente la tarifa de IA en el checkout.
- **Opción directa de "Comprar Slot":**
  - Implementada en los formularios de creación y edición (`submit_action = 'buy_slot'`) y como acción directa desde la tabla del panel principal.
- **Asignación atómica en cola FIFO regional (D01, D03, D04, D08):**
  - Servicio [`SlotSchedulerService.php`](./app/Services/SlotSchedulerService.php) con transacciones y bloqueo pesimista (`lockForUpdate()`) para garantizar:
    1. Una única cola FIFO por región (`region_id`).
    2. Como máximo un anuncio activo por región e instante.
    3. Cada slot dura exactamente 60 segundos (`ends_at = starts_at + 60s`).
    4. Sin solapamientos temporales: Si la cola está libre, inicia inmediatamente; si hay turnos en espera, se coloca de forma atómica en `max(ends_at)`.
    5. Sincronización con Redis (`region:{id}:fifo_slots`).
- **Visualización inmediata en la página principal:**
  - Al completar la compra del slot, el anuncio se proyecta de inmediato en el visor en vivo (`/` o `/r/{region}`) con renderizado server-side (SSR) y cliente, temporizador regresivo de 60 segundos y botón de compra sobrepuesto.
- **Pruebas automatizadas:** [`tests/Feature/SlotSchedulingAndPaymentTest.php`](./tests/Feature/SlotSchedulingAndPaymentTest.php) con 7 pruebas y 44 aserciones verificadas (suite completa: 33 pruebas y 131 aserciones pasando al 100%).

### ✅ Incremento 5: Módulo de Pre-registro y Anuncio Fallback (Completado y verificado)

- **Tabla y modelo `CampaignLead` (D02):** Migración [`2026_10_24_000001_create_campaign_leads_table.php`](./database/migrations/2026_10_24_000001_create_campaign_leads_table.php) con campos `email` (nullable), `phone` (nullable), `user_type` (enum: `merchant`/`shopper`) y `region_id` (FK nullable a `regions`). Modelo [`CampaignLead.php`](./app/Models/CampaignLead.php) con fillables y relación a `Region`.
- **Endpoint de captura (D02):** [`CampaignLeadController.php`](./app/Http/Controllers/CampaignLeadController.php) con ruta pública `POST /campaign/pre-register` — requiere al menos `email` **o** `phone` — responde en JSON con mensaje de éxito o error.
- **Anuncio Fallback inmersivo:** Cuando la cola regional está vacía, el visor muestra un creativo de marca con título "Próximamente… Un Minuto Para Vender", subtítulo descriptivo, animación de anillo pulsante con el logotipo oficial, botón `#F05500` "Avisarme del lanzamiento" y enlace secundario a `/register` para comerciantes.
- **Modal de pre-registro:** Formulario embebido accesible desde el fallback con selector de rol (Comprador / Comerciante), campos de email y teléfono, envío AJAX con CSRF, retroalimentación de éxito/error y cierre automático tras registro exitoso.
- **Verificado:** Migración ejecutada en PostgreSQL (`DONE 15.91ms`). Ruta `POST /campaign/pre-register` confirmada en `php artisan route:list`.

