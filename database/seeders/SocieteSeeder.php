<?php

namespace Database\Seeders;

use App\Models\Societe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SocieteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider la table des sociétés
        Societe::truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Générer 40 sociétés simplement avec fake()
        for ($i = 0; $i < 20; $i++) {
            Societe::create([
                'name' => fake('fr_FR')->unique()->company(),
                'secteur' => fake('fr_FR')->randomElement([
                    'Technologie','Finance','Santé','Agriculture','Éducation',
                    'Transport','Construction','Tourisme','Commerce','Services'
                ]),
                'nif' => 'NIF' . fake()->numerify('########'),
                'stat' => 'STAT' . fake()->numerify('########'),
                'adresse' => fake('fr_FR')->streetAddress(),
                'telephone' => '+261 ' . fake()->numerify('## ## ### ##'),
                'email' => fake()->unique()->companyEmail(),
                'status' => fake()->randomElement(['Actif','Inactif','Suspendu'])
            ]);
        }

        $this->command->info('40 sociétés créées avec succès avec fake().');
    }
}
