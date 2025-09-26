<?php

namespace Database\Seeders;

use App\Models\Wish;
use Illuminate\Database\Seeder;

class WishSeeder extends Seeder
{
    public function run(): void
    {
        Wish::factory(20)->create();
    }
}

