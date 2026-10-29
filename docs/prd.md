# Product Requirements Document

## Alexa — E-Commerce Website Tumbler Premium

## 1. Ringkasan Produk

Alexa adalah brand tumbler premium fiktif yang dikembangkan sebagai proyek mata kuliah e-commerce. Website ini dibangun sebagai platform belanja online yang menampilkan tiga koleksi tumbler stainless steel dengan desain editorial yang bersih dan minimalis.

Tujuan utama website adalah memberikan pengalaman berbelanja yang lengkap — mulai dari penelusuran katalog produk, pemilihan varian warna, penambahan ke keranjang, proses checkout, pembayaran, hingga pelacakan pengiriman.

Website Alexa bukan sekadar toko online sederhana. Tampilannya dirancang menyerupai brand lifestyle premium dengan perhatian khusus pada tipografi, tata letak, dan konsistensi visual.

---

## 2. Latar Belakang dan Permasalahan

### Konteks Proyek

Proyek ini dikembangkan untuk memenuhi kebutuhan tugas akhir mata kuliah e-commerce. Scope proyek mencakup pengembangan sistem belanja online fungsional yang mengimplementasikan alur transaksi dari awal hingga akhir.

### Permasalahan yang Ingin Diselesaikan

| No  | Permasalahan                                               | Solusi yang Diimplementasikan                               |
| --- | ---------------------------------------------------------- | ----------------------------------------------------------- |
| 1   | Produk tidak memiliki saluran penjualan digital            | Katalog produk online yang dapat diakses publik             |
| 2   | Pembeli tidak dapat melihat pilihan varian sebelum membeli | Halaman detail produk dengan pemilihan warna interaktif     |
| 3   | Proses pembelian tidak dapat dilakukan online              | Keranjang belanja, checkout, dan pembayaran terintegrasi    |
| 4   | Tidak ada sistem manajemen pesanan untuk pengelola toko    | Panel admin untuk manajemen produk, pesanan, dan pengiriman |
| 5   | Tidak ada informasi status pengiriman untuk pembeli        | Integrasi pelacakan pengiriman real-time via Biteship       |

---

## 3. Tujuan Produk

### Dari Sisi Pengguna (Pembeli)

- Dapat menelusuri dan memilih produk beserta variannya tanpa hambatan.
- Dapat menyelesaikan proses pembelian secara online dari keranjang hingga pembayaran.
- Dapat memantau status pesanan dan memperbarui informasi pengiriman secara mandiri.

### Dari Sisi Pengelola Toko (Admin)

- Dapat mengelola data produk dan varian (stok, harga, gambar) melalui panel admin.
- Dapat memproses pesanan masuk dan memperbarui status pengiriman.
- Dapat melihat ringkasan pesanan dan memantau aktivitas transaksi.

### Dari Sisi Proyek (Akademis)

- Mengimplementasikan sistem e-commerce end-to-end dengan arsitektur monolitik Laravel.
- Mengintegrasikan payment gateway sandbox (Midtrans) dan layanan pengiriman sandbox (Biteship).
- Memenuhi standar keamanan aplikasi web dasar (autentikasi, validasi, otorisasi).

---

## 4. Pengguna dan Peran

Website Alexa memiliki dua jenis pengguna:

### 4.1 Pengunjung Umum (Guest)

Pengguna yang belum login. Dapat mengakses:

- Halaman utama (homepage)
- Katalog produk
- Halaman detail produk

Tidak dapat mengakses keranjang, checkout, atau halaman pesanan.

### 4.2 Pelanggan (Customer)

Pengguna yang sudah terdaftar dan login. Dapat mengakses seluruh fitur Guest, ditambah:

- Keranjang belanja
- Proses checkout dan pengisian alamat pengiriman
- Pembayaran via Midtrans Snap
- Riwayat pesanan dan detail pesanan
- Pelacakan status pengiriman

### 4.3 Administrator (Admin)

Pengguna dengan flag `is_admin = true`. Dapat mengakses panel admin di `/admin`:

- Dashboard ringkasan
- Manajemen produk dan varian
- Manajemen pesanan (perubahan status)
- Manajemen pengiriman (input nomor resi dan kurir)

Admin tidak dapat mengakses halaman checkout atau fitur belanja pelanggan sebagai admin.

---

## 5. Fitur Utama

### 5.1 Katalog Produk

- Homepage menampilkan hero section, ringkasan tiga koleksi produk (Catalog Showcase), dan informasi brand.
- Halaman `/products` menampilkan seluruh produk dalam grid tiga kolom.
- Setiap kartu produk menampilkan gambar, nama, harga, dan badge stok.
- Produk hanya dapat tampil jika status produk dan variannya aktif.

### 5.2 Detail Produk

- Halaman `/products/{slug}` menampilkan informasi lengkap sebuah produk.
- Pengguna dapat memilih varian warna; gambar produk berganti secara dinamis sesuai pilihan.
- Menampilkan stok tersedia untuk varian yang dipilih.
- Tombol "Add to Cart" aktif jika varian tersedia; tidak aktif jika stok habis.
- Memerlukan login jika pengguna belum autentikasi saat menekan tombol keranjang.

### 5.3 Keranjang Belanja

- Setiap akun memiliki satu keranjang (`cart`) yang bersifat persisten.
- Pengguna dapat mengubah jumlah item atau menghapus item dari keranjang.
- Sistem memvalidasi stok saat penambahan dan pembaruan item di sisi server.
- Total harga dihitung di sisi server, bukan dari input klien.

### 5.4 Checkout

- Pengguna mengisi data alamat pengiriman (nama penerima, nomor telepon, jalan, kota, provinsi, kode pos).
- Pengguna memilih kurir dan layanan pengiriman yang tersedia melalui Biteship Sandbox.
- Ringkasan pesanan ditampilkan sebelum konfirmasi.
- Setelah konfirmasi, pesanan dibuat dengan status `pending` dan pembayaran dengan status `pending`.

### 5.5 Pembayaran

- Terintegrasi dengan Midtrans Snap (mode sandbox).
- Setelah pesanan dikonfirmasi, pengguna memulai pembayaran dari halaman detail pesanan.
- Midtrans menampilkan popup pembayaran dengan berbagai metode yang tersedia dalam sandbox.
- Status pembayaran diperbarui secara otomatis melalui webhook dari Midtrans.
- Status pesanan diperbarui menjadi `processing` setelah pembayaran dikonfirmasi.

### 5.6 Manajemen Pesanan (Pelanggan)

- Halaman `/orders` menampilkan riwayat pesanan milik akun yang login.
- Halaman detail pesanan menampilkan status pesanan, status pembayaran, item yang dipesan, dan informasi pengiriman.
- Pelanggan dapat memperbarui data pelacakan pengiriman melalui tombol refresh tracking.

### 5.7 Panel Admin

- Dashboard menampilkan ringkasan data pesanan terkini.
- **Manajemen Produk:** Admin dapat membuat, mengedit, dan menonaktifkan produk beserta variannya.
- **Manajemen Pesanan:** Admin dapat melihat semua pesanan, memperbarui status pesanan, dan menginput nomor resi.
- **Manajemen Pengiriman:** Admin menginput nama kurir dan nomor resi; sistem meneruskan pelacakan ke Biteship.

### 5.8 Pelacakan Pengiriman

- Terintegrasi dengan Biteship Sandbox untuk pelacakan real-time.
- Pelanggan dapat memperbarui informasi tracking dari halaman detail pesanan.
- Sistem menyimpan hasil tracking terakhir ke tabel `shipments`.

### 5.9 Autentikasi

- Registrasi akun baru dengan nama, email, dan password.
- Password divalidasi dengan aturan minimum 8 karakter, kombinasi huruf besar/kecil, dan angka.
- Login menggunakan email dan password.
- Session diregenerasi setelah login untuk mencegah session fixation.
- Logout menghapus dan menginvalidasi session.

---

## 6. Alur Bisnis Utama

### Alur Pembelian Pelanggan

```
Homepage / Katalog Produk
    -> Halaman Detail Produk
    -> Pilih Varian Warna
    -> Atur Jumlah
    -> Tambahkan ke Keranjang (login diperlukan)
    -> Halaman Keranjang
    -> Checkout — Isi Alamat Pengiriman & Pilih Kurir
    -> Konfirmasi Pesanan
    -> Halaman Detail Pesanan
    -> Mulai Pembayaran (Midtrans Snap)
    -> Status Pesanan Diperbarui via Webhook
    -> Admin Memproses & Input Nomor Resi
    -> Pelanggan Melacak Pengiriman
```

### Alur Manajemen Pesanan Admin

```
Admin Login
    -> Dashboard Admin
    -> Daftar Pesanan Masuk
    -> Detail Pesanan
    -> Perbarui Status Pesanan (processing -> packed -> shipped -> delivered)
    -> Input Kurir & Nomor Resi
    -> Sistem Simpan Data Pengiriman ke Biteship
```

---

## 7. Kebutuhan Non-Fungsional

### Keamanan

| Aspek            | Implementasi                                                                    |
| ---------------- | ------------------------------------------------------------------------------- |
| Autentikasi      | Middleware `auth` pada seluruh route yang memerlukan login                      |
| Otorisasi Admin  | Middleware `admin` pada seluruh route `/admin`                                  |
| Kepemilikan Data | Controller memvalidasi bahwa pesanan/keranjang milik pengguna yang login        |
| Password         | Di-hash menggunakan bcrypt via `Hash::make()`                                   |
| CSRF             | Token CSRF pada seluruh form POST/PATCH/DELETE                                  |
| SQL Injection    | Eloquent ORM dengan parameterized query                                         |
| XSS              | Blade `{{ }}` escaping pada seluruh output dinamis                              |
| Mass Assignment  | `$fillable` terdefinisi pada setiap model Eloquent                              |
| Harga & Stok     | Dihitung dan divalidasi di sisi server; tidak mempercayai nilai dari klien      |
| Secrets          | API key Midtrans dan Biteship disimpan di `.env`, tidak di-commit ke repository |

### Validasi Bisnis

- Stok tidak boleh bernilai negatif; sistem memblokir pembelian jika stok tidak mencukupi.
- Harga total pesanan dihitung dari database, bukan dari form submission.
- Status pembayaran hanya diperbarui melalui webhook Midtrans yang diverifikasi, bukan dari endpoint klien.

---

## 8. Arsitektur dan Teknologi

### Arsitektur Aplikasi

Alexa menggunakan arsitektur **monolitik Laravel** dengan alur request sebagai berikut:

```
Browser -> Route -> Middleware -> Controller -> Service (jika perlu) -> Eloquent -> MySQL -> Blade -> Browser
```

### Tabel Arsitektur

| Lapisan            | Teknologi                                        |
| ------------------ | ------------------------------------------------ |
| Backend Framework  | Laravel 13 (PHP 8.3)                             |
| Database           | MySQL                                            |
| Frontend           | Blade Templates, HTML, CSS, JavaScript (Vanilla) |
| Environment Lokal  | Laragon                                          |
| Payment Gateway    | Midtrans Snap (Sandbox)                          |
| Layanan Pengiriman | Biteship (Sandbox)                               |

### Dependensi Utama (composer.json)

| Package                    | Keterangan                         |
| -------------------------- | ---------------------------------- |
| `laravel/framework ^13.17` | Core framework Laravel             |
| `laravel/tinker ^3.0`      | REPL untuk debugging               |
| `phpunit/phpunit ^12.5`    | Framework pengujian unit dan fitur |

Integrasi Midtrans dan Biteship diimplementasikan secara langsung melalui service class internal (`MidtransService`, `BiteshipService`) menggunakan HTTP client bawaan Laravel, tanpa dependensi package pihak ketiga tambahan.

---

## 9. Model Data

### Tabel Utama

| Tabel              | Deskripsi                                             |
| ------------------ | ----------------------------------------------------- |
| `users`            | Data akun pengguna dan admin                          |
| `collections`      | Koleksi produk (Core, Active, Trek)                   |
| `products`         | Data produk utama                                     |
| `product_variants` | Varian warna setiap produk (SKU, harga, stok, gambar) |
| `carts`            | Keranjang belanja per pengguna                        |
| `cart_items`       | Item dalam keranjang (varian + jumlah)                |
| `addresses`        | Alamat pengiriman pengguna                            |
| `orders`           | Data pesanan                                          |
| `order_items`      | Item dalam pesanan (snapshot harga saat checkout)     |
| `payments`         | Data pembayaran dan status dari Midtrans              |
| `shipments`        | Data pengiriman dan hasil tracking dari Biteship      |

### Relasi Antar Model

```
User --< Order --< OrderItem >-- ProductVariant >-- Product >-- Collection
User --  Cart  --< CartItem  >-- ProductVariant
Order -- Payment
Order -- Shipment
User --< Address
```

### Varian Produk

Terdapat **3 produk** dengan total **12 varian** (4 varian per produk):

| Produk               | Varian                          | SKU Prefix  | Harga       |
| -------------------- | ------------------------------- | ----------- | ----------- |
| Alexa Core (500ml)   | Midnight, Sage, Ivory, Lavender | `ALX-CORE-` | Rp899.000   |
| Alexa Active (600ml) | Snow, Eclipse, Sky, Blush       | `ALX-ACT-`  | Rp1.099.000 |
| Alexa Trek (750ml)   | Midnight, Steel, Sand, Onyx     | `ALX-TRK-`  | Rp1.299.000 |

Stok awal: **20 unit per varian**.

---

## 10. Status dan Alur Status

### Status Pesanan

| Status       | Keterangan                                             |
| ------------ | ------------------------------------------------------ |
| `pending`    | Pesanan baru dibuat, menunggu pembayaran               |
| `processing` | Pembayaran dikonfirmasi, pesanan sedang diproses admin |
| `packed`     | Pesanan sudah dikemas oleh admin                       |
| `shipped`    | Pesanan sudah dikirim, nomor resi tersedia             |
| `delivered`  | Pesanan diterima oleh pelanggan                        |
| `cancelled`  | Pesanan dibatalkan                                     |

### Status Pembayaran

| Status      | Keterangan                        |
| ----------- | --------------------------------- |
| `pending`   | Menunggu konfirmasi dari Midtrans |
| `paid`      | Pembayaran berhasil dikonfirmasi  |
| `failed`    | Pembayaran gagal                  |
| `expired`   | Waktu pembayaran habis            |
| `cancelled` | Pembayaran dibatalkan             |

### Status Pengiriman

| Status       | Keterangan                      |
| ------------ | ------------------------------- |
| `pending`    | Menunggu nomor resi dari admin  |
| `shipped`    | Paket telah diserahkan ke kurir |
| `in_transit` | Paket dalam perjalanan          |
| `delivered`  | Paket telah diterima            |

Status pesanan, pembayaran, dan pengiriman dikelola secara **terpisah** dan tidak saling menimpa.

---

## 11. Struktur Kode

### Direktori Utama

```
alexa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # DashboardController, OrderController,
│   │   │   │                   # ProductController, ProductVariantController,
│   │   │   │                   # ShipmentController
│   │   │   ├── Customer/       # HomeController, ProductController,
│   │   │   │                   # CartController, CheckoutController,
│   │   │   │                   # OrderController, PaymentController,
│   │   │   │                   # TrackingController
│   │   │   └── Webhook/        # MidtransWebhookController
│   │   └── Middleware/         # AdminMiddleware
│   ├── Models/                 # User, Collection, Product, ProductVariant,
│   │                           # Cart, CartItem, Address, Order, OrderItem,
│   │                           # Payment, Shipment
│   └── Services/               # MidtransService, BiteshipService
├── database/
│   ├── migrations/             # 16 file migrasi
│   └── seeders/                # DatabaseSeeder
├── resources/
│   ├── views/
│   │   ├── layouts/            # app.blade.php, admin.blade.php
│   │   ├── auth/               # login.blade.php, register.blade.php
│   │   ├── admin/              # dashboard, orders, products
│   │   ├── cart/               # index.blade.php
│   │   ├── checkout/           # index.blade.php
│   │   ├── orders/             # index.blade.php, show.blade.php
│   │   └── products/           # index.blade.php, show.blade.php
│   └── css/ js/                # Aset frontend
├── routes/
│   └── web.php                 # Definisi seluruh route aplikasi
├── public/
│   └── images/                 # Aset gambar produk dan homepage
└── tests/
    └── Feature/                # 95 test case, 543 assertions
```

### Route Utama

| Method    | URI                               | Middleware      | Fungsi                       |
| --------- | --------------------------------- | --------------- | ---------------------------- |
| GET       | `/`                               | —               | Homepage                     |
| GET       | `/products`                       | —               | Katalog semua produk         |
| GET       | `/products/{slug}`                | —               | Detail produk                |
| GET       | `/cart`                           | auth            | Keranjang belanja            |
| POST      | `/cart/items`                     | auth            | Tambah item ke keranjang     |
| GET       | `/checkout`                       | auth            | Halaman checkout             |
| POST      | `/checkout`                       | auth            | Buat pesanan                 |
| GET       | `/orders`                         | auth            | Riwayat pesanan              |
| GET       | `/orders/{order}`                 | auth            | Detail pesanan               |
| POST      | `/orders/{order}/payment`         | auth            | Inisiasi pembayaran Midtrans |
| POST      | `/payments/midtrans/notification` | — (CSRF exempt) | Webhook Midtrans             |
| GET       | `/admin`                          | auth, admin     | Dashboard admin              |
| GET/POST  | `/admin/products`                 | auth, admin     | Manajemen produk             |
| GET/PATCH | `/admin/orders/{order}`           | auth, admin     | Manajemen pesanan            |

---

## 12. Batasan dan Ruang Lingkup

### Di Dalam Ruang Lingkup Proyek

- Seluruh fitur yang tercantum pada Bagian 5 (Fitur Utama).
- Integrasi Midtrans Snap dalam mode **sandbox** (bukan produksi).
- Integrasi Biteship dalam mode **sandbox** (bukan produksi).
- Panel admin berbasis web untuk pengelolaan produk dan pesanan.
- Pengujian fungsional dengan PHPUnit (95 test case).

### Di Luar Ruang Lingkup Proyek

| Fitur                              | Alasan                                       |
| ---------------------------------- | -------------------------------------------- |
| Notifikasi email                   | Tidak diimplementasikan pada versi ini       |
| Fitur pencarian dan filter         | Tidak diimplementasikan pada versi ini       |
| Ulasan dan rating produk           | Tidak diimplementasikan pada versi ini       |
| Voucher / kode diskon              | Tidak diimplementasikan pada versi ini       |
| Integrasi payment gateway produksi | Menggunakan sandbox untuk keperluan akademis |
| Integrasi kurir produksi           | Menggunakan sandbox Biteship                 |
| Aplikasi mobile                    | Website-only, tidak ada aplikasi native      |
| Multi-bahasa                       | Hanya Bahasa Indonesia pada antarmuka utama  |
| Upload gambar produk oleh admin    | Gambar dikelola langsung di server           |

---

## 13. Pengujian

### Suite Pengujian

Proyek ini menggunakan **PHPUnit** sebagai framework pengujian dengan hasil terverifikasi:

- **Total Test Case:** 95
- **Total Assertion:** 543
- **Status:** Seluruhnya lulus (passing)

### Cakupan Pengujian

Pengujian dilakukan menggunakan **Feature Tests** yang mensimulasikan HTTP request sesungguhnya terhadap aplikasi.

| Area Pengujian    | Contoh Skenario                                                    |
| ----------------- | ------------------------------------------------------------------ |
| Autentikasi       | Registrasi berhasil, login berhasil, proteksi route tanpa login    |
| Katalog Produk    | Homepage dapat diakses, daftar produk tampil, detail produk tampil |
| Keranjang Belanja | Tambah item, ubah jumlah, hapus item, validasi stok                |
| Checkout          | Buat pesanan, validasi alamat, kalkulasi total di server           |
| Pembayaran        | Inisiasi Midtrans, pemrosesan webhook, pembaruan status            |
| Manajemen Pesanan | Admin update status, input resi pengiriman                         |
| Panel Admin       | Proteksi akses admin, manajemen produk dan varian                  |
| Keamanan          | CSRF protection, kepemilikan data, otorisasi admin                 |

### Catatan

Seluruh pengujian berjalan pada lingkungan **test database** terpisah menggunakan `RefreshDatabase` trait. Tidak ada hasil pengujian yang dikarang — angka dan status di atas mencerminkan kondisi aktual saat dokumen ini dibuat.

---

## 14. Ringkasan untuk Presentasi

### Tentang Proyek Ini

Alexa adalah website e-commerce yang dibangun untuk memenuhi tugas akhir mata kuliah e-commerce. Proyek ini mensimulasikan toko online brand tumbler premium secara end-to-end — dari katalog produk hingga pembayaran dan pengiriman.

### Apa yang Sudah Dibangun

| Komponen       | Detail                                                               |
| -------------- | -------------------------------------------------------------------- |
| **Backend**    | Laravel 13, PHP 8.3, MySQL — arsitektur monolitik                    |
| **Frontend**   | Blade Templates, CSS custom, JavaScript vanilla                      |
| **Produk**     | 3 koleksi, 12 varian warna, 1 admin panel                            |
| **Pembayaran** | Midtrans Snap Sandbox — terintegrasi penuh dengan webhook            |
| **Pengiriman** | Biteship Sandbox — pemilihan kurir dan pelacakan resi                |
| **Keamanan**   | Autentikasi, otorisasi, CSRF, validasi server-side, hashing password |
| **Pengujian**  | 95 test case, 543 assertions — seluruhnya lulus                      |

### Alur yang Dapat Didemonstrasikan

1. Pengunjung menelusuri katalog dan detail produk tanpa login.
2. Pengunjung mendaftar akun baru.
3. Pelanggan memilih varian, menambahkan ke keranjang, mengisi alamat, memilih kurir, dan melakukan checkout.
4. Pelanggan menyelesaikan pembayaran melalui Midtrans Snap (sandbox).
5. Admin memproses pesanan, mengubah status, dan menginput nomor resi.
6. Pelanggan memperbarui dan melihat status pengiriman.

### Nilai Akademis yang Ditunjukkan

- Implementasi **MVC** dengan service layer untuk logika bisnis eksternal.
- Penerapan **keamanan web** secara menyeluruh (OWASP-aligned).
- Integrasi **API pihak ketiga** (payment gateway dan logistik).
- **Pengujian terautomasi** dengan coverage yang memadai.
- Desain UI/UX yang memperhatikan **konsistensi visual** dan pengalaman pengguna.
