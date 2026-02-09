# Moka POS Coffeeshop

Web POS modern untuk coffeeshop berbasis `Laravel 11 + Blade + Alpine.js` (tanpa SPA framework terpisah), dibuat agar cepat dipakai kasir dan nyaman dipantau admin.

## Kenapa Project Ini Menarik
- UI modern ala Moka POS: clean, ringan, responsif untuk desktop/tablet/mobile.
- Kasir-friendly: alur transaksi cepat, keyboard shortcut, dan checkout ringkas.
- Siap operasional: role admin & kasir, open bill, pembayaran multi metode, cetak struk thermal.
- Insight bisnis langsung jalan: omzet, modal, laba kotor, top menu, dan export CSV.
- Efficient hosting: upload gambar produk otomatis dioptimasi (kompres/convert) agar bandwidth hemat dan preview tetap cepat.

## Fitur Unggulan
- `Auth + Role`
  - Admin: kelola master data + laporan + void transaksi.
  - Kasir: POS, riwayat transaksi milik sendiri, lanjutkan open bill milik sendiri.
- `Master Data`
  - Kategori, produk, varian, addon, metode pembayaran.
  - Produk mendukung `harga jual` dan `harga modal`.
  - Upload gambar produk (`jpg/jpeg/png/webp/svg`) dengan optimasi otomatis.
- `POS Kasir`
  - Search produk cepat (nama/SKU), kategori, kartu menu, cart interaktif.
  - Add varian, addon, catatan item, qty stepper.
  - Pajak default `10%` dan bisa diedit dari popup pembayaran.
  - Open Bill:
    - simpan/update order belum dibayar
    - pakai ID internal (`Open Bill #ID`)
    - lanjutkan ke pembayaran kapan saja
- `Checkout & Receipt`
  - Metode: Cash, QRIS, Debit, E-Wallet.
  - Validasi backend + kalkulasi ulang total (tidak percaya total dari client).
  - Invoice harian unik: `CS-YYYYMMDD-XXXX`.
  - Receipt thermal 80mm + print dari browser.
- `Laporan Admin`
  - Total omzet, jumlah transaksi, laba kotor.
  - Breakdown metode bayar.
  - Top menu (dengan kolom modal).
  - Daftar transaksi + modal/laba per transaksi.
  - Export CSV.

## Tech Stack
- `PHP 8.2+`
- `Laravel 11`
- `Laravel Breeze`
- `Blade + Alpine.js`
- `TailwindCSS`
- `MySQL`

## Akun Default Seeder
- Admin: `admin@coffeeshop.test` / `password`
- Kasir: `kasir@coffeeshop.test` / `password`

## Cara Menjalankan Project
1. Install dependency PHP:
```bash
composer install
```
2. Install dependency frontend:
```bash
npm install
```
3. Buat file env:
```bash
cp .env.example .env
```
4. Generate app key:
```bash
php artisan key:generate
```
5. Atur koneksi database MySQL di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```
6. Migrate + seed data demo:
```bash
php artisan migrate --seed
```
7. Link storage publik:
```bash
php artisan storage:link
```
8. Jalankan app:
```bash
php artisan serve
```
9. Jalankan asset dev:
```bash
npm run dev
```

Untuk mode production build:
```bash
npm run build
```

## Testing
Jalankan semua feature test:
```bash
php artisan test --testsuite=Feature
```

## Catatan Print Thermal
- Gunakan halaman receipt dan print via browser.
- Pastikan printer thermal (USB/Bluetooth/LAN) sudah terpasang di device kasir.
- Tidak menggunakan library print tambahan.

## Keamanan Data Dasar
- `.env`, `vendor`, `node_modules`, dan build cache sudah di-ignore pada `.gitignore`.
- Jangan commit file kredensial.

---
git remote add origin https://github.com/yassnath/moka-pos-coffeeshop.git
git push -u origin main
```

Selesai. Setelah itu cukup `git add .`, `git commit -m "..."`, `git push` untuk update berikutnya.
