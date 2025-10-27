<?php

use Modules\Adherent\Models\Societe;
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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            //Infos Personnelles
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('sexe')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            //Profession
            $table->foreignIdFor(Societe::class)->nullable()->constrained();
            $table->date('date_debut')->nullable();
            $table->string('poste')->nullable();
            $table->string('status')->nullable();
            $table->date('date_fin')->nullable();
            //Adhesion
            $table->date('date_adhesion')->nullable();
            $table->string('type_adhesion')->nullable();
            $table->string('numero')->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
