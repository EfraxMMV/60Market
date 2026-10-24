# Diagnóstico Estratégico y Técnico Inicial: 60Market — Un Minuto Para Vender

A partir del análisis exhaustivo del documento fundacional `1MPV.md`, se construye el modelo mental integral del negocio, producto, arquitectura tecnológica, operaciones y riesgos.

---

### A. Qué es 60Market (Un Minuto Para Vender)

* **[HECHO]** Es una plataforma web desarrollada por la empresa **60Market** bajo el lema *"Un minuto puede cambiar una venta"*. 
* **[HECHO]** Su mecanismo central consiste en que los anunciantes pagan una suma de dinero para exhibir un anuncio comercial durante **exactamente sesenta segundos (60 s)** en la plataforma.
* **[HECHO]** Los usuarios visualizan el anuncio junto a un botón transaccional directo (`Comprar` / `Apartar`) que los redirige inmediatamente al enlace de venta o canal digital de la empresa anunciante.

---

### B. Problema que resuelve

* **[HECHO]** Dificultad crítica de los pequeños negocios y PyMES para obtener **visibilidad digital efectiva** y convertir esa exposición en **oportunidades reales de venta** dentro de su mercado local.
* **[HECHO]** Saturación y competencia por la atención: En México existen 110 millones de internautas y el comercio digital generó 8.27 billones de pesos en 2023 (donde las PyMES representan el 50.1% de este valor), pero las PyMES enfrentan barreras severas para adoptar herramientas complejas de marketing digital (respaldado en INEGI 2024, DataReportal 2025 y OCDE 2021).
* **[HECHO]** Falta de un mecanismo directo e inmediato que conecte un anuncio publicitario con consumidores interesados que se encuentren dentro del área de cobertura geográfica del negocio, eliminando la fricción entre el descubrimiento del producto y la acción de compra.

---

### C. Solución propuesta

* **[HECHO] Micro-publicidad temporal:** Espacios publicitarios estrictamente delimitados a 60 segundos exactos.
* **[HECHO] Fila de espera secuencial (*Query*):** Los anunciantes compran un espacio y entran en una cola ordenada hasta que llega su turno de aparecer en pantalla.
* **[HECHO] Segmentación geográfica:** Geolocalización por ciudad o radio geográfico determinado para garantizar relevancia local.
* **[HECHO] Emparejamiento por intereses (*Tags*):** Clasificación por etiquetas temáticas (ej. maquillaje, deportes, belleza).
* **[HECHO] Asistente virtual / Chatbot:** Un bot interactivo que pregunta a los visitantes sus intereses y les notifica cuándo saldrá un anuncio afín (*"estará por salir en X minutos"*).
* **[HECHO] Enlace transaccional sin intermediación compleja:** Botón directo (`Comprar` / `Apartar`) hacia la pasarela o canal externo del anunciante.
* **[HECHO] Flexibilidad creativa:** Posibilidad de proyectar imagen, video o contenido generado mediante un *prompt*.

---

### D. Usuarios y clientes

* **[HECHO] Clientes (quienes pagan):** Negocios locales, PyMES y emprendedores que buscan ventas directas y visibilidad sin la complejidad de plataformas publicitarias tradicionales.
* **[HECHO] Usuarios (quienes consumen):** Compradores digitales que navegan buscando ofertas en su localidad o área circundante.
* **[HECHO] Equipo y asesores del proyecto:**
  * Jetzael Yeray Anguiano Solis (FIME) — Desarrollador e investigador.
  * Efraín Alejandro Arias Meza (FIME) — Desarrollador e investigador.
  * Citlalli Sarai Rivera Escareño (FIME) — Desarrollador e investigador.
  * Milton Tinoco Delgadillo (FCAC) — Contador asesor.
  * Dr. Walter Alexander Mata López (FIME) — Doctor asesor del proyecto.

---

### E. Producto

* **[HECHO]** Plataforma web responsiva orientada a la visualización de anuncios en tiempo real de 1 minuto continuo.
* **[HECHO]** Panel para anunciantes: Carga de recursos (imágenes, videos, prompts), definición de etiquetas (*tags*), configuración del enlace de compra y pago del slot.
* **[HECHO]** Módulo de moderación: Filtro para excluir contenido explícito o inapropiado antes de su publicación.
* **[DECISIÓN]** La duración del anuncio está rígidamente fijada en 60 segundos.
* **[DECISIÓN]** La logística de despacho de productos, cobranza final y atención postventa es 100% responsabilidad del anunciante.

---

### F. Componentes de software

* **[HECHO]** Interfaz web para el espectador (reproductor/visualizador del anuncio con temporizador de 60 s y botón `Comprar`/`Apartar`).
* **[HECHO]** Motor de cola / fila (*queue system*) para gestionar el orden de proyección cronológica de los anuncios.
* **[HECHO]** Módulo de geolocalización (detección de ubicación por navegador o IP para filtrar por ciudad/radio).
* **[HECHO]** Sistema de etiquetado y *pairing* (*tags* por producto/categoría).
* **[HECHO]** Chatbot interactivo para consulta de intereses de usuario y cálculo de tiempo de espera (*"saldrá en X minutos"*).
* **[HECHO]** Filtro de contenido y moderación.
* **[PREGUNTA ABIERTA]** El stack tecnológico específico (framework de frontend, motor de backend, base de datos, motor de WebSockets/SSE para sincronización en tiempo real y pasarela de pago) aún no ha sido establecido en `1MPV.md`.

---

### G. Componentes de hardware

* **[HECHO]** `1MPV.md` no define hardware físico dedicado, robótico ni mecatrónico.
* **[HECHO]** El sistema opera en arquitectura cliente-servidor estándar: servidores/cloud para la plataforma y dispositivos finales (smartphones, tablets, computadoras) para los usuarios y anunciantes. No se requiere manufactura de hardware.

---

### H. Modelo de negocio identificado

* **[HECHO]** Modelo publicitario de pago por espacio temporal (Pay-per-Slot): el anunciante paga una tarifa por cada minuto de exposición.
* **[HECHO]** La plataforma no cobra comisión sobre la venta final del producto (el botón redirige al link propio del negocio).
* **[SUPUESTO]** Se asume que el volumen de anunciantes pagando por minuto generará ingresos suficientes para cubrir infraestructura y rentabilidad.
* **[PREGUNTA ABIERTA]** Esquema de pricing: ¿tarifa plana por minuto, o precios dinámicos según ciudad, horario de tráfico (horas pico) o demanda de la categoría?

---

### I. Objetivos identificados

* **[HECHO]** Reducir la distancia entre la publicidad digital y la conversión comercial para PyMES mexicanas.
* **[HECHO]** Ofrecer una alternativa accesible, directa y sin barreras técnicas frente a los ecosistemas publicitarios hipercompetitivos (Meta Ads, Google Ads).
* **[HECHO]** Fomentar el dinamismo económico local aprovechando la geolocalización.

---

### J. Decisiones ya tomadas

1. **[DECISIÓN]** Formato estricto de 60 segundos por anuncio.
2. **[DECISIÓN]** Modelo de asignación mediante cola secuencial (*query*).
3. **[DECISIÓN]** Criterios de filtrado: geolocalización (ciudad/radio) y etiquetas (*tags*).
4. **[DECISIÓN]** Inclusión de un asistente virtual (chatbot) para alertar sobre anuncios próximos afines.
5. **[DECISIÓN]** Transacción externa: no intermediar el cobro del producto; canalizar tráfico directamente al anunciante.
6. **[DECISIÓN]** Existencia obligatoria de un filtro de contenido no apto o explícito.

---

### K. Supuestos detectados

* **[SUPUESTO 1 — Retención de usuarios]:** Se asume que los compradores querrán entrar y permanecer en una página web viendo anuncios continuos de 1 minuto o esperando que el chatbot les avise.
* **[SUPUESTO 2 — Capacidad del anunciante]:** Se asume que las PyMES locales cuentan con un canal transaccional funcional (WhatsApp Business, Mercado Libre, Shopify, terminal de pago virtual) al cual redirigir.
* **[SUPUESTO 3 — Conversión en 60 segundos]:** Se asume que 60 segundos es un lapso adecuado para generar el deseo de compra y lograr el clic en `Comprar`/`Apartar`.
* **[SUPUESTO 4 — Generación por Prompt]:** Se asume que permitir a los comercios subir un "prompt" para generar contenido publicitario generará anuncios de calidad comercial suficiente sin supervisión manual intensiva.
* **[SUPUESTO 5 — Escalabilidad de la cola única]:** Se asume que una fila secuencial es viable; sin embargo, si 120 negocios compran un minuto en la misma ciudad para la misma tarde, el último en la fila tendrá una espera de 2 horas.

---

### L. Preguntas abiertas

1. **Dilema del Marketplace (Huevo o Gallina):** ¿Cómo se atraerá y retendrá a la audiencia de compradores antes de que los anunciantes estén dispuestos a pagar por el minuto?
2. **Modelo de Cola:** ¿Habrá múltiples canales temáticos simultáneos por ciudad o existirá una sola pantalla centralizada?
3. **Mecanismo de Pago:** ¿Qué procesador se utilizará para que el anunciante pague su minuto (ej. Stripe, Mercado Pago, SPEI)?
4. **Definición del Prompt:** Cuando un anunciante usa un "prompt", ¿se refiere a generar una imagen/video con IA o a un generador de texto/copy publicitario?
5. **Stack de desarrollo:** ¿Qué tecnologías domina el equipo de desarrollo de FIME para priorizar velocidad de entrega en el MVP?

---

### M. Riesgos principales

* **Riesgo Comercial / Adopción (Alto):** Si la web no cuenta con espectadores constantes en la ciudad del anunciante, el negocio pagará por 60 segundos frente a cero ojos reales, provocando abandono inmediato.
* **Riesgo Operativo / Moderación (Medio-Alto):** Si un anunciante sube un video o prompt inapropiado o fraudulento y el sistema automatizado falla, el contenido saldrá en vivo dañando la reputación de la plataforma.
* **Riesgo Técnico de Sincronización (Medio):** Mantener a múltiples clientes web sincronizados al segundo exacto (00:01 a 00:60) requiere una arquitectura en tiempo real robusta (WebSockets / Server-Sent Events) y CDN optimizada.
* **Riesgo de Experiencia de Usuario (Medio):** Si el tiempo de espera en cola es excesivo, el anunciante pierde el control sobre a qué hora exacta se visualiza su oferta.

---

### N. Dependencias principales

* Servicio o API de geolocalización confiable en navegadores modernos.
* Pasarela de pagos para el cobro ágil del slot a los anunciantes.
* Infraestructura de almacenamiento y entrega de video/imágenes de baja latencia (Storage + CDN).
* Enlaces de conversión externos proporcionados por los anunciantes.

---

### O. Oportunidades de mejora

* **[PROPUESTA] Ofertas Flash / Gamificación para el comprador:** Dar al usuario una razón real para estar en la web. Por ejemplo: cupones exclusivos que solo son válidos durante los 60 segundos que dura el anuncio.
* **[PROPUESTA] Canales temáticos paralelos:** En lugar de una sola fila secuencial por ciudad, dividir en categorías simultáneas (ej. Canal Alimentos, Canal Moda, Canal Servicios) para multiplicar el inventario publicitario disponible por hora.
* **[PROPUESTA] Métricas para el negocio:** Entregar al anunciante un reporte simple al término de su minuto: cuántas personas vieron su anuncio y cuántas hicieron clic en `Comprar`/`Apartar`.

---

### P. Nivel de madurez actual

* **Nivel: Concepto / Especificación Inicial.**
* **Justificación:** El proyecto cuenta con un planteamiento claro del problema, justificación estadística del mercado mexicano, definición funcional básica y un equipo interdisciplinario conformado. Sin embargo, no cuenta aún con arquitectura de software implementada, prototipo navegable, diseño de interfaces (UI/UX) ni validación de mercado con anunciantes reales.

---

### Q. Prioridades actuales

1. **Definición de incentivo para compradores:** Definir la mecánica para que el usuario consumidor permanezca en la plataforma (ej. ofertas irresistibles, descuentos por tiempo limitado).
2. **Diseño y arquitectura del MVP:** Establecer el stack tecnológico más sencillo y rápido de implementar para el equipo (evitando sobreingeniería).
3. **Flujo de usuario y Mockup básico:** Diseñar el viaje del anunciante (crear anuncio y pagar) y la experiencia del comprador (ver anuncio de 60 s y comprar).
4. **Mecánica de la cola en tiempo real:** Diseñar cómo funciona técnicamente el sistema de turnos y la sincronización del minuto.

---

### R. Resumen ejecutivo

**60Market — Un Minuto Para Vender** propone transformar la visibilidad de PyMES locales en México mediante un formato efímero y directo de 60 segundos con botón de compra integrado, reduciendo la distancia entre descubrimiento y conversión.

El rol del agente es actuar como analista de negocio, arquitecto de software y estratega de producto, cuidando no sobreingeniar, señalando riesgos a tiempo y acompañando la construcción técnica y comercial del MVP conforme a las directrices del fundador.
