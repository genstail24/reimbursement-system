<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Transportasi',
                'limit_per_month' => 1000000,
            ],
            [
                'name' => 'Kesehatan',
                'limit_per_month' => 1500000,
            ],
            [
                'name' => 'Makan',
                'limit_per_month' => 750000,
            ],
            [
                'name' => 'Internet',
                'limit_per_month' => 500000,
            ],
            [
                'name' => 'Akomodasi',
                'limit_per_month' => 2000000,
            ],
            [
                'name' => 'Lainnya',
                'limit_per_month' => 1000000,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['limit_per_month' => $category['limit_per_month']]
            );
        }
    }
}
