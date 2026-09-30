<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Fiksi', 'deskripsi' => 'Buku yang mengandung cerita rekaan'],
            ['nama_kategori' => 'Non-Fiksi', 'deskripsi' => 'Buku berbasis fakta dan kejadian nyata'],
            ['nama_kategori' => 'Fantasi', 'deskripsi' => 'Buku dengan unsur keajaiban dan dunia imaginatif'],
            ['nama_kategori' => 'Science Fiction', 'deskripsi' => 'Buku bertema ilmu pengetahuan dan futuristik'],
            ['nama_kategori' => 'Roman', 'deskripsi' => 'Buku dengan cerita cinta dan hubungan manusia'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
