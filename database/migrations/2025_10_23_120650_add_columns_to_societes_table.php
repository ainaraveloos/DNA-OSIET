<?php

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
        Schema::table('societes', function (Blueprint $table) {
            $table->string("name")->nullable();
            $table->string('secteur')->nullable();
            $table->string("nif")->nullable();
            $table->string("stat")->nullable();
            $table->string("adresse")->nullable();
            $table->string("telephone")->nullable();
            $table->string("email")->nullable();
            $table->string("date_adhesion")->nullable();
            $table->string("nom_contact")->nullable();
            $table->string("status")->nullable();

            //Nombre d'employé
            $table->integer('nbre_employe')->nullable();
            // LOGO du societe
            $table->string('img')->nullable();
            $table->string('thumb_img')->nullable();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('societes');
    }
};
