<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MasterSeeder::class);
        if (config('sibimta.seed_demo') && filter_var(config('sibimta.seed_demo'), FILTER_VALIDATE_BOOLEAN) && Mahasiswa::count() === 0) {
            $this->call(DemoSeeder::class);
        }
    }
}
