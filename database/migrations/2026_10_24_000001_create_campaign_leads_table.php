<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla campaign_leads (Incremento 5 — Módulo de Pre-registro)
 *
 * Registra interacciones de visitantes (compradores y comercios) que expresan
 * interés en la plataforma antes de su lanzamiento oficial.
 * D02: comprador anónimo — ningún campo de identidad es obligatorio.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_leads', function (Blueprint $table) {
            $table->id();

            // Datos de contacto opcionales (D02: comprador anónimo)
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();

            // Tipo de actor que se registra
            $table->enum('user_type', ['merchant', 'shopper']);

            // Región de interés (nullable: puede no haberse resuelto aún — P03)
            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            $table->timestamps();

            // Índices de consulta
            $table->index('user_type');
            $table->index('region_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_leads');
    }
};
