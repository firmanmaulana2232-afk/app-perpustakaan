<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private array $members = [
        [
            'id' => 1,
            'nama' => 'Ahmad Fauzi',
            'nim' => '2232001',
            'email' => 'ahmad.fauzi@example.com',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Raya ITS No. 1, Surabaya',
            'status' => 'aktif',
        ],
        [
            'id' => 2,
            'nama' => 'Siti Nurhaliza',
            'nim' => '2232002',
            'email' => 'siti.nurhaliza@example.com',
            'nomor_telepon' => '082345678901',
            'alamat' => 'Jl. Gebang Wetan No. 15, Surabaya',
            'status' => 'aktif',
        ],
        [
            'id' => 3,
            'nama' => 'Budi Santoso',
            'nim' => '2232003',
            'email' => 'budi.santoso@example.com',
            'nomor_telepon' => '083456789012',
            'alamat' => 'Jl. Keputih Sukolilo No. 8, Surabaya',
            'status' => 'nonaktif',
        ],
    ];

    public function index()
    {
        $members = $this->members;

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(StoreMemberRequest $request)
    {
        $validated = $request->validated();

        return redirect()->route('members.index')
            ->with('success', "Anggota \"{$validated['nama']}\" berhasil ditambahkan (data dummy, belum tersimpan ke database).");
    }

    public function show(string $id)
    {
        return "MemberController@show, id: {$id}";
    }

    public function edit(string $id)
    {
        return "MemberController@edit, id: {$id}";
    }

    public function update(Request $request, string $id)
    {
        return "MemberController@update, id: {$id}";
    }

    public function destroy(string $id)
    {
        return redirect()->route('members.index')
            ->with('success', "Anggota dengan id {$id} berhasil dihapus (data dummy, belum tersimpan ke database).");
    }
}