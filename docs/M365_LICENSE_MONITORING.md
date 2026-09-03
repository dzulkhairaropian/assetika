# Microsoft 365 Real-Time License & Device Monitoring Specification

Dokumen ini berisi spesifikasi teknis, konfigurasi integrasi Microsoft Graph API, serta placeholder data yang perlu diisi untuk mengaktifkan fitur pemantauan lisensi Microsoft 365 (Business Standard / Desktop Seats) secara realtime di **Assetika**.

---

## 1. Credentials & Configuration (Diisi oleh Admin)

Silakan lengkapi data konfigurasi Azure / Microsoft Entra ID di bawah ini sebelum fitur diimplementasikan:

```env
# ==============================================================================
# MICROSOFT 365 / ENTRA ID GRAPH API CONFIGURATION
# ==============================================================================
M365_TENANT_ID="7ad98c16-ff92-46e1-b991-24647152bced"
M365_CLIENT_ID="59197eed-1542-46e9-a54b-81f2330117ea"
M365_OBJECT_ID="5bcd865e-4a25-47ff-808e-76041d6f70bb"
M365_CLIENT_SECRET="HmQ8Q~hjVH~HYfhL1~mjyCfiwfUK8juDG70YYcNU"
M365_SECRET_ID="4d8da603-9df3-41b3-9d06-b1e40ef37951"
M365_REDIRECT_URI="${APP_URL}/oauth/microsoft/callback"

# Akun-akun M365 Standard yang digunakan untuk aktivasi Office Desktop (Pisahkan dengan koma jika lebih dari satu)
M365_STANDARD_ACCOUNTS="master-std-01@company.com,master-std-02@company.com"

# Interval Sinkronisasi Otomatis (dalam menit)
M365_SYNC_INTERVAL_MINUTES=60
```

### Form Isian Data Tenant:

| Parameter | Value | Keterangan |
| :--- | :--- | :--- |
| **Organization Name / Tenant Domain** | `Diastika Biotekindo, PT` | Perusahaan |
| **Directory (Tenant) ID** | `7ad98c16-ff92-46e1-b991-24647152bced` | GUID Tenant dari Microsoft Entra ID |
| **Application (Client) ID** | `59197eed-1542-46e9-a54b-81f2330117ea` | GUID App Registration dari Azure Portal |
| **Object ID** | `5bcd865e-4a25-47ff-808e-76041d6f70bb` | Enterprise Application Object ID |
| **Client Secret ID** | `4d8da603-9df3-41b3-9d06-b1e40ef37951` | Secret Identifier |
| **Client Secret (Value)** | `HmQ8Q~hjVH~HYfhL1~mjyCfiwfUK8juDG70YYcNU` | Nilai secret key yang digenerate |
| **Client Secret Expiration** | `24 Months` | Tanggal kedaluwarsa secret key |

---

## 2. Daftar Akun M365 Standard & Mapping Kuota

Isi daftar akun master Standard yang dipakai untuk aktivasi komputer kantor:

| No | Email Akun M365 Standard | Total Kuota Device | Device Terpakai (Estimasi) | Keterangan / PIC |
| :---: | :--- | :---: | :---: | :--- |
| 1 | `license1@company.com` | 5 | 0 / 5 | Akun Master Batch 1 |
| 2 | `license2@company.com` | 5 | 0 / 5 | Akun Master Batch 2 |
| 3 | | 5 | 0 / 5 | |
| 4 | | 5 | 0 / 5 | |

---

## 3. Panduan Setup di Azure Portal / Entra ID

Ikuti langkah-langkah berikut untuk mendapatkan `Client ID` dan `Client Secret`:

1. **Buka Azure Portal / Microsoft Entra ID:**
   * Kunjungi [https://entra.microsoft.com](https://entra.microsoft.com) atau [https://portal.azure.com](https://portal.azure.com).
2. **Buat App Registration Baru:**
   * Masuk ke menu **Applications** -> **App registrations** -> Klik **New registration**.
   * Name: `Assetika M365 License Monitor`
   * Supported account types: `Accounts in this organizational directory only (Single tenant)`
   * Klik **Register**.
3. **Catat Tenant ID & Client ID:**
   * Salin **Application (client) ID** dan **Directory (tenant) ID** dari halaman *Overview*.
4. **Buat Client Secret:**
   * Masuk ke menu **Certificates & secrets** -> Tab **Client secrets** -> Klik **New client secret**.
   * Description: `Assetika API Integration`
   * Expires: Pilih `24 months` (atau sesuai kebijakan company).
   * Klik **Add** -> **Salin nilai pada kolom `Value`** (Perhatian: Nilai ini hanya muncul sekali).
5. **Tambahkan API Permissions:**
   * Masuk ke menu **API permissions** -> Klik **Add a permission** -> Pilih **Microsoft Graph**.
   * Pilih **Application permissions** (Bukan Delegated permissions).
   * Centang permission berikut:
     * `User.Read.All` (Membaca informasi user/akun)
     * `Device.Read.All` (Membaca nama komputer/laptop yang terhubung)
     * `Directory.Read.All` (Membaca struktur direktori dan relasi)
     * `Reports.Read.All` *(Opsional)* (Membaca statistik pemakaian Office 365)
   * Klik **Add permissions**.
   * Klik tombol **Grant admin consent for [Nama Company Anda]** di sebelah tombol Add permission.

---

## 4. Arsitektur Teknis & Komponen Assetika

```mermaid
flowchart TD
    subgraph Cloud [Microsoft Cloud]
        ENTRA[Microsoft Entra ID / Graph API]
    end

    subgraph Backend [Assetika Laravel 13 Backend]
        SERVICE[App\Services\MicrosoftGraphService]
        JOB[App\Jobs\SyncM365LicensesJob]
        COMMAND[php artisan m365:sync-licenses]
    end

    subgraph DB [Assetika Database]
        LICENSE_TBL[(licenses)]
        SEAT_TBL[(license_seats)]
        ASSET_TBL[(assets)]
    end

    subgraph Frontend [Livewire 4 + Tailwind UI]
        DASHBOARD[M365 License Livewire Monitor]
        SYNC_BTN[Live Sync Trigger Button]
    end

    ENTRA -->|REST API v1.0| SERVICE
    SERVICE --> JOB
    COMMAND --> JOB
    JOB -->|Update / Auto-Match Hostname| LICENSE_TBL
    JOB --> SEAT_TBL
    SEAT_TBL <--> ASSET_TBL
    LICENSE_TBL --> DASHBOARD
    SYNC_BTN -->|Trigger Manual Sync| SERVICE
```

### A. Endpoint Graph API yang Digunakan:
* **Get User ID:** `GET https://graph.microsoft.com/v1.0/users/{userPrincipalName}`
* **Get Registered Devices by User:** `GET https://graph.microsoft.com/v1.0/users/{id}/registeredDevices`
* **Get Subscribed SKUs (Total Lisensi Perusahaan):** `GET https://graph.microsoft.com/v1.0/subscribedSkus`

### B. Mekanisme Auto-Matching Device ke Asset di Assetika:
1. Graph API mengembalikan data device, contoh:
   * `displayName`: `NB-FIN-SITI`
   * `operatingSystem`: `Windows 11 Enterprise`
   * `approximateLastSignInDateTime`: `2026-09-03T14:30:00Z`
   * `deviceId`: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
2. Sistem Assetika mencocokkan `displayName` (Hostname) dengan:
   * `assets.name` atau `assets.asset_tag` di database Assetika.
3. Jika cocok:
   * Otomatis mengisi `license_seats.assigned_to_id` (Asset Laptop) & relasi `User` pemegang laptop tersebut.
   * Menampilkan status aktivasi valid & tanggal terakhir online.
4. Jika tidak cocok (Device baru belum terdaftar di Asset):
   * Ditandai sebagai **Unmapped Device** dengan opsi 1-klik untuk *"Register as New Asset"*.

---

## 5. Fitur UI / Dashboard yang Akan Dibangun

1. **Summary Header Cards:**
   * Total Standard Accounts
   * Total Seats Available vs Used (e.g. `8 / 10 Active (80%)`)
   * Total Basic Users
   * Last Synchronized Timestamp
2. **Per-Account License Meter:**
   * Progress bar visual (Hijau `< 80%`, Kuning `80%`, Merah `100% / Full`).
   * Slot indicator `[1] [2] [3] [4] [5]`.
3. **Interactive Device Table per Lisensi:**
   * Hostname / Device Name
   * Mapped Asset Tag & Asset Name
   * Employee Name & Department
   * Operating System & Version
   * Last Active / Sign-in Time
   * Action: *Sync Now* / *View Asset Detail* / *Quick Map*.

---

## 6. Checklist Implementasi

- [ ] Admin melengkapi credentials di Section 1
- [ ] Admin melakukan Grant Admin Consent di Azure Portal
- [ ] Implementasi `MicrosoftGraphService` di `app/Services/`
- [ ] Penambahan kolom sinkronisasi M365 pada migration `licenses` & `license_seats`
- [ ] Pembuatan Console Command `php artisan m365:sync-licenses` & Scheduler
- [ ] Pembuatan Livewire Component untuk Dashboard License Monitoring
- [ ] Testing sinkronisasi real-time & verifikasi pemakaian kuota 5 device
