<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa a tabela antes de popular para evitar duplicados.
        Product::truncate();

        // Criando alguns produtos de exemplo.
        Product::create([
            'name' => 'Laptop Gamer Pro',
            'base_price' => 7500.00,
            'description' => 'Laptop de alta performance para jogos e trabalho pesado.'
        ]);

        Product::create([
            'name' => 'Mouse Vertical Ergonômico',
            'base_price' => 250.50,
            'description' => 'Mouse desenhado para reduzir a tensão no pulso.'
        ]);

        Product::create([
            'name' => 'Teclado Mecânico RGB',
            'base_price' => 499.90,
            'description' => 'Teclado com switches mecânicos e iluminação customizável.'
        ]);
    }
}
