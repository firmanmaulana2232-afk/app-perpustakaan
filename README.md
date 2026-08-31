# App Perpustakaan

Aplikasi Perpustakaan adalah sistem informasi yang digunakan untuk mengelola data buku, anggota, dan proses peminjaman di perpustakaan secara digital. Tujuannya adalah mempermudah admin dalam mencatat data, mengelola transaksi peminjaman, serta meningkatkan efisiensi pengelolaan perpustakaan.

## Cara Menjalankan Project Secara Lokal

1. Clone repository ini ke komputer Anda.
2. Masuk ke folder project dan jalankan:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   php artisan serve
   ```
3. Buka URL yang ditampilkan oleh `php artisan serve` di browser, biasanya `http://127.0.0.1:8000`.

## Penjelasan Singkat MVC

Model adalah bagian yang berhubungan dengan data dan logika bisnis, seperti mengambil data dari database dan mengatur aturan datanya. View adalah tampilan yang ditampilkan ke pengguna, biasanya berupa halaman HTML atau komponen UI. Controller berperan sebagai penghubung antara Model dan View, menerima request dari user, memprosesnya, lalu mengirim hasil ke tampilan yang tepat.

## Catatan

Project ini dibuat dengan framework Laravel dan mengikuti pola MVC untuk memisahkan logika, data, dan tampilan agar lebih terstruktur dan mudah dikembangkan.
