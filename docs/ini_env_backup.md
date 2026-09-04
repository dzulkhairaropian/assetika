# Panduan Konfigurasi Backup & Schedule Production

Dokumen ini berisi panduan variabel `.env`, cakupan backup App + DB, dan langkah mengaktifkan jadwal otomatis (*Backup Schedule*).

---

## 1. Apakah Backup "App + DB" (Full Backup) Mencakup Semuanya?

**YA, 100% TERBACKUP LENGKAP.**
Ketika memilih **Backup Application & Database** (Full Backup):
1. **Database Dump**: Seluruh struktur tabel dan seluruh isi baris data MySQL.
2. **File Upload & Dokumen**: Seluruh file lampiran BAST PDF, foto aset, avatar user, dan attachment tiket di `storage/app/public`.
3. **Source Code & Konfigurasi**: Seluruh source code proyek dan file `.env`.
4. **Yang Dikecualikan (Excluded secara aman)**: Folder `vendor`, `node_modules`, dan `storage/framework` (karena folder ini dapat diproduksi ulang secara otomatis dan menghemat ukuran zip).

---

## 2. Mengapa Backup Schedule Belum Berjalan Otomatis?

Di Laravel, pengaturan jadwal otomatis pada web UI (`daily`, `weekly`, `monthly`) membutuhkan **Laravel Task Scheduler (Cron Job)** yang berjalan setiap menit di server Operating System (OS).

### Cara Mengaktifkan Scheduler di Server Production:

#### A. Jika Server Linux (Ubuntu / CentOS / Debian):
Buka crontab server Prod:
```bash
crontab -e
```
Tambahkan 1 baris berikut di bagian paling bawah:
```bash
* * * * * cd /path/ke/proyek/assetika && php artisan schedule:run >> /dev/null 2>&1
```

#### B. Jika Server Windows / Laragon:
1. Buka **Task Scheduler** di Windows.
2. Buat Task baru: `Assetika Schedule Run`.
3. Set Trigger: `Daily`, repeat every `1 minute`.
4. Set Action: Start a program: `php.exe`, Argument: `artisan schedule:run`, Start in: `C:\laragon\www\assetika`.
5. Atau jalankan di background terminal:
   ```powershell
   php artisan schedule:work
   ```

---

## 3. Tambahan Variabel `.env` Production

```env
# Path lokasi folder bin mysqldump di server Prod
# Jika mysqldump sudah terdaftar di system PATH (misal Linux /usr/bin), kosongkan valuenya ("")
DB_DUMP_BINARY_PATH="C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin"

# Opsi tambahan mysqldump (kosongkan "" jika server Prod menggunakan MariaDB / MySQL 5.7)
DB_DUMP_EXTRA_OPTION="--column-statistics=0"
```

---

## 4. Pengaturan `DB_HOST` & `DB_PORT` Production

```env
DB_HOST=127.0.0.1
DB_PORT=3310
```

---

## 5. Pembersihan Cache Setelah Update

Jalankan perintah berikut di terminal Production:
```bash
php artisan config:clear
php artisan cache:clear
```
