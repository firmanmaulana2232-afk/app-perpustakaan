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

class PertemuanTujuhTest extends TestCase
{
    use RefreshDatabase;

    private function createSeedData(): array
    {
        $category = Category::create([
            'nama_kategori' => 'Fiksi Ilmiah',
            'deskripsi' => 'Buku fiksi ilmiah',
        ]);

        $book1 = Book::create([
            'judul' => 'Dune',
            'penulis' => 'Frank Herbert',
            'penerbit' => 'Chilton Books',
            'tahun_terbit' => 1965,
            'stok' => 5,
            'category_id' => $category->id,
        ]);

        $book2 = Book::create([
            'judul' => 'Foundation',
            'penulis' => 'Isaac Asimov',
            'penerbit' => 'Gnome Press',
            'tahun_terbit' => 1951,
            'stok' => 3,
            'category_id' => $category->id,
        ]);

        $member = Member::create([
            'nama' => 'Budi Santoso',
            'nim' => '2232009',
            'email' => 'budi@example.com',
            'nomor_telepon' => '081234567899',
            'alamat' => 'Surabaya',
            'status' => 'aktif',
        ]);

        $user = User::create([
            'name' => 'Petugas Perpustakaan',
            'email' => 'petugas@perpus.id',
            'password' => 'secret123',
            'role' => 'petugas',
        ]);

        return compact('category', 'book1', 'book2', 'member', 'user');
    }

    public function test_all_model_relationships(): void
    {
        $data = $this->createSeedData();

        $loan = Loan::create([
            'member_id' => $data['member']->id,
            'user_id' => $data['user']->id,
            'tanggal_pinjam' => '2026-09-30',
            'tanggal_kembali' => '2026-10-07',
            'status' => 'dipinjam',
        ]);

        $loanItem1 = $loan->loanItems()->create(['book_id' => $data['book1']->id]);
        $loanItem2 = $loan->loanItems()->create(['book_id' => $data['book2']->id]);

        // Category -> books
        $this->assertTrue($data['category']->books->contains($data['book1']));
        $this->assertTrue($data['category']->books->contains($data['book2']));

        // Book -> category
        $this->assertEquals($data['category']->id, $data['book1']->category->id);

        // Book -> loanItems
        $this->assertTrue($data['book1']->loanItems->contains($loanItem1));

        // Member -> loans
        $this->assertTrue($data['member']->loans->contains($loan));

        // User -> loans
        $this->assertTrue($data['user']->loans->contains($loan));

        // Loan -> member & user & loanItems
        $this->assertEquals($data['member']->id, $loan->member->id);
        $this->assertEquals($data['user']->id, $loan->user->id);
        $this->assertCount(2, $loan->loanItems);

        // LoanItem -> loan & book
        $this->assertEquals($loan->id, $loanItem1->loan->id);
        $this->assertEquals($data['book1']->id, $loanItem1->book->id);
    }

    public function test_books_views_show_category_name(): void
    {
        $data = $this->createSeedData();

        // Index view displays category name instead of raw ID
        $response = $this->get(route('books.index'));
        $response->assertStatus(200);
        $response->assertSee('Kategori');
        $response->assertSee('Fiksi Ilmiah');

        // Show view displays category name
        $response = $this->get(route('books.show', $data['book1']->id));
        $response->assertStatus(200);
        $response->assertSee('Kategori');
        $response->assertSee('Fiksi Ilmiah');
    }

    public function test_member_detail_shows_loan_history(): void
    {
        $data = $this->createSeedData();

        // When member has no loans
        $response = $this->get(route('members.show', $data['member']->id));
        $response->assertStatus(200);
        $response->assertSee('Anggota ini belum pernah meminjam buku.');

        // Create a loan for member
        $loan = Loan::create([
            'member_id' => $data['member']->id,
            'user_id' => $data['user']->id,
            'tanggal_pinjam' => '2026-09-30',
            'tanggal_kembali' => '2026-10-07',
            'status' => 'dipinjam',
        ]);
        $loan->loanItems()->create(['book_id' => $data['book1']->id]);

        $response = $this->get(route('members.show', $data['member']->id));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Peminjaman');
        $response->assertSee('Dune');
        $response->assertSee('Petugas Perpustakaan');
    }

    public function test_loans_crud_lifecycle(): void
    {
        $data = $this->createSeedData();

        // 1. Create page
        $response = $this->get(route('loans.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Peminjaman');
        $response->assertSee($data['member']->nama);
        $response->assertSee($data['user']->name);
        $response->assertSee($data['book1']->judul);

        // 2. Store loan
        $response = $this->post(route('loans.store'), [
            'member_id' => $data['member']->id,
            'user_id' => $data['user']->id,
            'tanggal_pinjam' => '2026-09-30',
            'tanggal_kembali' => '2026-10-07',
            'book_ids' => [$data['book1']->id, $data['book2']->id],
        ]);
        $response->assertRedirect(route('loans.index'));

        $this->assertDatabaseHas('loans', [
            'member_id' => $data['member']->id,
            'user_id' => $data['user']->id,
            'status' => 'dipinjam',
        ]);

        $loan = Loan::first();
        $this->assertDatabaseHas('loan_items', ['loan_id' => $loan->id, 'book_id' => $data['book1']->id]);
        $this->assertDatabaseHas('loan_items', ['loan_id' => $loan->id, 'book_id' => $data['book2']->id]);

        // 3. Index page
        $response = $this->get(route('loans.index'));
        $response->assertStatus(200);
        $response->assertSee($data['member']->nama);
        $response->assertSee($data['user']->name);
        $response->assertSee($data['book1']->judul);
        $response->assertSee($data['book2']->judul);
        $response->assertSee('badge-dipinjam');

        // 4. Show page
        $response = $this->get(route('loans.show', $loan->id));
        $response->assertStatus(200);
        $response->assertSee('Detail Peminjaman');
        $response->assertSee($data['member']->nama);
        $response->assertSee($data['book1']->judul);
        $response->assertSee('badge-dipinjam');

        // 5. Edit page
        $response = $this->get(route('loans.edit', $loan->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Peminjaman');
        $response->assertSee($data['member']->nama);

        // 6. Update
        $response = $this->put(route('loans.update', $loan->id), [
            'tanggal_pinjam' => '2026-09-30',
            'tanggal_kembali' => '2026-10-10',
            'status' => 'terlambat',
        ]);
        $response->assertRedirect(route('loans.index'));
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'terlambat',
            'tanggal_kembali' => '2026-10-10',
        ]);

        // 7. Destroy
        $response = $this->delete(route('loans.destroy', $loan->id));
        $response->assertRedirect(route('loans.index'));
        $this->assertDatabaseMissing('loans', ['id' => $loan->id]);
        $this->assertDatabaseMissing('loan_items', ['loan_id' => $loan->id]);
    }

    public function test_tugas_kembalikan_fitur(): void
    {
        $data = $this->createSeedData();

        $loan = Loan::create([
            'member_id' => $data['member']->id,
            'user_id' => $data['user']->id,
            'tanggal_pinjam' => '2026-09-30',
            'tanggal_kembali' => '2026-10-07',
            'status' => 'dipinjam',
        ]);
        $loan->loanItems()->create(['book_id' => $data['book1']->id]);

        // Button Kembalikan appears on index when status is dipinjam
        $response = $this->get(route('loans.index'));
        $response->assertStatus(200);
        $response->assertSee('Kembalikan');

        // Execute kembalikan
        $response = $this->put(route('loans.kembalikan', $loan->id));
        $response->assertRedirect(route('loans.index'));

        $loan->refresh();
        $this->assertEquals('dikembalikan', $loan->status);
        $this->assertEquals(now()->toDateString(), $loan->tanggal_dikembalikan);

        // On index, button Kembalikan no longer appears, badge is dikembalikan
        $response = $this->get(route('loans.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Kembalikan');
        $response->assertSee('badge-dikembalikan');
    }
}
