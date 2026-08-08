# Panduan Modul & Cara Penggunaan — Assetika

Dokumen ini menjelaskan fungsi seluruh modul di aplikasi **Assetika**, cara penggunaan harian, serta alur kerja (*workflow*) antar modul.

---

## 📌 1. Kelompok Menu: Dashboard & User Self-Service

### 1.1 Dashboard Utama (`/`)
* **Fungsi**: Ringkasan eksekutif seluruh aset IT. Menampilkan total aset, status deployment (*Ready*, *Deployed*, *Broken*, *In Service*), metrik finansial (Total Purchase Cost vs Current Book Value vs Total Depreciated), serta grafik alokasi aset per departemen & kategori.
* **Cara Pakai**: Dipakai Admin IT & Management untuk memantau kesehatan inventaris & keuangan aset secara *real-time*.

### 1.2 GA Dashboard (`/ga-dashboard`)
* **Fungsi**: Ringkasan aset fisik non-IT / General Affairs (Kendaraan kantor, Meja/Kursi, Bangunan, AC).
* **Cara Pakai**: Dipakai Admin GA untuk cek aset umum perusahaan.

### 1.3 My Assets (`/my-assets`)
* **Fungsi**: Portal karyawan untuk melihat daftar aset (laptop, monitor) & barang habis pakai yang sedang dipinjam/dialokasikan ke akun pengguna tersebut.
* **Cara Pakai**: Karyawan login → klik *My Assets* untuk mengecek aset yang menjadi tanggung jawabnya.

### 1.4 Request Item (`/requestable-assets`)
* **Fungsi**: Self-service karyawan untuk mengajukan permintaan aset/peripheral baru (misal: minta Mouse, Kabel HDMI, atau Laptop Kategori IT).
* **Cara Pakai**: Karyawan memilih kategori/barang → klik *Request* → pengajuan masuk ke status *Pending* untuk disetujui Admin.

---

## 🎟️ 2. Kelompok Menu: Support Ticketing

### 2.1 Tickets (`/ticket-dashboard`)
* **Fungsi**: Modul helpdesk / IT Support ticket untuk menangani keluhan/kendala teknis karyawan.
* **Cara Pakai**: Karyawan/Admin membuat tiket kendala → IT Support merespons, assign teknisi, & mengubah status tiket (*Open*, *In Progress*, *Resolved*, *Closed*).

### 2.2 Ticketing Data (`/admin/...`)
* **Fungsi**: Master konfigurasi helpdesk (*Priorities*, *Ticket Statuses*, *Ticket Types*, *SLA Statuses*, *Labels*, *Sources*).

---

## 💻 3. Kelompok Menu: IT Asset Management

### 3.1 IT Assets Inventory (`/assets`)
* **Fungsi**: Pusat inventaris hardware IT (Laptop, Server, PC, Printer, Switch).
* **Fitur Utama**:
  * **Auto Tag & Naming**: Otomatis menghasilkan `Asset Tag` (`DB/NB/250101/001`) dan `Device Name`.
  * **Dynamic Custom Fields**: Form menyesuaikan atribut khusus per kategori.
  * **Checkout / Checkin**: Alokasi aset ke User / Departemen / Lokasi.
  * **Maintenance**: Catat histori servis hardware.
  * **Filter Popover**: Filter aging (<1th s/d >4th), status, & *Fully Depreciated*.
  * **Batch Label QR Print**: Cetak label QR code massal.
* **Cara Pakai**:
  1. Klik *Add Asset* → pilih produk & isi spesifikasi.
  2. Klik ikon *Check-out* pada baris aset untuk menyerahkan ke karyawan.
  3. Klik *BAST* untuk cetak Berita Acara Serah Terima.

### 3.2 Components (`/components`)
* **Fungsi**: Pengelolaan sub-komponen internal hardware yang bisa dipasang/dilepas dari aset utama (RAM, SSD, GPU, Power Supply).
* **Cara Pakai**: Admin menginput stok komponen (contoh: RAM 16GB total 10 unit) → Klik *Checkout to Asset* untuk memasangkan RAM tersebut ke Laptop Aset #001 (Stok otomatis berkurang).

### 3.3 Licenses (`/licenses`)
* **Fungsi**: Manajemen aset digital / lisensi software (Microsoft 365, Adobe CC, Windows Pro, JetBrains).
* **Fitur Utama**: Tracking total *Seats*, lisensi terpakai vs sisa, & tanggal expired.
* **Cara Pakai**: Admin membuat data lisensi dengan N seats → Klik *Assign Seat* ke User atau Aset → Sistem melacak batas alokasi seat.

### 3.4 Kits (`/kits`)
* **Fungsi**: Template *package bundle* untuk onboarding karyawan baru.
* **Cara Pakai**: Admin membuat Kit "Developer Pack" (Laptop + Monitor + Lisensi JetBrains + Mouse) → Saat ada karyawan baru, klik *Checkout Kit* 1x klik → seluruh item otomatis ter-checkout ke user tersebut.

### 3.5 Consumables (`/consumables`)
* **Fungsi**: Barang habis pakai (Tinta Printer, Kabel LAN, Connector RJ45, Ballpoint).
* **Cara Pakai**: Input barang & batas *Low Threshold* → Klik *Checkout* jika diambil karyawan → Alert muncul otomatis saat stok menipis.

### 3.6 Depreciations (`/depreciations`)
* **Fungsi**: Profil penyusutan nilai finansial aset (Metode *Straight-Line*).
* **Cara Pakai**: Buat profil (contoh: "3 Year Straight Line" = 36 Bulan) → Pasang profil ini ke Aset → Sistem menghitung *Nilai Buku (Book Value)* otomatis berdasarkan `release_date`.

### 3.7 Audit / Opname (`/stock-counts`)
* **Fungsi**: Sesi opname fisik / pemeriksaan berkala stok aset di lapangan.
* **Cara Pakai**: Admin membuat sesi *Stock Count* (misal: Opname Lantai 2) → Tambahkan daftar aset → Petugas verifikasi status aset (*Found*, *Missing*, *Damaged*) → Klik *Mark Completed*.

### 3.8 Asset Requests (`/asset-requests`)
* **Fungsi**: Halaman persetujuan admin untuk meng-approve atau reject pengajuan aset dari karyawan.

---

## 🏢 4. Kelompok Menu: General Affairs (GA)

### 4.1 GA Assets (`/ga-assets`)
* **Fungsi**: Pengelolaan aset operasional kantor non-IT (Kendaraan Dinas, Meja, Kursi, AC, Bangunan).
* **Fitur**: Tracking plat nomor kendaraan, pajak STNK, BPKB, & log servis berkala kendaraan.

### 4.2 GA Categories (`/ga-categories`)
* **Fungsi**: Master kategori aset umum (Kendaraan, Furniture, Electronic, Building).

---

## 🗂️ 5. Kelompok Menu: Master Data

### 5.1 Organization (`/companies`, `/departments`, `/positions`, `/locations`)
* **Fungsi**: Hirarki struktur perusahaan, departemen, jabatan karyawan, dan lokasi cabang/gedung kantor.

### 5.2 Catalog (`/products`, `/master-categories`, `/categories`, `/custom-fields`, `/fieldsets`, `/eulas`, `/brands`, `/operating-systems`)
* **Fungsi**: Master katalog produk, atribut custom field dinamis, template EULA serah terima, merk/brand, dan sistem operasi.

### 5.3 Support Data (`/statuses`, `/vendors`, `/maintenance-types`)
* **Fungsi**: Master status aset (*Ready*, *Deployed*, *Broken*), daftar vendor/supplier, dan tipe maintenance (*Preventive*, *Corrective*).

---

## 📊 6. Kelompok Menu: Reports & Tools

### 6.1 Reports (`/reports`)
* **Fungsi**: Laporan terstruktur untuk *Activity Log*, *Asset Inventory Report*, & *Depreciation Financial Report*.

### 6.2 Label Generator (`/labels`)
* **Fungsi**: Tools cetak massal label barcode & QR Code aset dengan layout siap cetak (A4 PDF).

### 6.3 Import Data (`/import`)
* **Fungsi**: Import data massal dari file CSV/Excel untuk Assets, Consumables, & Users dengan fitur preview & validasi.

---

## ⚙️ 7. Kelompok Menu: System

### 7.1 Access Control (`/users`, `/roles`)
* **Fungsi**: Manajemen akun pengguna & pengaturan hak akses berbasis Role (*Super Admin*, *IT Admin*, *GA Admin*, *User*).

### 7.2 Backups (`/backups`)
* **Fungsi**: Backup database & file sistem secara berkala dengan fitur download & restore.
