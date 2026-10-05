<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(StoreSeeder::class);
    }
}
