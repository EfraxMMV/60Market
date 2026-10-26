# Contexto de implementación — 60Market

## 1. Fuentes, alcance y criterio de certeza

Este documento consolida la conversación [Arquitectura de sincronización realtime](chatgpt-conversation://6ab41764-062c-83e8-a596-b12eed2d9e95), incluidos sus antecedentes del prototipo V0, y los documentos locales [1MPV.md](sources/1MPV.md) y [60Market_Analisis.md](sources/60Market_Analisis.md). Los archivos de `sources/` son referencias sincronizadas de solo lectura.

Se distinguen cuatro estados:

- **Acordado:** instrucción explícita del usuario o parte del modelo que el usuario aceptó, siempre aplicando sus correcciones posteriores.
- **Propuesto:** recomendación o detalle presentado por el asistente, sin convertir su afirmación de «modelo cerrado» en una aprobación adicional del usuario.
- **Pendiente:** alternativa, parámetro o política que no quedó resuelta.
- **Histórico V0:** experimento anterior; no implica elección automática para producción V1.

`README.md` contiene exclusivamente decisiones acordadas, identificadas como D01–D09. Este documento conserva además propuestas y pendientes. La existencia de una decisión no demuestra que esté implementada: en este espacio se encontraron los dos documentos fuente y las instrucciones del proyecto, no una aplicación V1 ni resultados de pruebas. No se verificó el código del prototipo. Del Balsamiq se utiliza la descripción del usuario y el análisis registrado en la conversación; no se realizó aquí una nueva inspección del archivo `.bmpr`.

Las decisiones explícitas más recientes prevalecen sobre propuestas anteriores. Las etiquetas «HECHO» del diagnóstico no bastan por sí solas para convertir una deducción en requisito aprobado.

## 2. Concepto y alcance acordado

60Market, «Un Minuto Para Vender», ofrece exposición publicitaria local de 60 segundos a negocios y PyMES. El comprador ve el anuncio directamente y accede al canal de venta del anunciante mediante `Comprar`. La plataforma cobra el espacio publicitario; la transacción del producto y su logística pertenecen al negocio.

Dos actores:

| Actor | Funciones acordadas | Identidad |
|---|---|---|
| Comprador/visitante | Ver anuncios regionales, expresar intereses al chatbot, recibir avisos de próximos anuncios y pulsar Comprar | Sin cuenta ni datos personales requeridos |
| Anunciante/empresa | Acceder mediante login empresarial, crear contenido publicitario, asociar destino y tags, pagar por su turno y administrar sus anuncios | Cuenta empresarial en `users` |

El visor contiene anuncio, CTA de compra común, acceso superior a publicación e indicador de tiempo. Mostrar simultáneamente barra y segundos fue una **propuesta**: el usuario admitió contador o barra, sin cerrar esa combinación.

La moderación preventiva es obligatoria. Estadísticas y clics anónimos entran en V1. No se ha acordado crear cuentas de compradores para soportarlos.

## 3. Evolución de decisiones y trazabilidad

Las referencias C1–C10 corresponden a mensajes localizables en la conversación citada, en orden conceptual; no son versiones de software.

| Referencia | Evidencia y consecuencia | Decisión README |
|---|---|---|
| F1 | Documento fundacional: pago por minuto, compra externa, contenido multimedia/prompt, geografía, tags, chatbot y filtro | D01, D05, D07 |
| C1 | Usuario describe Balsamiq: anuncio como inicio, único Comprar, acceso superior e indicador de tiempo | D02 |
| C2 | Usuario: «solo existe una única cola por región»; los intereses avisan, sin alterar lo que se muestra | D03, D05 |
| C3 | Usuario: «cola FIFO estricta por región» y compra del siguiente minuto al final de la cola | D03 |
| C4 | Modelo inicial con relaciones, slots, constraints, pagos e índices; el usuario acepta los demás aspectos al retirar el login del comprador | D04, D06, D08 |
| C5 | Usuario: comprador sin login, email ni datos personales; identidad innecesaria | D02 |
| C6 | Modelo revisado: región/intereses locales; el usuario pide implementarlo en su siguiente mensaje | D05, D08 |
| C7 | Usuario añade login empresarial y estadísticas/clic anónimo desde las primeras versiones | D06, D09 |
| C8 | Usuario determina presentación en el formato subido: imagen, video o HTML IA | D07 |
| C9 | Respuesta final propone once tablas, campos adicionales, impresiones, sanitización y pipeline de moderación | Propuesta final detallada; distinguir sus adiciones |
| C10 | Usuario solicita README de acuerdos, contexto completo y prompt de implementación con actualización documental paralela | Contrato de continuidad |

Cambios que sustituyen ideas anteriores:

- La cola única regional descarta los canales temáticos paralelos sugeridos en el diagnóstico y la personalización del feed por radio individual.
- `users` dejó de representar compradores; su reincorporación se limita al login empresarial. `user_interest` se retira.
- La propuesta de posponer analytics quedó sustituida por la solicitud de incluir estadísticas y clics desde V1.
- La autenticación empresarial dejó de ser una elección abierta entre enlaces mágicos y otras alternativas: se pidió login empresarial. Sus detalles de registro y recuperación no se cerraron.
- El HTML generado es una modalidad de creativo; el CTA comercial de producción permanece fuera del creativo, aunque la demo incluyera botones.
- Redis como cola oficial fue sustituido por programación persistente en PostgreSQL.

## 4. Arquitectura acordada y stack propuesto

### Base del dominio aceptada

- Laravel como autoridad del dominio y PostgreSQL como fuente persistente de verdad del modelo aceptado.
- PostGIS para representar/resolver regiones; el motor opera después con `region_id`.
- Redis para estado efímero y coordinación, sin reemplazar los slots persistidos.
- Recursos multimedia identificados por claves de almacenamiento, con R2 contemplado en el modelo.
- Una sola programación consultada por visor, anunciante y chatbot.

### Propuestas de implementación, sin aprobación global del stack

La conversación favoreció un **monolito modular Laravel + Inertia/React**, sin dos proyectos separados ni microservicios. El usuario presentó una tabla tecnológica como propuesta, y el asistente la evaluó de forma preliminar; no consta aprobación explícita de toda la tabla.

| Componente mencionado | Estado documental |
|---|---|
| PHP 8.4, Inertia, React, Tailwind | Base propuesta; versiones definitivas no cerradas |
| Reverb + Echo / HTTP + SSE | Alternativas para validar con el protocolo temporal; elección pendiente |
| Laravel Queue, Horizon | Propuestos para jobs; Horizon cuando existan trabajos reales |
| Cloudflare CDN/DNS/WAF | Propuesto para entrega y protección |
| R2 / Cloudflare Stream | R2 contemplado para multimedia; Stream fue una posibilidad posterior |
| OpenAI API | Propuesta para IA V1; no sustituye automáticamente Groq/Workers AI del prototipo |
| Stripe o Mercado Pago | Proveedor de cobro de slots pendiente |
| Resend | Propuesto para email empresarial, no seleccionado definitivamente |
| Git/GitHub, Pest/PHPUnit, Playwright | Herramientas propuestas para desarrollo y validación |
| Pulse/Sentry | Observabilidad propuesta conforme se despliegue |
| VPS Linux; Forge opcional | Despliegue propuesto; proveedor y operación pendientes |

Los precios, cuotas y capacidades comerciales mencionados en el chat fueron referencias de aquella discusión, no compromisos ni valores verificados para implementar. No se fijan aquí presupuestos ni modelos de IA actuales.

## 5. Modelo de datos V1

### 5.1 Estado del contrato

El usuario aceptó el modelo relacional anterior con el comprador anónimo y solicitó añadir login empresarial y analytics. La respuesta final lo consolidó en **once tablas**. Se conserva esa propuesta fielmente a continuación, identificando las adiciones que no recibieron ratificación posterior. No es un esquema SQL ya ejecutado.

| Tabla | Campos del modelo consolidado | Estado y responsabilidad |
|---|---|---|
| `users` | `id`, `name`, `email UNIQUE`, `password`, `email_verified_at`, `created_at`, `updated_at` | Login empresarial acordado; campos recuperados del modelo anterior. Sin comprador ni `region_id` del comprador |
| `businesses` | `id`, `owner_user_id FK`, `region_id FK`, `name`, `description`, `logo_storage_key`, `status`, timestamps | Entidad y propietario acordados. `logo_storage_key` sustituye al anterior `logo_url` en la propuesta final |
| `regions` | `id`, `name`, `state`, `slug UNIQUE`, `boundary GEOMETRY`, `is_active`, timestamps | Región controlada y cola asociada; geometría geográfica del modelo aceptado |
| `advertisements` | `id`, `business_id FK`, `region_id FK`, `title`, `destination_url`, `content_type`, `status`, `moderation_status`, `moderation_reason`, timestamps | Base aceptada; `content_type = image / video / html` concreta los formatos pedidos |
| `advertisement_assets` | `id`, `advertisement_id FK`, `type`, `storage_key`, `mime_type`, `file_size`, timestamps | Recursos multimedia; `file_size` se añadió en la propuesta final |
| `slots` | `id`, `region_id FK`, `advertisement_id UNIQUE FK`, `starts_at`, `ends_at`, timestamps | Turno temporal. Máximo uno por anuncio |
| `tags` | `id`, `name`, `slug UNIQUE`, `is_active`, timestamps | Catálogo controlado |
| `advertisement_tag` | `advertisement_id FK`, `tag_id FK`, PK compuesta por ambos | Relación muchos a muchos, sin modificar FIFO |
| `payments` | `id`, `advertisement_id FK`, `provider`, `provider_payment_id`, `amount`, `currency`, `status`, `paid_at`, timestamps | Varios intentos/pagos por anuncio; proveedor pendiente |
| `ad_impressions` | `id`, `advertisement_id FK`, `region_id FK`, `created_at` | Tabla propuesta en respuesta final para visualizaciones anónimas; criterio de impresión pendiente |
| `ad_clicks` | `id`, `advertisement_id FK`, `region_id FK`, `created_at` | Materialización propuesta del registro anónimo solicitado |

`timestamps` significa `created_at` y `updated_at`. En el esquema previo los identificadores eran `BIGINT`, importes `DECIMAL`, texto corto `VARCHAR` y tiempos `TIMESTAMP`; precisión, zona horaria, nulabilidad completa y acciones de borrado no quedaron especificadas como migraciones definitivas.

La respuesta final propuso también `advertisements.generated_html TEXT NULL` para almacenar HTML en PostgreSQL, sin exigir guardarlo en R2. Esta ubicación sigue siendo **propuesta**, no un requisito nuevo del README.

El modelo anterior incluía `advertisement_assets.sort_order` para ordenar logo, producto e imagen IA; la tabla resumida final lo omitió sin justificar su eliminación. Debe resolverse al concretar el esquema. El `type = html` que aparecía antes en assets no debe mezclarse sin decisión con la propuesta posterior de `generated_html`.

### 5.2 Relaciones

```text
users 1 ── N businesses 1 ── N advertisements
regions 1 ── N businesses
regions 1 ── N advertisements
regions 1 ── N slots
advertisements 1 ── 0..1 slots
advertisements 1 ── N advertisement_assets
advertisements N ── N tags (advertisement_tag)
advertisements 1 ── N payments
advertisements 1 ── N ad_impressions / ad_clicks (propuesta analytics)
regions 1 ── N ad_impressions / ad_clicks (propuesta analytics)
```

No se reproduce la flecha `regions → users` del diagrama final del chat: sus campos no la sustentan después de retirar las cuentas de compradores. Tampoco hay FK directa de anuncio a usuario: la propiedad pasa por la empresa.

`businesses.region_id` es establecimiento; `advertisements.region_id` es publicación. El esquema permite distinguirlos, pero no autoriza por sí solo anuncios fuera de la región del negocio: esa política comercial sigue pendiente.

La cola se deriva de `slots` por región, ordenados por `starts_at`; para anuncios actuales/futuros se utiliza `ends_at > ahora`. No hay tabla `queues`, `user_interest` ni necesidad acordada de `anonymous_sessions`.

### 5.3 Estados e índices

Se acordó separar estado comercial de moderación. Los valores propuestos fueron:

- Comercial: `draft`, `pending_payment`, `paid`, `scheduled`, `cancelled`.
- Moderación en el modelo inicial: `pending`, `approved`, `rejected`.
- Pago: `pending`, `paid`, `failed`, `refunded`, `cancelled`.

La moderación final propone además la salida `REVIEW`. Su correspondencia con campos/estados y su atención operativa no quedaron resueltas. Que existan nombres `cancelled` o `refunded` no define políticas de cancelación o reembolso. `LIVE` y `COMPLETED` son estados temporales derivados del slot, no transiciones dependientes de un worker puntual.

Índices contemplados en el modelo aceptado: `slots(region_id, starts_at)`, `advertisements(business_id)`, `advertisements(region_id, status)`, `payments(advertisement_id, status)` y `advertisement_tag(tag_id, advertisement_id)`. El antiguo índice sobre `user_interest` deja de aplicar.

## 6. Flujos e invariantes

### Comprador

```text
entrar → resolver región → consultar slot vigente → renderizar creativo
       → mostrar tiempo restante → Comprar → canal externo del negocio
```

Región/intereses se conservan localmente; elegir memoria de sesión o almacenamiento persistente local sigue pendiente. La propuesta analytics añade registro de impresión al mostrarse el anuncio y clic antes de la redirección. `POST /ads/{id}/click` fue un ejemplo de ruta, no un contrato HTTP aprobado.

### Anunciante y publicación

Flujo propuesto a partir del modelo aceptado:

```text
login empresarial → empresa → borrador de anuncio
→ contenido + destino + región + tags → validación y moderación
→ aprobación → pago confirmado → asignación atómica al final de la cola
→ espera → slot de 60 segundos → finalización derivada del tiempo
```

El pago confirmado determina la entrada en la cola; un checkout abandonado no tiene slot. Moderar antes de cobrar se recomendó para no cobrar contenido rechazado; su manejo operativo completo no fue ratificado. La edición posterior, los rechazos y los reembolsos necesitan políticas antes de habilitar esos recorridos.

### Asignación transaccional

El diseño discutido valida la confirmación del proveedor, bloquea la asignación de la región, registra el pago, encuentra el final de su cola, crea un único slot y deja el anuncio programado en una transacción. Repetir un webhook no debe repetir la asignación. La validación concreta del proveedor depende de su elección.

Se propuso alinear los inicios al minuto exacto: con cola ocupada se usa el final del último slot; con cola vacía, el próximo minuto. Ejemplo propuesto: pago a las 18:04:37 → emisión 18:05:00–18:06:00. **La duración de 60 segundos está acordada; la alineación y la pantalla durante ese hueco son recomendaciones pendientes de confirmar.** También falta concretar el orden ante confirmaciones simultáneas o recibidas fuera de orden.

### Sincronización

El servidor determina el slot vigente. Entrar a las 18:00:37 en un slot 18:00:00–18:01:00 deja aproximadamente 23 segundos, no un minuto nuevo. Todos los visitantes de esa región consultan la misma programación. Los avisos realtime ayudan a representarla; la existencia del slot activo no depende de que un job cambie su estado exactamente en el segundo cero.

El protocolo de carga inicial, reconexión, corrección de reloj y transporte realtime aún debe concretarse. No se promete sincronización perfecta de red ni se considera Reverb ya elegido.

### Chatbot

El comprador expresa intereses → se normalizan contra tags → Laravel busca próximos slots de su región → calcula coincidencias y tiempo de espera → el chatbot presenta los resultados. El modelo no decide el orden de la cola ni inventa tiempos. Se propusieron operaciones controladas del backend para consultar los datos, no acceso libre del LLM a PostgreSQL.

### Invariantes aceptadas

1. Una única cola FIFO por región; como máximo un anuncio activo por región e instante.
2. `ends_at = starts_at + 60 segundos` y ausencia de solapamientos regionales.
3. `advertisement_id` único en slots; antes de tener turno puede no existir slot.
4. Tags e intereses no adelantan anuncios ni crean otra secuencia.
5. Confirmaciones repetidas de pago no generan slots duplicados.
6. PostgreSQL conserva programación y relaciones; transacciones/constraints protegen el dominio además del backend.
7. El comprador no requiere cuenta; el propietario empresarial se relaciona con sus anuncios por su negocio.
8. `Comprar` y el temporizador son controles de la plataforma.

Se propuso una exclusion constraint regional sobre rangos temporales para impedir solapamientos. La sintaxis y extensiones necesarias quedan para las migraciones. La respuesta final añadió como regla propuesta **un único creativo principal por anuncio**, compatible con que un HTML componga varios recursos; no equivale a limitar toda la tabla de assets a una sola fila.

## 7. Contenido, seguridad y moderación

### Formatos acordados

- Imagen: se muestra la imagen durante los 60 segundos del slot.
- Video: se reproduce el recurso como video dentro del visor.
- HTML IA: se genera desde backend y se presenta ajustado al contenedor de 60Market.

No se acordó subir HTML arbitrario como archivo del anunciante. Duración de video menor/mayor a 60 segundos, loop, recorte y límites de carga siguen abiertos.

### Recomendaciones de seguridad discutidas

El usuario planteó basar la seguridad de archivos en coincidencia de formatos y considerar segura la generación desde backend. La respuesta corrigió ambas suposiciones: la extensión no demuestra el contenido real y la salida de un LLM sigue requiriendo validación. Se documenta la corrección como **recomendación técnica presentada**, no como aprobación implícita de cada mecanismo.

Para imágenes/videos se propuso comprobar extensión permitida, MIME detectado, tamaño y decodificación/integridad. Se sugirieron JPEG, PNG y WebP para imágenes y MP4 para video, con límites de codec, resolución y duración por definir. Reprocesar/recodificar archivos fue una mejora propuesta.

Para HTML/CSS se propuso sanitización y restricciones: sin JavaScript, iframes aportados por el creativo, formularios, manejadores de eventos, redirecciones ni URLs arbitrarias. El aislamiento del preview se discutió desde V0; no debe confundirse prohibir un iframe dentro del creativo con decidir cómo la plataforma aísla su renderizador. El mecanismo de aislamiento y las reglas exactas de CSS/recursos no se cerraron.

No se debe presentar la generación backend como garantía de seguridad ni publicar HTML sin resolver estas recomendaciones. Su concreción es un pendiente previo a habilitar ese formato en producción.

### Moderación obligatoria; implementación propuesta

El objetivo acordado es impedir contenido explícito/inapropiado para toda la familia. Se propuso el pipeline:

```text
subida/generación → validación técnica → moderación → APPROVE / REVIEW / REJECT
```

Se sugirió normalizar categorías, puntuaciones y motivo, con umbrales propios. Las categorías discutidas incluyen sexual/NSFW, violencia gráfica, drogas, odio y contenido perturbador. Lista definitiva, umbrales, proveedor, revisión humana y manejo de fallos no están decididos.

- **Imágenes:** servicio/modelo de moderación visual.
- **Video:** muestreo de frames; cada cinco segundos fue un ejemplo, no una frecuencia aprobada. Puede omitir contenido breve. Audio/transcripción se mencionaron para cobertura más completa.
- **HTML IA:** moderar prompt antes de generar; después revisar texto extraído, HTML/CSS y recursos visuales incluidos, también los generados por IA.

Se distinguió moderación («¿está permitido?») de calidad publicitaria («¿es claro y útil?»). Nitidez, legibilidad, resolución, contraste, composición y claridad de oferta se propusieron para recomendaciones de calidad. No bloquear por baja calidad en el MVP fue una recomendación. No se acordó crear un servicio de calidad ni añadir `quality_score` al esquema V1.

## 8. Analytics

**Acordado:** estadísticas desde V1 y clics anónimos, sin identidad de comprador.

**Propuesto en el cierre:** `ad_impressions`, `ad_clicks`, conteos por anuncio/región y panel empresarial con visualizaciones, clics y `CTR = clics / impresiones × 100`. Esos eventos no demuestran compras realizadas en la web externa ni usuarios únicos.

**Pendiente:** qué constituye una impresión, cómo tratar refresh/reconexión, repetición de clics, CTR sin impresiones, protección frente a inflación de métricas, retención y definición final del panel. La agregación temporal se mencionó para después, según tráfico, sin introducirla en el modelo V1 como obligación.

## 9. Antecedentes V0 de generación

Antes del sistema Laravel se trabajó una demo Python/Streamlit. El usuario expresó satisfacción con la herramienta y pasó a la implementación real. Esto es evidencia conversacional del prototipo, no una verificación local de su funcionamiento.

Componentes ya mencionados: `app.py`, `services/ad_generator.py`, `services/validator.py`, `services/image_analyzer.py`, `services/image_generator.py`, `services/image_prompt_generator.py` y `prompts/ad_system_prompt.py`.

Aprendizajes y cambios tratados:

- Acumular la respuesta generada antes de renderizar HTML, en lugar de renderizar fragmentos del stream como documentos completos.
- Groq para generación textual/HTML y análisis visual; separar análisis de imágenes de generación de imágenes.
- Generación de imagen opcional mediante checkbox; integración solicitada de Cloudflare Worker + Workers AI/FLUX. Replicate había sido una referencia previa, no la elección posterior del experimento.
- El Worker utilizaba autenticación Bearer con secreto; se trató un error 401 por discrepancia de autorización. No se copian credenciales ni valores de ejemplo como configuración real.
- Combinar imágenes subidas, por ejemplo logo y producto, con imagen generada; no sustituir unas por otras.
- Placeholders `{{USER_IMAGE_1}}`, `{{USER_IMAGE_2}}`, `{{USER_IMAGE_3}}` y `{{AI_IMAGE}}`; el análisis individual solicitado asocia una descripción a cada imagen para guiar la composición.
- Se propuso validar que cada recurso aportado aparezca una vez y dejar al generador decidir jerarquía, tamaño y posición.
- El límite de tres imágenes y los nombres de modelos citados pertenecían a la demo. No son límites de V1 ni garantías vigentes de APIs.

No se decidió trasladar Streamlit, estos archivos Python, modelos concretos ni el Worker a la arquitectura Laravel. El diseño V1 puede aprovechar esos aprendizajes; su integración y proveedores están pendientes. Tampoco se decidió una arquitectura multiagente para producción.

## 10. Decisiones pendientes

| ID | Tema | Qué falta resolver |
|---|---|---|
| P01 | Stack y realtime | Ratificar complementos del stack, versiones, transporte y protocolo temporal/reconexión |
| P02 | Slots | **Alineación al minuto cerrada:** Acordada como obligatoria por el usuario (2026-10-06; estado: acordado, no implementado). Resta resolver: desempate y orden ante confirmaciones concurrentes/fuera de orden |
| P03 | Geografía | Límites y regiones iniciales, resolución geográfica, ausencia/denegación de ubicación, publicación fuera de región del negocio |
| P04 | Pagos | Stripe o Mercado Pago, tarifa/moneda, cancelaciones, reembolsos y tratamiento de fallos |
| P05 | Acceso empresarial | **Resuelto en Incremento 3:** Registro empresarial con creación de `Business`, login/logout, CRUD de borradores, tags y políticas de autorización por negocio (`AdvertisementPolicy`). |
| P06 | Multimedia | Tamaño, formatos/codecs definitivos, resolución, duración, reproducción de videos y procesamiento |
| P07 | HTML y assets | `generated_html`, creativo principal, `sort_order`, aislamiento y política HTML/CSS/URLs |
| P08 | Moderación | Proveedor/modelo, categorías, umbrales, REVIEW, revisión humana, cobertura de video/audio y fallos del servicio |
| P09 | IA | Proveedores de generación/chatbot/visión; reutilización del prototipo y alcance del evaluador de calidad |
| P10 | Analytics | Ratificación del detalle final de tablas/panel, impresión, duplicados, CTR con cero impresiones, retención |
| P11 | Estado anónimo | Duración y mecanismo del almacenamiento local de región/intereses |
| P12 | Operación | Hosting, email, observabilidad, despliegue, costos y límites reales de servicios |
| P13 | Esquema | **Resuelto para el modelo base en Incremento 1:** 11 tablas migradas en PostgreSQL con PostGIS, claves foráneas, restricciones de unicidad e índices acordados (D08). |
| P14 | Producto/mercado | Atracción y retención de compradores, validación con anunciantes y esperas largas |

Estos pendientes no autorizan introducir cupones, suscripciones, favoritos, notificaciones push persistentes, historial de compradores ni canales temáticos. Fueron oportunidades, alternativas o ejemplos de posibles necesidades futuras, no alcance aprobado.

## 11. Ideas de implementación incremental discutidas

La conversación propuso validar primero el visor y el motor de 60 segundos con anuncios de prueba, antes de depender de pagos, IA o panel completo. También propuso comenzar las migraciones por `regions → users/businesses → advertisements/assets → slots/tags → payments/analytics`. Son una guía de implementación, no evidencia de trabajo realizado.

Recorrido sugerido para continuar, sujeto a los pendientes de cada parte:

1. Revisar el repositorio real y traducir el modelo aceptado a migraciones, relaciones y restricciones. Resolver las diferencias de esquema que impidan esa parte.
2. Verificar el núcleo regional y el visor con datos de prueba: duración, no solapamiento, entrada a mitad del slot, regiones independientes y tiempo derivado del servidor.
3. Implementar acceso empresarial, empresas y preparación de anuncios según los formatos acordados.
4. Completar validación/moderación, pago y asignación idempotente de slots cuando estén resueltas sus políticas y proveedores.
5. Integrar región/intereses, tags y chatbot sobre la programación real; incorporar la generación de creativos según las decisiones de seguridad/IA.
6. Integrar analytics anónimos desde V1 y su presentación conforme se cierre su definición. Verificar el flujo completo y documentar lo implementado.

Las pruebas discutidas deben proteger especialmente pagos concurrentes, webhooks repetidos, slots exactos sin solapamientos y espectadores regionales sincronizados. No se considera terminada una función por tener interfaz, mocks o un proveedor simulado. Tampoco se habilita publicación de contenido sin el filtro obligatorio.

## 12. Riesgos y continuidad documental

El diagnóstico identifica captación de audiencia, conversión, disposición a esperar, cobertura de moderación y sincronización como riesgos. Son aspectos por validar, no afirmaciones de éxito. Los supuestos comerciales no deben convertirse en nuevas funcionalidades automáticamente.

Para mantener trazabilidad durante la implementación:

- Conservar D01–D09 como referencias de decisiones; asignar identificadores nuevos solo a decisiones nuevas expresamente resueltas.
- Mantener propuestas y pendientes aquí. Registrar su resolución y su origen cuando ocurra.
- Actualizar README en el mismo incremento que materializa una decisión o cambio: describir el comportamiento real y añadir evidencia verificable de implementación vinculada a su decisión.
- Distinguir `acordado, no verificado`, `parcial` e `implementado y verificado` al registrar avances; no equiparar acuerdo y ejecución.
- Registrar en este contexto el historial de cambios de decisión: decisión anterior, nueva decisión, motivo, autorización y relación con el incremento. El README mantiene únicamente el contrato vigente y avances comprobados asociados a él.
- Usar referencias reales a archivos, pruebas o commits cuando existan. No inventar hashes, fechas de implementación ni resultados.

### Historial de cambios y resoluciones de pendientes
- **2026-10-06 (Incremento 1 — Inicialización y Modelo de Datos):**
  - **P02:** El usuario resuelve explícitamente que la política de alineación a minuto cerrado es obligatoria. Estado: *acordado, no implementado* (diferido a la fase del visor/cola en vivo).
  - **P13:** Esquema relacional base acordado implementado en migraciones Laravel y probado en PostgreSQL 16 con PostGIS y Redis.
  - **Infraestructura dev:** Acordado y verificado uso de `docker-compose.yml` para PostgreSQL 16 (PostGIS) y Redis local.
  - **Invariantes:** Verificadas mediante suite automatizada `tests/Feature/DomainModelTest.php` (8 pruebas, 22 aserciones exitosas).
  - **Directriz documental:** El usuario instruye tratar `README.md` como documentación técnica convencional (arquitectura, decisiones vigentes D01–D09, versiones, stack, modelo relacional y estado de implementación comprobado), depurando contenido obsoleto en cada cambio.
- **2026-10-06 (Incremento 2 — Núcleo Regional y Visor Básico):**
  - **D02 y D04 implementados:** Visor web responsivo con cuenta regresiva en vivo de 60 s (`resources/views/viewer.blade.php`), consulta de slot activo en servidor (`ViewerService.php`, `ViewerController.php`), entrada a mitad de slot sin reinicio de tiempo.
  - **D01 y D09 implementados:** Botón `Comprar` con redirección externa al canal del anunciante (`/ads/{id}/click/{region}`) y registro de clics e impresiones anónimos sin `user_id`.
  - **Población de prueba:** `DatabaseSeeder.php` con regiones, anunciantes y slots secuenciales de 60 s.
  - **Pruebas de comportamiento:** `tests/Feature/ViewerTest.php` (5 pruebas de integración, 13 pruebas totales pasando con 40 aserciones).
  - **Refinamiento de identidad y diseño:** Integración del logotipo oficial `60Logo.png`, cambio de nombre público a «Un Minuto Para Vender», paleta cromática oficial (#F05500, #E8955D, #FEFEFE, #4C6189, #032765) con botón `Comprar` fijado obligatoriamente en `#F05500`, y rediseño inmersivo: el anuncio ocupa toda la pantalla con el contador y CTA reubicados en el dock inferior.

- **2026-10-06 (Incremento 3 — Módulo y Panel de Acceso Empresarial):**
  - **D06 implementado:** Autenticación y registro exclusivo para anunciantes (`AuthController.php`, `users` asociado a `Business`), login con credenciales seguras y cierre de sesión.
  - **D07 y D08 implementados:** Panel de administración de anuncios (`AdvertisementController.php`), CRUD de borradores (`draft`), asignación geográfica de región (`region_id`), clasificación con etiquetas temáticas (`Tag`) y previsualización interactiva de 60 segundos en el reproductor.
  - **Políticas de autorización (D07, D08):** `AdvertisementPolicy.php` garantiza que un anunciante solo pueda ver, modificar o eliminar sus propios anuncios y bloquea la edición directa de anuncios en cola (`queued`) o activos (`active`).
  - **P05 resuelto:** Registro, verificación y administración de borradores implementados y verificados.
- **2026-10-06 (Incremento 4 — Programación de Turnos, Pasarela de Pago Sandbox y Encolado FIFO):**
  - **D01, D03, D04 y D08 implementados:** Asignación atómica de turnos de 60 segundos al final de la cola regional al confirmarse el pago. Garantía transaccional de no solapamiento mediante bloqueo pesimista en PostgreSQL (`lockForUpdate()`) e invariante de unicidad de slot por anuncio.
  - **Pasarela de pago Sandbox Mercado Pago:** `MercadoPagoSandboxService.php` para generación de preferencias, checkout sandbox interactivo (`resources/views/dashboard/checkout/show.blade.php`), simulación de pago aprobado/rechazado y registro formal en tabla `payments`.
  - **Regla de negocio de cobro único y reedición con IA:** Generaciones ilimitadas en sesión durante la creación del borrador; si el anunciante edita el anuncio tras guardar el borrador, se activa la bandera `requires_ai_recharge` aplicando nuevamente la tarifa de IA en el checkout.
  - **Acción directa "Comprar Slot":** Disponible tanto en la creación/edición del anuncio (`submit_action = 'buy_slot'`) como en la tabla del panel principal.
  - **Visualización inmediata en la página principal:** Tras la adquisición, si la cola está libre, el anuncio inicia de inmediato (`starts_at = now()`) y se proyecta en el visor en vivo (`/` o `/r/{region}`) con soporte SSR y cliente, reproduciendo el creativo (HTML/Imagen/Video) con temporizador de 60 segundos y botón `Comprar`.
  - **Pruebas de comportamiento:** `tests/Feature/SlotSchedulingAndPaymentTest.php` (7 pruebas nuevas; suite completa: 33 pruebas y 131 aserciones pasando al 100%).

- **2026-10-24 (Incremento 5 — Módulo de Pre-registro y Anuncio Fallback):**
  - **Tabla `campaign_leads` (D02):** Migración y modelo `CampaignLead` creados. Campos: `email` (nullable), `phone` (nullable), `user_type` (enum: `merchant`/`shopper`), `region_id` (FK nullable a `regions`). Al menos un canal de contacto es requerido en el endpoint; el comprador sige siendo anónimo.
  - **Endpoint de captura:** `CampaignLeadController@store` con ruta pública `POST /campaign/pre-register`. Responde en JSON. Valida que al menos `email` o `phone` estén presentes.
  - **Anuncio Fallback (cola vacía):** `viewer.blade.php` modificado — cuando no hay slot activo, se muestra el creativo de pre-lanzamiento "Próximamente… Un Minuto Para Vender" tanto en el render inicial PHP como en la actualización dinámica por JavaScript.
  - **Modal de pre-registro:** Formulario embebido con selector de rol, campos de email/teléfono, envío AJAX con CSRF y retroalimentación inmediata. Botón de acción en `#F05500`. Enlace secundario a `/register` para comerciantes.
  - **Verificado:** Migración ejecutada en PostgreSQL (`DONE 15.91ms`). Ruta confirmada en `php artisan route:list`.

- **2026-10-26 (Incremento 5.1 — Pre-registros unificados, panel y exportación):**
  - **Tabla `pre_registrations`:** sustituye funcionalmente a `campaign_leads` para el modal del visor. `contact` único (correo en minúsculas o teléfono `+?\d{8,15}`), `region_id` FK nullable con `nullOnDelete`, `user_type` opcional, índice único `(contact, region_id)`. La migración copia los leads de `campaign_leads`; esa tabla y su endpoint `POST /campaign/pre-register` se conservan como legado (pendiente decidir su retiro).
  - **Endpoint `POST /pre-register`:** ruta web (CSRF activo; `api/*` está exento de CSRF, por eso no se usó `/api/pre-register`) con `throttle:10,1`. 201 creado · 200 duplicado · 422 validación. `Log::info` con contacto enmascarado.
  - **Panel `/dashboard/pre-registros` + export CSV:** restringido por Gate `view-pre-registrations` a `config('app.admin_emails')` (variable `ADMIN_EMAILS`). Decisión: no existían roles de administrador y todo usuario autenticado es comercio; sin el Gate, cualquier comercio vería todos los contactos.
  - **Verificado:** 12 pruebas nuevas (52 aserciones) en PostgreSQL/PostGIS; suite completa 44/45 (el fallo restante requiere la extensión GD local, ajeno a este cambio). Backfill y rollback verificados.

Estado actual: **Incremento 5.1 completado y verificado.** Próximo paso: Incremento 6 (Chatbot de descubrimiento de ofertas por tags — sujeto a la resolución de P09).
