<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CategorySeeder::class,
        ]);

        if (User::count() === 0) {
            User::create([
                'name' => 'Petugas Perpustakaan',
                'email' => 'petugas@perpus.id',
                'password' => bcrypt('password'),
                'role' => 'petugas',
            ]);
        }
    }
}
