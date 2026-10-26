<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla pre_registrations (módulo "Avisarme del lanzamiento").
 *
 * Unifica el contacto (correo o teléfono) en una sola columna `contact` y lo
 * asocia a la región que el visitante estaba viendo en el visor público.
 *
 * Backfill: copia los leads ya capturados en `campaign_leads` (Incremento 5)
 * para no perder contactos de producción. La tabla original NO se elimina.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pre_registrations', function (Blueprint $table) {
            $table->id();

            // Correo (normalizado a minúsculas) o teléfono (solo dígitos y '+')
            $table->string('contact', 255);

            // Región del visor. nullOnDelete: borrar una región no destruye los leads.
            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            // 'shopper' (comprador) | 'merchant' (comercio local). Opcional.
            $table->string('user_type', 20)->nullable();

            $table->timestamps();

            // Evita duplicados del mismo contacto en la misma región
            $table->unique(['contact', 'region_id']);
            $table->index('created_at');
        });

        $this->backfillFromCampaignLeads();
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_registrations');
    }

    /**
     * Copia los registros de campaign_leads → pre_registrations (idempotente).
     */
    private function backfillFromCampaignLeads(): void
    {
        if (! Schema::hasTable('campaign_leads')) {
            return;
        }

        DB::table('campaign_leads')->orderBy('id')->chunk(500, function ($leads) {
            $rows = [];

            foreach ($leads as $lead) {
                $contact = $lead->email
                    ? strtolower(trim($lead->email))
                    : preg_replace('/[^\d+]/', '', (string) $lead->phone);

                if ($contact === '' || $contact === null) {
                    continue;
                }

                $rows[] = [
                    'contact' => $contact,
                    'region_id' => $lead->region_id,
                    'user_type' => $lead->user_type,
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->updated_at,
                ];
            }

            // ON CONFLICT DO NOTHING: respeta el índice único (contact, region_id)
            DB::table('pre_registrations')->insertOrIgnore($rows);
        });
    }
};
