<?php

use Modules\Adherent\Models\Salarie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Salarie::class)->nullable()->constrained();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('sexe')->nullable();
            $table->string('lien_parente')->nullable();
            $table->date('date_naissance')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaires');
    }
};
