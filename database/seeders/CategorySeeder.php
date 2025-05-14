<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Eletrônicos', 'slug' => 'eletronicos'],
            ['name' => 'Roupas', 'slug' => 'roupas'],
            ['name' => 'Calçados', 'slug' => 'calcados'],
            ['name' => 'Acessórios', 'slug' => 'acessorios'],
            ['name' => 'Casa e Decoração', 'slug' => 'casa-decoracao'],
            ['name' => 'Esportes', 'slug' => 'esportes'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
