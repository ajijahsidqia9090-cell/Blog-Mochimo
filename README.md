# 🛒 Mochimo E-Commerce (PHP & MySQL)
## 📖 Description

Website ini merupakan E-Commerce berbasis PHP dan MySQL yang digunakan untuk melakukan transaksi jual beli produk secara online.

Sistem ini memiliki 2 level pengguna utama yaitu:

Admin
Customer

Setiap pengguna memiliki fitur dan hak akses masing-masing untuk mengelola dan menggunakan sistem secara optimal.

## 🎥 Demo Video Mochimo

(Isi dengan link demo kamu jika ada)
Contoh:
https://youtu.be/nk_DcL6ffy4?si=rdcMEXlKytj9RVAh

## 🚀 Features
🔐 Login & Register Customer
🛍️ Katalog Produk & Kategori
🔎 Pencarian Produk
🛒 Keranjang Belanja (Cart)
💳 Checkout (COD, Transfer, QRIS)
📦 Detail Pesanan
🚚 Tracking Pesanan
🔔 Notifikasi Pesanan & Voucher
🧾 Riwayat Transaksi
📊 Dashboard Admin
📦 CRUD Produk & Kategori
💸 Manajemen Voucher

## 👥 User Roles
### 👑 Admin
Mengelola data produk (CRUD)
Mengelola kategori produk
Mengelola voucher
Melihat semua pesanan
Mengubah status pesanan:
Diproses
Dikirim
Selesai
Melihat total pendapatan
### 🛍️ Customer
Register & Login akun
Melihat produk
Melihat detail produk (gambar, deskripsi, harga, stok, ukuran)
Menambahkan produk ke keranjang
Melakukan checkout (QRIS / Transfer / COD)
Melihat status pesanan
Tracking pengiriman
Melihat notifikasi
Menggunakan voucher

## 📂 Project Structure
mochimo/
│
├── index.php → Halaman utama
├── auth/
│   ├── login.php → Login user
│   ├── register.php → Register user
│   └── logout.php → Logout
│
├── customer/
│   ├── dashboard.php → Halaman utama customer
│   ├── cart.php → Keranjang
│   ├── pesanan.php → Daftar pesanan
│   ├── detail_pesanan.php → Detail pesanan
│   ├── detail_produk.php → Detail produk
│   ├── tracking_pesanan.php → Tracking
│   ├── riwayat.php → Riwayat transaksi
│   ├── notifikasi.php → Notifikasi
│   └── bayar_qr.php → Pembayaran QRIS
│
├── admin/
│   ├── dashboard.php → Dashboard admin
│   ├── produk.php → Kelola produk
│   ├── kategori.php → Kelola kategori
│   ├── voucher.php → Kelola voucher
│   └── pesanan.php → Kelola pesanan
│
├── config/
│   └── koneksi.php → Koneksi database
│
├── template/
│   ├── header.php
│   ├── footer.php
│   └── navbar_customer.php
│
├── assets/
│   ├── img/
│   └── profile/
│
└── admin/upload/ → Upload gambar produk

## ⚙️ Installation
### Clone repository:
https://github.com/ajijahsidqia9090-cell/Web-Mochimo.git
### Pindahkan ke folder:
C:/laragon/www/
### Buat database:
db_mochimo
Import file .sql ke phpMyAdmin
### Jalankan project:
http://localhost/mochimo

## 👨‍💻 Author
Kelompok 1
Nama: Dimas Faturohman(2488010003), Khajizatu Sidqiyah(2488010044), Dewi Irimah(2488010049)
Kelas: Informatika - B

## 📌 Notes
Project ini dibuat untuk keperluan pembelajaran dan tugas akademik
Sistem masih dapat dikembangkan lebih lanjut seperti:
Payment gateway (Midtrans, dll)
Rating & review produk
Multi variasi produk (warna, ukuran)
Notifikasi real-time
