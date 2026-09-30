<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PertemuanDuaSampaiEmpatTest extends TestCase
{
    use RefreshDatabase;

    public function test_pertemuan_dua_routes_and_admin_group(): void
    {
        // 1. Admin route group check (Tugas Pertemuan 2)
        $response = $this->get('/admin/info');
        $response->assertStatus(200);
        $response->assertSee('Informasi Sistem Perpustakaan');

        // 2. categories.show is excluded
        $response = $this->get('/categories/1');
        $this->assertTrue(in_array($response->status(), [404, 405]));

        // 3. loans.kembalikan route exists
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loans.kembalikan'));
    }

    public function test_pertemuan_tiga_form_requests_validation(): void
    {
        // Category validation Indonesian messages
        $response = $this->post(route('categories.store'), []);
        $response->assertSessionHasErrors(['nama_kategori']);

        // Book validation
        $response = $this->post(route('books.store'), []);
        $response->assertSessionHasErrors(['judul', 'penulis', 'penerbit', 'tahun_terbit', 'stok', 'category_id']);

        // Member validation
        $response = $this->post(route('members.store'), []);
        $response->assertSessionHasErrors(['nama', 'nim', 'email', 'nomor_telepon', 'alamat', 'status']);
    }

    public function test_pertemuan_empat_master_layout_and_partials(): void
    {
        $category = Category::create(['nama_kategori' => 'Teknologi']);

        // Check books.index has master layout, navbar, brand, and footer
        $response = $this->get(route('books.index'));
        $response->assertStatus(200);
        $response->assertSee('📚 Perpustakaan Digital Kampus');
        $response->assertSee('Sistem Perpustakaan Digital Kampus');
        $response->assertSee(route('books.index'));
        $response->assertSee(route('categories.index'));
        $response->assertSee(route('members.index'));
        $response->assertSee(route('loans.index'));

        // Check categories.index has master layout
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertSee('📚 Perpustakaan Digital Kampus');
        $response->assertSee('Teknologi');

        // Check books.create has master layout (Tugas Pertemuan 4)
        $response = $this->get(route('books.create'));
        $response->assertStatus(200);
        $response->assertSee('📚 Perpustakaan Digital Kampus');
        $response->assertSee('Tambah Buku');

        // Check categories.create has master layout (Tugas Pertemuan 4)
        $response = $this->get(route('categories.create'));
        $response->assertStatus(200);
        $response->assertSee('📚 Perpustakaan Digital Kampus');
        $response->assertSee('Tambah Kategori');

        // Check alert partial when flash success is present
        $response = $this->withSession(['success' => 'Operasi Berhasil'])
            ->get(route('books.index'));
        $response->assertSee('alert-success');
        $response->assertSee('Operasi Berhasil');
    }
}
