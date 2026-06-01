<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Marvel',
                'slug' => 'marvel',
                'description' => 'Personajes de Marvel Comics y películas',
                'image' => 'https://via.placeholder.com/300x300?text=Marvel',
            ],
            [
                'name' => 'DC Comics',
                'slug' => 'dc-comics',
                'description' => 'Personajes de DC Comics',
                'image' => 'https://via.placeholder.com/300x300?text=DC+Comics',
            ],
            [
                'name' => 'Star Wars',
                'slug' => 'star-wars',
                'description' => 'Personajes de la saga Star Wars',
                'image' => 'https://via.placeholder.com/300x300?text=Star+Wars',
            ],
            [
                'name' => 'Pokemon',
                'slug' => 'pokemon',
                'description' => 'Personajes de Pokemon',
                'image' => 'https://via.placeholder.com/300x300?text=Pokemon',
            ],
            [
                'name' => 'Anime',
                'slug' => 'anime',
                'description' => 'Personajes de Anime popular',
                'image' => 'https://via.placeholder.com/300x300?text=Anime',
            ],
            [
                'name' => 'Disney',
                'slug' => 'disney',
                'description' => 'Personajes de Disney',
                'image' => 'https://via.placeholder.com/300x300?text=Disney',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
