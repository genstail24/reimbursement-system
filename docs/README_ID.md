# Sistem Manajemen Reimbursement Karyawan

Aplikasi manajemen reimbursment untuk karyawan yang dibuat menggunakan framework Laravel 12 (backend) dan Nuxt.js 3 (frontend).

---

## Prasyarat

Sebelumnya, pastikan sistem Anda telah memenuhi requirement berikut:

* **PHP** versi **8.3**  
  Instalasi: [php.net/downloads.php](https://www.php.net/downloads.php)
* **Node.js** versi **v20** atau lebih baru  
  Instalasi: [nodejs.org/en/download](https://nodejs.org/en/download/)
* **PNPM** sebagai package manager (jangan menggunakan npm)  
  Instalasi: [pnpm.io/installation](https://pnpm.io/installation)
* **Database MySQL**  
  Instalasi MySQL: [dev.mysql.com/downloads](https://dev.mysql.com/downloads/)
* **(Windows/MAC) Laragon atau XAMPP** — disarankan untuk kemudahan setup PHP & MySQL  
  - Laragon: [laragon.org](https://laragon.org/)  
  - XAMPP: [apachefriends.org](https://www.apachefriends.org/index.html)

---

## Diagram Workflow & Arsitektur

### Use Case Diagram

![Use Case Diagram](docs/images/use-case-diagram.jpg)

### Entity Relationship Diagram

![Entity Relationship Diagram](docs/images/entity-relationship-diagram.jpg)

### Diagram arsitektur

![Diagram Arsitektur](docs/images/architecture-diagram.jpg)

* **Frontend**: Nuxt.js berperan sebagai Interface langsung dengan user dan mengonsumsi API backend.
* **Backend**: Laravel menyediakan RESTful API, menangani business process, komunikasi dengan database, atau komunikasi dengan pihak ketiga (notifikasi email).
* **Database**: MySQL bertugas menyimpan data dari backend

---

## Desain & Pendekatan

* **Manajemen Role**: Menggunakan paket Spatie Laravel Permission untuk manajemen role (admin, manager, employee) dan permission yang sudah teruji di production dan *battle tested*.

* **Validasi Limit Bulanan**: Pada submission, backend mengecek total pengajuan per kategori pada bulan berjalan (limit_per_month) dan menolak jika melebihi.

* **Upload Bukti Transaksi**: Mendukung format PDF/JPG hingga 2MB, disimpan di Laravel filesystem (storage/app/public) dan path-nya disimpan di database.

* **Flow Approval**:

  1. Employee membuat reimbursement.
  2. Manager approve atau reject reimbursement.

* **Soft Delete**: Model Reimbursement menggunakan trait SoftDeletes, sehingga data dihapus secara lembut. Admin melihat record *soft-deleted* via query withTrashed().

* **Log Aktivitas**: Memakai paket Spatie Activitylog untuk mencatat aksi ('submission' dan 'approval' reimbursements).

* **Email Notification Asynchronous**: Menggunakan Laravel Queue (Database driver), job notifikasi mengirim email ke manager via Gmail SMTP atau mail provider lain.

---

## Fitur Utama

1. **Autentikasi**.
2. **Manajemen Role**.
3. **Manajemen Reimbursement** (submission, approval, delete).
4. **Manajemen User**.
5. **Manajemen Kategori**.
6. **Log Aktivitas**.
7. **Notifikasi Email** via queue.

------

## Setup & Deploy

### A. Manual (Single Repo)

#### 1. Clone Repository

```bash
git clone https://github.com/genstail24/reimbursement-system.git reimbursement-system
cd reimbursement-system
```


#### 2. Backend (Laravel 12)

1. Masuk ke direktori backend:

   ```bash
   cd backend
   ```
2. Install dependencies:

   ```bash
   composer install
   ```
3. Buat file environment:

   ```bash
   cp .env.example .env
   ```
4. Konfigurasi `.env` sesuai kebutuhan Anda (SMTP & MySQL, serta URL aplikasi):

   ```dotenv
   APP_NAME="ReimbursementSystem"
   APP_URL=http://localhost:8000           # URL backend
   APP_FRONTEND_URL=http://localhost:3000  # URL frontend

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database               # ganti dengan nama database Anda
   DB_USERNAME=username_db                 # ganti dengan username DB anda
   DB_PASSWORD=password_db                 # ganti dengan password DB anda

   # Queue (Database)
   QUEUE_CONNECTION=database

   # Mail (SMTP)
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io              # sesuaikan SMTP anda
   MAIL_PORT=2525
   MAIL_USERNAME=…
   MAIL_PASSWORD=…
   MAIL_FROM_ADDRESS=hello@example.com
   MAIL_FROM_NAME="Reimbursement System"
   ```
5. Generate key aplikasi:

   ```bash
   php artisan key:generate
   ```
6. Migrasi & seeder database:

   ```bash
   php artisan migrate --seed
   ```
7. Buat symbolic link untuk storage:

   ```bash
   php artisan storage:link
   ```
8. Jalankan queue worker di terminal terpisah:

   ```bash
   php artisan queue:work
   ```
9. Jalankan server di terminal baru:

   ```bash
   php artisan serve
   ```

#### 3. Frontend (Nuxt.js 3)

1. Buka terminal baru, lalu masuk ke direktori frontend:

   ```bash
   cd reimbursement-system/frontend
   ```
2. Install dependencies dengan PNPM:

   ```bash
   pnpm install
   ```
3. Buat file environment:

   ```bash
   cp .env.example .env
   ```
4. Konfigurasi `.env` (pastikan `API_BASE_URL` mengarah ke backend):

   ```dotenv
   NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
   ```
5. Jalankan development server:

   ```bash
   pnpm run dev
   ```
6. Buka aplikasi di browser:

   ```
   http://localhost:3000
   ```

### B. Manual (Single Repo)
Monorepo (Lerna)

> **! Belum Diimplementasikan**
> Struktur monorepo dengan Lerna masih dalam tahap development.

---

## Dokumentasi API

* **Postman Collection**: [Download API Collection](docs/Reimbursement_API_Collection.postman_collection) — import ke Postman untuk menguji endpoint.

---

## Tantangan & Solusi

Secara keseluruhan, dengan pengalaman yang saya miliki, saya tidak mengalami kesulitan berarti dalam hal perancangan teknis maupun implementasi backend dan frontend.
 