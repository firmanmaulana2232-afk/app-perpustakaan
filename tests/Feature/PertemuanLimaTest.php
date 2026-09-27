<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PertemuanLimaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_field_and_fillable(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);

        $petugas = User::create([
            'name' => 'Petugas Test',
            'email' => 'petugas@example.com',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $petugas->id,
            'role' => 'petugas',
        ]);
    }

    public function test_categories_crud(): void
    {
        // 1. Create
        $response = $this->post(route('categories.store'), [
            'nama_kategori' => 'Teknologi',
            'deskripsi' => 'Buku seputar IT',
        ]);
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['nama_kategori' => 'Teknologi']);

        $category = Category::first();

        // 2. Index
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Teknologi');

        // 3. Edit view
        $response = $this->get(route('categories.edit', $category->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Kategori');

        // 4. Update
        $response = $this->put(route('categories.update', $category->id), [
            'nama_kategori' => 'Sains & Teknologi',
            'deskripsi' => 'Buku sains dan IT',
        ]);
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['nama_kategori' => 'Sains & Teknologi']);

        // 5. Delete
        $response = $this->delete(route('categories.destroy', $category->id));
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_books_crud(): void
    {
        $category = Category::create([
            'nama_kategori' => 'Fiksi',
            'deskripsi' => 'Novel dan sastra',
        ]);

        // 1. Create view
        $response = $this->get(route('books.create'));
        $response->assertStatus(200);
        $response->assertSee('Fiksi');

        // 2. Store with invalid category fails
        $response = $this->post(route('books.store'), [
            'judul' => 'Buku Palsu',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2020,
            'stok' => 5,
            'category_id' => 9999,
        ]);
        $response->assertSessionHasErrors('category_id');

        // 3. Store valid book
        $response = $this->post(route('books.store'), [
            'judul' => 'Laskar Pelangi',
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2005,
            'isbn' => '9789793062792',
            'stok' => 10,
            'category_id' => $category->id,
        ]);
        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('books', ['judul' => 'Laskar Pelangi']);

        $book = Book::first();

        // 4. Index view
        $response = $this->get(route('books.index'));
        $response->assertStatus(200);
        $response->assertSee('Laskar Pelangi');
        $response->assertSee((string) $category->id);

        // 5. Show view
        $response = $this->get(route('books.show', $book->id));
        $response->assertStatus(200);
        $response->assertSee('Laskar Pelangi');
        $response->assertSee('ID Kategori');

        // 6. Edit view
        $response = $this->get(route('books.edit', $book->id));
        $response->assertStatus(200);
        $response->assertSee('Laskar Pelangi');

        // 7. Update
        $response = $this->put(route('books.update', $book->id), [
            'judul' => 'Laskar Pelangi Edisi Baru',
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2005,
            'isbn' => '9789793062792',
            'stok' => 12,
            'category_id' => $category->id,
        ]);
        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('books', ['judul' => 'Laskar Pelangi Edisi Baru', 'stok' => 12]);

        // 8. Delete
        $response = $this->delete(route('books.destroy', $book->id));
        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_members_crud_and_search(): void
    {
        // 1. Create view
        $response = $this->get(route('members.create'));
        $response->assertStatus(200);

        // 2. Store member
        $response = $this->post(route('members.store'), [
            'nama' => 'Ahmad Fauzi',
            'nim' => '2232001',
            'email' => 'ahmad@example.com',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Surabaya',
            'status' => 'aktif',
        ]);
        $response->assertRedirect(route('members.index'));
        $this->assertDatabaseHas('members', ['nim' => '2232001']);

        // Unique validation on store
        $response = $this->post(route('members.store'), [
            'nama' => 'Fauzi Duplicate',
            'nim' => '2232001',
            'email' => 'ahmad@example.com',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Surabaya',
            'status' => 'aktif',
        ]);
        $response->assertSessionHasErrors(['nim', 'email']);

        // Another member
        Member::create([
            'nama' => 'Siti Nurhaliza',
            'nim' => '2232002',
            'email' => 'siti@example.com',
            'nomor_telepon' => '082345678901',
            'alamat' => 'Surabaya',
            'status' => 'aktif',
        ]);

        $member1 = Member::first();

        // 3. Search feature
        $response = $this->get(route('members.index', ['search' => 'Ahmad']));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Siti Nurhaliza');

        // 4. Show view
        $response = $this->get(route('members.show', $member1->id));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');

        // 5. Edit view
        $response = $this->get(route('members.edit', $member1->id));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');

        // 6. Update
        $response = $this->put(route('members.update', $member1->id), [
            'nama' => 'Ahmad Fauzi Updated',
            'nim' => '2232001', // same nim should pass
            'email' => 'ahmad@example.com', // same email should pass
            'nomor_telepon' => '081234567890',
            'alamat' => 'Surabaya Barat',
            'status' => 'aktif',
        ]);
        $response->assertRedirect(route('members.index'));
        $this->assertDatabaseHas('members', ['nama' => 'Ahmad Fauzi Updated', 'alamat' => 'Surabaya Barat']);

        // 7. Delete
        $response = $this->delete(route('members.destroy', $member1->id));
        $response->assertRedirect(route('members.index'));
        $this->assertDatabaseMissing('members', ['id' => $member1->id]);
    }

    public function test_loan_and_loan_item_models(): void
    {
        $category = Category::create(['nama_kategori' => 'Umum']);
        $book = Book::create([
            'judul' => 'Buku Uji',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'stok' => 5,
            'category_id' => $category->id,
        ]);
        $member = Member::create([
            'nama' => 'Member Pinjam',
            'nim' => '2232099',
            'email' => 'pinjam@example.com',
            'nomor_telepon' => '089999999',
            'alamat' => 'Kampus ITS',
            'status' => 'aktif',
        ]);
        $user = User::create([
            'name' => 'Petugas Pinjam',
            'email' => 'petugas_pinjam@example.com',
            'password' => 'secret',
            'role' => 'petugas',
        ]);

        $loan = Loan::create([
            'member_id' => $member->id,
            'user_id' => $user->id,
            'tanggal_pinjam' => '2026-09-27',
            'tanggal_kembali' => '2026-10-04',
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('loans', ['id' => $loan->id, 'status' => 'dipinjam']);

        $loanItem = LoanItem::create([
            'loan_id' => $loan->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('loan_items', ['id' => $loanItem->id, 'loan_id' => $loan->id, 'book_id' => $book->id]);
    }
}
