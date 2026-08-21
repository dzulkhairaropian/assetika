# 🚀 Technical Specification & Implementation Plan: Assetika Next-Phase Features

Dokumen ini berisi spesifikasi teknis dan rencana implementasi untuk 4 modul/fitur baru pada aplikasi **Assetika** (Laravel 13 + Livewire 4).

---

## 📌 Daftar Modul

1. [Knowledge Base (KB) Module](#1-knowledge-base-kb-module)
2. [Advanced SLA & Escalation Engine](#2-advanced-sla--escalation-engine)
3. [Asset Reservation System (Peminjaman Aset)](#3-asset-reservation-system-peminjaman-aset)
4. [Unified Expiry & Renewal Tracker](#4-unified-expiry--renewal-tracker)

---

## 1. 📚 Knowledge Base (KB) Module

### Konsep Utama
Pusat pengetahuan dan solusi publik untuk karyawan (end-user) dan teknisi. Berfungsi ganda:
- **Public Portal**: Karyawan dapat mencari artikel jawaban/solusi mandiri.
- **Live Suggestion**: Saat user membuat Tiket atau Request (Hardware/Consumable/License), sistem memberikan rekomendasi artikel KB secara real-time untuk mengurangi jumlah tiket masuk.

### Schema Database

```php
// Database Migrations

// 1. kb_categories
Schema::create('kb_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('icon')->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// 2. kb_articles
Schema::create('kb_articles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('kb_categories')->cascadeOnDelete();
    $table->foreignId('author_id')->constrained('users');
    $table->string('title');
    $table->string('slug')->unique();
    $table->longText('content');
    $table->integer('views_count')->default(0);
    $table->boolean('is_published')->default(true);
    $table->timestamps();
});

// 3. kb_tags & pivot
Schema::create('kb_tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->timestamps();
});

Schema::create('kb_article_tag', function (Blueprint $table) {
    $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained('kb_tags')->cascadeOnDelete();
    $table->primary(['article_id', 'tag_id']);
});
```

### Integrasi UI & Logic
- **Livewire Component**: `KbArticleSearch` (Live suggestions dengan debounce 300ms pada form Tiket & Request).
- **Public Page**: `/kb` untuk pencarian terbuka per kategori & tag.

---

## 2. ⏱️ Advanced SLA & Escalation Engine

### Konsep Utama
Menghitung secara presisi batas waktu penanganan tiket berdasarkan prioritas, **Jam Kerja (Operating Hours)**, dan **Pause State** (misal saat menunggu respon user). Memanfaatkan tabel `status_slas` dan relasi `Ticket.sla_status_id` yang sudah ada di Assetika.

### Schema Database

```php
// 1. sla_policies (Definisi Target Per Prioritas)
Schema::create('sla_policies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('priority_id')->constrained('priorities')->cascadeOnDelete();
    $table->integer('response_target_minutes');  // Target respon pertama
    $table->integer('resolution_target_hours');  // Target penutupan tiket
    $table->timestamps();
});

// 2. Modifikasi ticket_statuses (Tambah Fitur Pause)
Schema::table('ticket_statuses', function (Blueprint $table) {
    $table->boolean('is_paused')->default(false)->after('is_closed');
});

// 3. Modifikasi tickets (Tambah Waktu Due & Tracking Pause)
Schema::table('tickets', function (Blueprint $table) {
    $table->timestamp('sla_due_at')->nullable()->after('completed_time');
    $table->timestamp('sla_paused_at')->nullable()->after('sla_due_at');
    $table->integer('total_paused_minutes')->default(0)->after('sla_paused_at');
});
```

### Business Hours & Pause Logic

1. **Operating Hours Config**:
   - Default: Senin – Jumat, 08.00 – 17.00 WIB.
   - Durasi SLA dilompati di luar jam kerja & hari libur.
2. **Pause & Resume Cycle**:
   - Ketika status tiket berubah ke `is_paused = true` (contoh: *Pending User Response*), catat `sla_paused_at = now()`.
   - Ketika status berubah kembali ke *Active*, hitung durasi `now() - sla_paused_at`, tambahkan ke `total_paused_minutes`, lalu undurkan `sla_due_at`.
3. **Automated Evaluator (Cron Job)**:
   - Command `tickets:evaluate-sla` dijalankan tiap 5 menit via Laravel Scheduler.
   - Mengubah `sla_status_id` secara otomatis:
     - 🟢 **On Track** (Sisa waktu > 30%)
     - 🟡 **Warning** (Sisa waktu <= 30%)
     - 🔴 **Breached** (Sudah lewat `sla_due_at`)

---

## 3. 📅 Asset Reservation System (Peminjaman Aset)

### Konsep Utama
Modul booking aset sementara (laptop dinas, proyektor, mobil operasional GA, ruang rapat) dengan kalender visual serta proteksi jadwal bentrok.

### Schema Database

```php
Schema::create('asset_reservations', function (Blueprint $table) {
    $table->id();
    $table->nullableMorphs('reservable'); // Support Asset (IT) & GaAsset (GA)
    $table->foreignId('user_id')->constrained('users'); // Peminjam
    $table->foreignId('approved_by_id')->nullable()->constrained('users'); // Approver
    $table->dateTime('start_time');
    $table->dateTime('end_time');
    $table->string('status')->default('pending'); // pending, approved, rejected, checked_out, completed, cancelled
    $table->text('purpose');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Proteksi Bentrok & Validation Logic

```php
// Pengecekan overlap sebelum menyimpan reservasi
$hasOverlap = AssetReservation::where('reservable_type', $type)
    ->where('reservable_id', $id)
    ->whereIn('status', ['pending', 'approved', 'checked_out'])
    ->where(function ($query) use ($startTime, $endTime) {
        $query->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function ($q) use ($startTime, $endTime) {
                  $q->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime);
              });
    })->exists();
```

---

## 4. 🔔 Unified Expiry & Renewal Tracker

### Konsep Utama
Pusat pengawasan kadaluarsa terpadu untuk:
- Domain & SSL Certificate (Standalone items).
- Lisensi Software (Integrasi dengan tabel `licenses`).
- Pajak & Asuransi Kendaraan GA (Integrasi dengan tabel `ga_asset_vehicles`).

### Schema Database

```php
Schema::create('renewals', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Nama item / domain / lisensi
    $table->string('type'); // domain, ssl_certificate, license, vehicle_tax, vehicle_insurance, custom
    $table->nullableMorphs('renewable'); // Relasi opsional ke License atau GaAssetVehicle
    $table->date('issued_date')->nullable();
    $table->date('expiration_date');
    $table->integer('reminder_days_before')->default(30); // Reminder H-30
    $table->foreignId('vendor_id')->nullable()->constrained('vendors');
    $table->foreignId('responsible_user_id')->nullable()->constrained('users');
    $table->decimal('cost', 15, 2)->nullable();
    $table->boolean('auto_renew')->default(false);
    $table->string('status')->default('active'); // active, expiring_soon, expired, renewed
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Cron Job & Notification Pipeline
- Command `renewals:check-expiry` dijalankan harian.
- Mengubah status ke `expiring_soon` jika `expiration_date - now() <= reminder_days_before`.
- Mengirim event / log notifikasi pengingat ke `responsible_user_id`.

---

## 🎯 Ringkasan Struktur File Baru

| Fitur | Models | Migrations | Livewire Components |
|---|---|---|---|
| **Knowledge Base** | `KbArticle`, `KbCategory`, `KbTag` | `create_kb_tables.php` | `Pages\Kb\Index`, `Pages\Kb\Show`, `Components\KbSearch` |
| **SLA Engine** | `SlaPolicy` | `create_sla_policies_table.php`, `add_sla_fields_to_tickets.php` | `Pages\Sla\PolicyIndex`, `Console\Commands\EvaluateSla` |
| **Reservations** | `AssetReservation` | `create_asset_reservations_table.php` | `Pages\Reservations\Index`, `Pages\Reservations\Calendar` |
| **Renewal Tracker** | `Renewal` | `create_renewals_table.php` | `Pages\Renewals\Index`, `Console\Commands\CheckExpiry` |

---

Dokumen ini siap digunakan sebagai acuan eksekusi pengembangan fase berikutnya di **Assetika**.
