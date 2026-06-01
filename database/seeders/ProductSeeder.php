<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $marvel = Category::where('slug', 'marvel')->first();
        $dcComics = Category::where('slug', 'dc-comics')->first();
        $starWars = Category::where('slug', 'star-wars')->first();
        $pokemon = Category::where('slug', 'pokemon')->first();

        $products = [
            // Marvel
            [
                'name' => 'Spider-Man Pop',
                'slug' => 'spider-man-pop',
                'description' => 'Figura Pop de Spider-Man',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 50,
                'category_id' => $marvel->id,
                'image' => 'https://via.placeholder.com/200x300?text=Spider-Man',
                'active' => true,
            ],
            [
                'name' => 'Iron Man Pop',
                'slug' => 'iron-man-pop',
                'description' => 'Figura Pop de Iron Man',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 45,
                'category_id' => $marvel->id,
                'image' => 'https://via.placeholder.com/200x300?text=Iron+Man',
                'active' => true,
            ],
            [
                'name' => 'Thor Pop',
                'slug' => 'thor-pop',
                'description' => 'Figura Pop de Thor',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 40,
                'category_id' => $marvel->id,
                'image' => 'https://via.placeholder.com/200x300?text=Thor',
                'active' => true,
            ],
            [
                'name' => 'Avengers Set',
                'slug' => 'avengers-set',
                'description' => 'Set de 4 Funko Pops de los Avengers',
                'price' => 49.99,
                'cost' => 25.00,
                'stock' => 20,
                'category_id' => $marvel->id,
                'image' => 'https://via.placeholder.com/200x300?text=Avengers+Set',
                'active' => true,
            ],
            
            // DC Comics
            [
                'name' => 'Batman Pop',
                'slug' => 'batman-pop',
                'description' => 'Figura Pop de Batman',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 55,
                'category_id' => $dcComics->id,
                'image' => 'https://via.placeholder.com/200x300?text=Batman',
                'active' => true,
            ],
            [
                'name' => 'Superman Pop',
                'slug' => 'superman-pop',
                'description' => 'Figura Pop de Superman',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 50,
                'category_id' => $dcComics->id,
                'image' => 'https://via.placeholder.com/200x300?text=Superman',
                'active' => true,
            ],
            [
                'name' => 'Wonder Woman Pop',
                'slug' => 'wonder-woman-pop',
                'description' => 'Figura Pop de Wonder Woman',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 35,
                'category_id' => $dcComics->id,
                'image' => 'https://via.placeholder.com/200x300?text=Wonder+Woman',
                'active' => true,
            ],
            
            // Star Wars
            [
                'name' => 'Darth Vader Pop',
                'slug' => 'darth-vader-pop',
                'description' => 'Figura Pop de Darth Vader',
                'price' => 15.99,
                'cost' => 8.50,
                'stock' => 60,
                'category_id' => $starWars->id,
                'image' => 'https://via.placeholder.com/200x300?text=Darth+Vader',
                'active' => true,
            ],
            [
                'name' => 'Yoda Pop',
                'slug' => 'yoda-pop',
                'description' => 'Figura Pop de Yoda',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 42,
                'category_id' => $starWars->id,
                'image' => 'https://via.placeholder.com/200x300?text=Yoda',
                'active' => true,
            ],
            [
                'name' => 'Baby Yoda Pop',
                'slug' => 'baby-yoda-pop',
                'description' => 'Figura Pop de Baby Yoda',
                'price' => 16.99,
                'cost' => 9.00,
                'stock' => 70,
                'category_id' => $starWars->id,
                'image' => 'https://via.placeholder.com/200x300?text=Baby+Yoda',
                'active' => true,
            ],
            
            // Pokemon
            [
                'name' => 'Pikachu Pop',
                'slug' => 'pikachu-pop',
                'description' => 'Figura Pop de Pikachu',
                'price' => 13.99,
                'cost' => 7.50,
                'stock' => 80,
                'category_id' => $pokemon->id,
                'image' => 'https://via.placeholder.com/200x300?text=Pikachu',
                'active' => true,
            ],
            [
                'name' => 'Charizard Pop',
                'slug' => 'charizard-pop',
                'description' => 'Figura Pop de Charizard',
                'price' => 14.99,
                'cost' => 8.00,
                'stock' => 38,
                'category_id' => $pokemon->id,
                'image' => 'https://via.placeholder.com/200x300?text=Charizard',
                'active' => true,
            ],
            [
                'name' => 'Mewtwo Pop',
                'slug' => 'mewtwo-pop',
                'description' => 'Figura Pop de Mewtwo',
                'price' => 16.99,
                'cost' => 9.00,
                'stock' => 25,
                'category_id' => $pokemon->id,
                'image' => 'https://via.placeholder.com/200x300?text=Mewtwo',
                'active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
